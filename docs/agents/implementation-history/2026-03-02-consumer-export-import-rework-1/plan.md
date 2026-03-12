# Plan

## Summary

This rework plan addresses the two outstanding issues recorded in the synthesis
document for the `2026-03-02-consumer-export-import` project:

1. **Failing test** — `TranslationExporterTest::testUntranslatedStringHasEmptyTranslation`
   fails whenever `composer import-translations` has been run, because all strings are
   then translated and the test can find no untranslated hash to assert against.
2. **Missing PHPDoc** — `Localization::createExporter()` and
   `Localization::createImporter()` have no doc-block, while every other `create*()`
   method on the facade has one.

---

## Architectural Context

### Affected files

| File | Role |
|---|---|
| `tests/testsuites/Tools/TranslationExporterTest.php` | Integration test class for `TranslationExporter`. Uses the real `localization/` directory as its source of storage and INI files. |
| `src/Localization.php` | Static facade. All `create*()` factory methods live here. |

### Failing test — root cause

`testUntranslatedStringHasEmptyTranslation` works by:

1. Running a fresh export (in `setUp()`) against `localization/storage.json` and the
   accompanying INI files in `localization/`.
2. In the test body, reading `localization/de_DE-application-localization-server.ini`
   directly via `parse_ini_file()`.
3. Checking each exported JSON entry: if a hash is **absent** from the INI, its
   `translation` field must be `""`.
4. Asserting that **at least one such untranslated entry** was found.

After `composer import-translations` is run (as it was before this rework), every
hash is written into the INI files, so step 4 always fails. The test is
environment-sensitive; it does not own its fixtures.

### PHPDoc coverage on the facade

Inspecting `src/Localization.php`:

| Method | Has PHPDoc |
|---|---|
| `createCountries()` (L312) | ✅ |
| `createCurrencies()` (L329) | ✅ |
| `createScanner()` (L1102) | ✅ |
| `createExporter()` (L1109) | ❌ |
| `createImporter()` (L1114) | ❌ |
| `createGenerator()` (L1223) | ✅ |
| `createEditor()` (L1320) | ✅ |

Only `createExporter()` and `createImporter()` are missing doc-blocks.

---

## Approach / Architecture

### WP-001 — Fix the failing test

Replace the test's dependency on the real `localization/` directory (whose INI state
is mutable by Composer scripts) with a self-contained temporary-directory fixture.

**Steps inside `TranslationExporterTest`:**

1. In `setUp()`, create a temp directory (`sys_get_temp_dir() . '/al-exporter-test-' . uniqid()`).
2. Copy `localization/storage.json` into the temp dir.
3. Copy the four live INI files from `localization/` into the temp dir, preserving
   the same filenames (`de_DE-application-localization-server.ini`, etc.).
4. **Strip one entry** from the `de_DE-application-localization-server.ini` copy by
   parsing it, dropping the last key with a non-empty value, and re-writing it as a
   valid INI. This guarantees at least one untranslated hash regardless of the real
   localization state.
5. Set `$this->localizationDir` to the temp dir. Re-point `$this->deJsonFile` /
   `$this->frJsonFile` accordingly. The rest of `setUp()` (`Localization::configure()`
   + `TranslationExporter::create()->export()`) is unchanged.
6. Add a `tearDown()` method that recursively removes the temp dir.

This approach:
- Keeps the test as a real integration test (no mocking).
- Makes it deterministic (fixtures are controlled, not affected by Composer scripts).
- Does not touch any other test or production code.

### WP-002 — Add PHPDoc to the two missing methods

Add a short doc-block to each method, matching the style of `createScanner()`:

```php
/**
 * Creates a new exporter instance used to generate per-locale
 * per-source JSON translation export files.
 *
 * Requires {@see configure()} to have been called beforehand.
 *
 * @return TranslationExporter
 */
public static function createExporter() : TranslationExporter

/**
 * Creates a new importer instance used to read per-locale
 * per-source JSON translation export files and write them
 * back into the INI translation files.
 *
 * Requires {@see configure()} to have been called beforehand.
 *
 * @return TranslationImporter
 */
public static function createImporter() : TranslationImporter
```

---

## Rationale

- **Temp-dir fixture** is the standard pattern for integration tests whose output
  depends on mutable on-disk state. It keeps the test honest without mocking.
- **Stripping one INI entry** rather than using an entirely synthetic fixture avoids
  having to maintain a separate stub `storage.json`; the real `storage.json` provides
  the hash list and the real INI provides real translations — the test just guarantees
  one gap.
- **PHPDoc only on the two missing methods** stays within the issue scope and avoids
  unrelated style churn on methods that already have docs.

---

## Detailed Steps

1. **Implement temp-dir fixture in `TranslationExporterTest`:**
   a. Add a `private string $tempDir;` property.
   b. Replace the hard-coded `$this->localizationDir = $root . '/localization'` in
      `setUp()` with the temp-dir setup sequence (create, copy, strip one INI entry).
   c. Add `tearDown()` to remove the temp dir.
   d. Verify that the other tests in the class (`testExportedJsonHasRequiredTopLevelKeys`,
      `testStringEntryHasRequiredFields`, `testNativeLocaleIsExcluded`,
      `testTranslatedStringHasCorrectTranslation`, `testFilePathsAreRelative`) still
      pass — they all read from `$this->localizationDir` so they will naturally pick
      up the temp dir.

2. **Add PHPDoc to `createExporter()` and `createImporter()` in `src/Localization.php`.**

3. **Run PHPStan** (`composer analyze`) — must pass at level 8 with 0 errors.

4. **Run the test suite** (`composer test`) — all tests must pass.

5. **Update `docs/agents/project-manifest/api-core.md`** — add a PHPDoc note to the
   `createExporter()` and `createImporter()` entries in the Factory Methods table.

---

## Dependencies

- No new dependencies.
- `TranslationExporterTest` depends on `localization/storage.json` (read) and the
  four locale INI files (read). These are copied to the temp dir before the test runs,
  so they must exist in `localization/` at test time (they always do).

---

## Required Components

| Component | New / Modified |
|---|---|
| `tests/testsuites/Tools/TranslationExporterTest.php` | Modified |
| `src/Localization.php` | Modified |
| `docs/agents/project-manifest/api-core.md` | Modified |

---

## Assumptions

- `localization/storage.json` always exists (it is committed to the repository).
- At least one hash in `localization/de_DE-application-localization-server.ini` has a
  non-empty value (so the stripping logic finds something to remove).
- The test environment has write access to `sys_get_temp_dir()`.

---

## Constraints

- PHPStan must continue to pass at level 8.
- No production API changes — only PHPDoc additions.
- The fix must not change test names or move tests to a different file.

---

## Out of Scope

- Rewriting the other `create*()` PHPDoc blocks (they already exist).
- Changing the `TranslationImporterTest` (not referenced as failing).
- Any changes to `TranslationExporter` or `TranslationImporter` production logic.

---

## Acceptance Criteria

- `TranslationExporterTest::testUntranslatedStringHasEmptyTranslation` passes
  regardless of whether `composer import-translations` has been run.
- All other tests in `TranslationExporterTest` continue to pass.
- `Localization::createExporter()` and `Localization::createImporter()` have PHPDoc
  doc-blocks matching the style of neighbouring `create*()` methods.
- PHPStan level 8 reports 0 errors.
- Full test suite passes (`composer test`).

---

## Testing Strategy

- Run `composer test-file -- tests/testsuites/Tools/TranslationExporterTest.php` to
  verify the targeted test and all sibling tests pass.
- Run `composer analyze` to confirm PHPStan is clean.
- Run `composer test` to confirm no regressions across the full suite.

---

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **`sys_get_temp_dir()` is not writable in some CI environments** | Use `$root . '/tests/cache/exporter-test-{uniqid}'` as a fallback path inside the known-writable test cache directory. |
| **INI stripping removes the only entry, leaving nothing for `testTranslatedStringHasCorrectTranslation`** | Strip one entry from the de_DE server INI only, leaving many remaining. `testTranslatedStringHasCorrectTranslation` iterates all entries and needs only one translated entry, which will still exist. |
| **`storage.json` contains absolute paths that differ in the temp dir** | `storage.json` is read to obtain source configuration; absolute path references inside it (if any) are normalised by the exporter's `getFilePaths()` method and by the scanner's load path. The storage folder path is derived from where `configure()` is called, not from paths inside the JSON. No risk. |
