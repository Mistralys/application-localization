# Synthesis Report — Consumer Export / Import

**Project:** 2026-03-02-consumer-export-import  
**Date:** 2026-03-02  
**Status:** COMPLETE  

---

## Summary

This project delivered two non-breaking improvements that make
`TranslationExporter` and `TranslationImporter` usable by consuming applications
out of the box, without requiring a custom constant or environment variable.

---

## Changes Delivered

### WP-001 — Fix `loadConfig()` Default Config-Path Fallback

**Files modified:** [src/Tools/TranslationExporter.php](../../../../src/Tools/TranslationExporter.php), [src/Tools/TranslationImporter.php](../../../../src/Tools/TranslationImporter.php)

The default config-path fallback in both `loadConfig()` methods was changed from:

```php
// Before (resolves to the library package root inside vendor/)
$defaultPath = __DIR__ . '/../../localization-tools-config.php';
```

to:

```php
// After (resolves to the project root of the application running Composer)
$defaultPath = getcwd() . '/localization-tools-config.php';
```

This is a backward-compatible change. The constant and environment-variable lookup
paths (steps 1 and 2) are unchanged. The fallback now resolves to the consumer's
project root, meaning consuming applications can simply place
`localization-tools-config.php` at their project root and run
`composer export-translations` / `composer import-translations` without any
additional configuration.

---

### WP-002 — Facade Factory Methods

**Files modified:** [src/Localization.php](../../../../src/Localization.php)

Two factory methods were added to the `Localization` static facade, immediately
after `createScanner()`:

```php
public static function createExporter() : TranslationExporter
{
    return TranslationExporter::create();
}

public static function createImporter() : TranslationImporter
{
    return TranslationImporter::create();
}
```

These follow the existing `create*()` factory-method pattern used by
`createScanner()`, `createEditor()`, `createGenerator()`, `createCountries()`,
and `createCurrencies()`. Consuming applications no longer need to import
`AppLocalize\Tools\*` classes directly to instantiate the tools; they can use
`Localization::createExporter()` and `Localization::createImporter()` instead.

---

### WP-003 — Facade Factory Method Tests

**Files created:** [tests/testsuites/Tools/LocalizationFacadeToolsTest.php](../../../../tests/testsuites/Tools/LocalizationFacadeToolsTest.php)

Two smoke tests were added to verify the new factory methods return the correct
types:

- `test_createExporter_returnsTranslationExporterInstance` — asserts `Localization::createExporter()` returns a `TranslationExporter` instance.
- `test_createImporter_returnsTranslationImporterInstance` — asserts `Localization::createImporter()` returns a `TranslationImporter` instance.

Both pass. The test class matches the style of neighboring test files in
`tests/testsuites/Tools/`.

**PHPStan:** 0 errors across 169 files (level 8).  
**Test suite:** 110 tests, 939 assertions.

> **Note:** A pre-existing failure in `TranslationExporterTest::testUntranslatedStringHasEmptyTranslation`
> was present before this feature branch began (confirmed in WP-001 pipeline records).
> It is caused by `composer import-translations` having been run, leaving all strings
> translated and making the "find an untranslated string" assertion impossible.
> This failure is unrelated to the work in this plan and introduces no regression.

---

### WP-004 — Project Manifest Documentation Updates

**Files modified:**
- [docs/agents/project-manifest/api-core.md](api-core.md): Added `createExporter()` and `createImporter()` to the Factory Methods block.
- [docs/agents/project-manifest/api-translator-editor.md](api-translator-editor.md): Updated Config Resolution Order step 3 to reflect `getcwd()` and added a consumer guidance note.

---

## Impact Assessment

| Area | Impact |
|---|---|
| Consuming applications using `composer export-translations` / `composer import-translations` | **Improved** — default config fallback now works without constant/env-var |
| Consuming applications using programmatic API | **Improved** — `Localization::createExporter()` and `Localization::createImporter()` are now available |
| Library's own test suite | **Unaffected** — library uses `LOCALIZATION_TOOLS_CONFIG` constant which takes precedence over the fallback |
| Backward compatibility | **Fully preserved** — no existing API changed |
| PHPStan level 8 | **Passes** with 0 errors |

---

## Outstanding Issues

| Issue | Severity | Notes |
|---|---|---|
| `TranslationExporterTest::testUntranslatedStringHasEmptyTranslation` failing | Low | Pre-existing. Caused by all strings being translated after `composer import-translations`. Needs a separate investigation/fix in its own plan. |
| No PHPDoc on `createExporter()` / `createImporter()` | Low | Acceptable given inconsistent doc-block usage on other `create*()` methods. Consider a doc-block cleanup pass. |
