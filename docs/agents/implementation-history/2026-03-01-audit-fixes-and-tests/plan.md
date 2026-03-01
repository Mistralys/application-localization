# Plan

## Summary

Address the three bugs found by the unit test audit (decimal precision loss in `CurrencyNumberInfo`, broken validation in `CountryCurrency::isNumberValid()`, and a class/filename mismatch silently skipping 3 parser tests), modernize the `phpunit.xml` configuration, add the most important missing unit tests (CurrencyNumberInfo + Scanner subsystem), and investigate / fix the `ClientFilesGenerator` regenerating JS files on every request instead of using its cache key mechanism.

## Architectural Context

### Relevant Modules

- **`CurrencyNumberInfo`** ([src/Localization/Currencies/CurrencyNumberInfo.php](src/Localization/Currencies/CurrencyNumberInfo.php)) — Value object holding a parsed currency number as `int $number` + `int $decimals`. Used by all currency formatting.
- **`CountryCurrency`** ([src/Localization/Countries/CountryCurrency.php](src/Localization/Countries/CountryCurrency.php)) — Wraps a currency for a specific country, providing number formatting, normalization, parsing, and validation. Constructs `CurrencyNumberInfo` in `tryParseNumber()`.
- **`PHPParserTests`** ([tests/testsuites/Parser/PHPParserTests.php](tests/testsuites/Parser/PHPParserTests.php)) — Test file containing class `ParserPHPTest` (mismatched name).
- **`ClientFilesGenerator`** ([src/Localization/Translator/ClientFilesGenerator.php](src/Localization/Translator/ClientFilesGenerator.php)) — Writes JS client translation files to disk with a cache key mechanism (`cachekey.txt`).
- **`Localization` facade** ([src/Localization.php](src/Localization.php)) — Static facade owning `writeClientFiles()`, `createGenerator()`, `configure()`, and all locale/source management.
- **`LocalizationScanner`** ([src/Localization/Scanner/](src/Localization/Scanner/)) — Scans registered source folders for translatable strings. Zero test coverage.

### Conventions

- PHP 8.4+, strict types, PHPStan level 8.
- Tests mirror source structure under `tests/testsuites/{Subsystem}/`.
- Numbered `sprintf` placeholders (`%1$s`) only.
- PHPUnit ≥ 13.0 (project currently on 12.5; `phpunit.xml` has PHPUnit 9 deprecated attributes).

## Approach / Architecture

The plan is organized into five work packages:

1. **Bug fixes** — Fix the three audit bugs in source code.
2. **PHPUnit config modernization** — Update `phpunit.xml` to remove deprecated attributes and add per-directory test suites.
3. **Missing unit tests** — Add critical tests for `CurrencyNumberInfo` (verifying bug fixes) and `LocalizationScanner` (zero-coverage subsystem).
4. **JS generation investigation** — Diagnose and fix the `ClientFilesGenerator` regeneration issue.
5. **Manifest updates** — Update project manifest docs to reflect changes.

## Rationale

- **BUG-2 (decimal precision)** is the highest-priority fix: it silently corrupts financial data. Changing `$decimals` from `int` to `string` is a small breaking change, but it is the only correct path — an `int` cannot preserve leading zeros. The `getDecimals()` return type will change from `int` to `string`, which is an acceptable trade-off for data integrity.
- **BUG-1 (validation)** is a one-line fix with high impact — the current code never rejects invalid numbers.
- **BUG-3 (class name mismatch)** restores 3 silently-skipped tests.
- **Scanner tests** are chosen as the most important missing test because the scanner is a core subsystem with zero coverage, and the test infrastructure (example source folder, bootstrap with configured sources) already exists.
- **JS generation** is investigated as a separate work package because the root cause is not yet confirmed and may span library code and consumer application configuration.

## Detailed Steps

### WP-1: Bug Fixes

#### Step 1.1 — Fix BUG-2: `CurrencyNumberInfo` decimal precision

**File:** [src/Localization/Currencies/CurrencyNumberInfo.php](src/Localization/Currencies/CurrencyNumberInfo.php)

The root cause: `$decimals` is stored as `int`, so `(int)"05"` becomes `5`, and `getString()` returns `"100.5"` instead of `"100.05"`.

Changes:
1. Change the `$decimals` property type from `int` to `string`.
2. Update the constructor to accept `int|string $decimals` and convert to string internally: `$this->decimals = (string)$decimals`.
3. Update `getString()` to return `$this->number . '.' . $this->decimals` (already does this, but now `$decimals` is a string preserving leading zeros).
4. Update `getDecimals()` return type from `int` to `string`.
5. Update `countDecimals()` to use `strlen($this->decimals)` (already does, but now operates on string directly — handle `"0"` as a special case: `$this->decimals === "0"` should return `0`).
6. Update `isNegative()` — unchanged, still checks `$this->number < 0`.
7. Update `getFloat()` — unchanged, still casts `getString()` to float.

**File:** [src/Localization/Countries/CountryCurrency.php](src/Localization/Countries/CountryCurrency.php)

Changes in `tryParseNumber()` (around lines 173-196):
1. Remove the `(int)` cast on `$decimals` when constructing `CurrencyNumberInfo`. Pass the decimal string directly: `new CurrencyNumberInfo((int)$thousands, $decimals)`.
2. The `$decimals = 0;` default should become `$decimals = '0';`.

#### Step 1.2 — Fix BUG-1: `CountryCurrency::isNumberValid()`

**File:** [src/Localization/Countries/CountryCurrency.php](src/Localization/Countries/CountryCurrency.php), line ~97

Change:
```php
// Before (broken):
return preg_match($this->getRegex(), (string)$number) !== false;

// After (fixed):
return preg_match($this->getRegex(), (string)$number) === 1;
```

`preg_match()` returns `0` (no match), `1` (match), or `false` (error). The current `!== false` returns `true` for both `0` and `1`, so invalid numbers are never rejected.

#### Step 1.3 — Fix BUG-3: `PHPParserTests.php` class name mismatch

**File:** [tests/testsuites/Parser/PHPParserTests.php](tests/testsuites/Parser/PHPParserTests.php), line 12

Change:
```php
// Before:
final class ParserPHPTest extends TestCase

// After:
final class PHPParserTests extends TestCase
```

This restores 3 silently-skipped tests (`test_findTexts`, `test_triggerWarnings`, `test_parseStringValues`).

### WP-2: PHPUnit Configuration Modernization

**File:** [phpunit.xml](phpunit.xml)

Replace the entire file with:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit colors="true"
         stopOnFailure="false"
         bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Core">
            <directory suffix=".php">./tests/testsuites/Core</directory>
        </testsuite>
        <testsuite name="Countries">
            <directory suffix=".php">./tests/testsuites/Countries</directory>
        </testsuite>
        <testsuite name="Currencies">
            <directory suffix=".php">./tests/testsuites/Currencies</directory>
        </testsuite>
        <testsuite name="Events">
            <directory suffix=".php">./tests/testsuites/Events</directory>
        </testsuite>
        <testsuite name="Locale">
            <directory suffix=".php">./tests/testsuites/Locale</directory>
        </testsuite>
        <testsuite name="Parser">
            <directory suffix=".php">./tests/testsuites/Parser</directory>
        </testsuite>
        <testsuite name="TimeZones">
            <directory suffix=".php">./tests/testsuites/TimeZones</directory>
        </testsuite>
        <testsuite name="Translator">
            <directory suffix=".php">./tests/testsuites/Translator</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Removed deprecated attributes: `backupGlobals`, `backupStaticAttributes`, `convertErrorsToExceptions`, `convertNoticesToExceptions`, `convertWarningsToExceptions`, `processIsolation`. These were removed in PHPUnit 10+ and are not valid in the project's PHPUnit 12.5.

### WP-3: Critical Missing Unit Tests

#### Step 3.1 — `CurrencyNumberInfo` tests (audit items H1, H2, H3)

**New file:** `tests/testsuites/Currencies/CurrencyNumberInfoTests.php`

Tests to implement:
1. **`test_getString_preservesLeadingZeroDecimals`** — `new CurrencyNumberInfo(1445, '05')` → assert `getString()` returns `"1445.05"` (not `"1445.5"`). This is the primary regression test for BUG-2.
2. **`test_getString_zeroDecimals`** — `new CurrencyNumberInfo(100, '0')` → assert `"100.0"`.
3. **`test_getString_leadingZeroOnly`** — `new CurrencyNumberInfo(0, '50')` → assert `"0.50"`.
4. **`test_getFloat`** — `new CurrencyNumberInfo(1445, '05')` → assert `getFloat()` returns `1445.05`.
5. **`test_countDecimals_leadingZero`** — `new CurrencyNumberInfo(100, '05')` → assert `countDecimals()` returns `2`.
6. **`test_countDecimals_noDecimals`** — `new CurrencyNumberInfo(100, '0')` → assert `countDecimals()` returns `0`.
7. **`test_countDecimals_threeDigits`** — `new CurrencyNumberInfo(100, '500')` → assert `countDecimals()` returns `3`.
8. **`test_isNegative_negative`** — `new CurrencyNumberInfo(-50, '0')` → assert `isNegative()` is `true`.
9. **`test_isNegative_positive`** — `new CurrencyNumberInfo(50, '0')` → assert `isNegative()` is `false`.
10. **`test_isNegative_zero`** — `new CurrencyNumberInfo(0, '0')` → assert `isNegative()` is `false`.

#### Step 3.2 — `CountryCurrency::isNumberValid()` tests (audit item H4)

**New file:** `tests/testsuites/Currencies/ValidationTests.php`

Tests to implement (using US country currency for simplicity):
1. **`test_isNumberValid_validInteger`** — `"1500"` → `true`.
2. **`test_isNumberValid_validDecimal`** — `"1,445.50"` → `true`.
3. **`test_isNumberValid_dashNotation`** — `"50,-"` → `true`.
4. **`test_isNumberValid_invalidAlphabetic`** — `"abc"` → `false`.
5. **`test_isNumberValid_invalidMultipleDecimals`** — `"12.34.56"` → `false`.
6. **`test_isNumberValid_empty`** — `""` → `true` (empty is valid per current logic).
7. **`test_isNumberValid_null`** — `null` → `true` (null is valid per current logic via `empty()` check).

#### Step 3.3 — Scanner subsystem end-to-end test (audit item M10)

**New file:** `tests/testsuites/Scanner/ScannerTests.php`

> Note: Also create the `tests/testsuites/Scanner/` directory and add a `Scanner` test suite entry to `phpunit.xml`.

Tests to implement:
1. **`test_scan_findsExpectedHashes`** — Create scanner from `Localization::createScanner()`, call `scan()`, verify `getCollection()` returns a non-empty `StringCollection` with known hashes from the example source files.
2. **`test_scan_fileCount`** — After scanning, verify the number of unique files found is > 0.
3. **`test_scan_executionTime`** — Verify `getExecutionTime()` returns a value > 0 after scanning.
4. **`test_load_fromStorageJson`** — After scanning (which saves to `storage.json`), create a new scanner instance and call `load()` → verify the loaded collection matches the scanned one.
5. **`test_load_missingFile`** — Create scanner with a non-existent storage file path → `load()` should be a no-op and not throw.

### WP-4: JS Client File Generation Investigation & Fix

#### Step 4.1 — Diagnose the regeneration issue

The `ClientFilesGenerator::writeFiles()` method at [line 131](src/Localization/Translator/ClientFilesGenerator.php#L131) has a caching mechanism:

```php
if(!$force && $this->getCacheKey() === self::getSystemKey()) {
    // skip
}
```

The system key is composed of three parts (line ~345):
```
Lib:{clientLibrariesCacheKey}|System:{libVersion}|Locales:{comma-separated-locale-IDs}
```

**Investigation findings from code analysis:**

1. **Call ordering dependency**: If `setClientLibrariesCacheKey()` is called AFTER `configure()`, the system key used during `writeFiles()` will have an empty `Lib:` segment. The cache key file is saved with empty `Lib:`. On the next request, if the same call order persists, the same empty-Lib system key is generated → should match. **This alone does not cause regeneration every request.**

2. **Potential root cause — unused cache properties**: The `$targetFolder` and `$cacheKeyFile` instance properties ([lines 23-24](src/Localization/Translator/ClientFilesGenerator.php#L23-L24)) are declared and reset in `handleFolderChanged()`, but `getTargetFolder()` and `getCacheKeyFile()` never read or write them. They create new `FolderInfo`/`FileInfo` instances on every call. This is dead code from an incomplete caching refactor.

3. **Potential root cause — translator reference stale after locale change**: The constructor captures `$this->translator = Localization::getTranslator()` at [line 28](src/Localization/Translator/ClientFilesGenerator.php#L28). When `selectAppLocale()` is called, `$translator` is set to `null` on the facade ([Localization.php line 479](src/Localization.php#L479)), but the generator still holds the old reference. This doesn't cause regeneration but could cause stale translations in the generated files.

4. **Most likely root cause — event-driven cache invalidation**: The generator registers `onLocaleChanged` and `onCacheKeyChanged` listeners in its constructor. When `Localization::configure()` is called, it creates the generator and immediately calls `writeFiles()`. If any locale changes or cache key changes happen AFTER this point in the bootstrap, they reset `$this->cacheKey = null` and `self::$systemKey = null`. While this doesn't trigger a new `writeFiles()` call in the SAME request, it means the generator's in-memory cache is invalidated. **If the consuming application calls `writeClientFiles()` explicitly at a later point** (e.g., after changing locales), the system key is recomputed with potentially different locale IDs, causing a rewrite.

5. **Another likely root cause — consumer application configuration**: The consuming application may be:
   - Calling `writeClientFiles(true)` (force mode) somewhere.
   - Setting a dynamic cache key (e.g., `setClientLibrariesCacheKey(time())`).
   - Having the `cachekey.txt` file in a directory that gets cleaned.
   - Adding locales after `configure()`, changing the `Locales:` segment pf the system key.

#### Step 4.2 — Code improvements to fix / prevent the issue

1. **Add a diagnostic method** to `ClientFilesGenerator`:
   ```php
   public function getWriteSkipReason() : ?string
   ```
   Returns `null` if files would be written (i.e., no skip), or a descriptive string like `"Cache key match: {stored} === {system}"` when skipped. This helps consumers diagnose why files are/aren't being regenerated.

2. **Add a static method** to `Localization`:
   ```php
   public static function getClientFilesDiagnostics() : array
   ```
   Returns `['storedKey' => ..., 'systemKey' => ..., 'match' => bool, 'folder' => ..., 'cacheKeyFile' => ...]`. This gives consuming applications a way to debug without enabling echo-based logging.

3. **Clean up dead code** in `ClientFilesGenerator`:
   - Remove the unused `$targetFolder` and `$cacheKeyFile` properties.
   - Remove the no-op resets in `handleFolderChanged()`.
   - OR, implement the intended instance-level caching for `getTargetFolder()` and `getCacheKeyFile()`.

4. **Document the required call ordering** in the `configure()` docblock: `setClientLibrariesCacheKey()` and all `addAppLocale()` calls MUST happen BEFORE `configure()` to ensure the system key is complete at write time.

5. **Add unit tests for timing scenarios** in `tests/testsuites/Translator/GeneratorTests.php`:
   - **`test_writeFiles_cacheKeyBeforeConfigure`** — Set cache key, then write → verify cache key file contains the full key → write again → assert `areFilesWritten()` returns `true` without re-writing (check file mtime).
   - **`test_writeFiles_localeAddedAfterWrite`** — Write files → add a new app locale → verify that the next `writeFiles()` regenerates (system key changed).
   - **`test_writeFiles_cacheKeyFilePersistedAcrossCalls`** — Write files → create a fresh generator instance → `areFilesWritten()` should return `true`.

### WP-5: Manifest Updates

Update the following manifest documents to reflect the changes:

| Document | What to update |
|---|---|
| [api-countries-currencies.md](docs/agents/project-manifest/api-countries-currencies.md) | `CurrencyNumberInfo::getDecimals()` return type change (`int` → `string`), constructor signature change. |
| [api-translator-editor.md](docs/agents/project-manifest/api-translator-editor.md) | Add `getWriteSkipReason()` method to `ClientFilesGenerator` API. |
| [api-core.md](docs/agents/project-manifest/api-core.md) | Add `getClientFilesDiagnostics()` to `Localization` facade API. |
| [constraints.md](docs/agents/project-manifest/constraints.md) | Add call-ordering requirement: `setClientLibrariesCacheKey()` and `addAppLocale()` must precede `configure()`. Update PHPUnit version reference. |
| [data-flows.md](docs/agents/project-manifest/data-flows.md) | Update Flow 5 (Client Library Generation) to document the cache key comparison logic and timing requirements. |

## Dependencies

- WP-1 (Bug Fixes) must complete before WP-3 Step 3.1 (CurrencyNumberInfo tests), since the tests verify the corrected behavior.
- WP-2 (PHPUnit config) should complete before WP-3 (tests), so the new Scanner suite is included.
- WP-1, WP-2, WP-3, and WP-4 can otherwise proceed in parallel.
- WP-5 (Manifest Updates) runs last, after all code changes are finalized.

## Required Components

### Modified Files

| File | Work Package |
|---|---|
| [src/Localization/Currencies/CurrencyNumberInfo.php](src/Localization/Currencies/CurrencyNumberInfo.php) | WP-1 (BUG-2 fix) |
| [src/Localization/Countries/CountryCurrency.php](src/Localization/Countries/CountryCurrency.php) | WP-1 (BUG-1 + BUG-2 caller fix) |
| [tests/testsuites/Parser/PHPParserTests.php](tests/testsuites/Parser/PHPParserTests.php) | WP-1 (BUG-3 fix) |
| [phpunit.xml](phpunit.xml) | WP-2 |
| [src/Localization/Translator/ClientFilesGenerator.php](src/Localization/Translator/ClientFilesGenerator.php) | WP-4 |
| [src/Localization.php](src/Localization.php) | WP-4 |
| [tests/testsuites/Translator/GeneratorTests.php](tests/testsuites/Translator/GeneratorTests.php) | WP-4 |

### New Files

| File | Work Package |
|---|---|
| `tests/testsuites/Currencies/CurrencyNumberInfoTests.php` | WP-3 |
| `tests/testsuites/Currencies/ValidationTests.php` | WP-3 |
| `tests/testsuites/Scanner/ScannerTests.php` | WP-3 |

### Updated Manifest Documents

| File | Work Package |
|---|---|
| [docs/agents/project-manifest/api-countries-currencies.md](docs/agents/project-manifest/api-countries-currencies.md) | WP-5 |
| [docs/agents/project-manifest/api-translator-editor.md](docs/agents/project-manifest/api-translator-editor.md) | WP-5 |
| [docs/agents/project-manifest/api-core.md](docs/agents/project-manifest/api-core.md) | WP-5 |
| [docs/agents/project-manifest/constraints.md](docs/agents/project-manifest/constraints.md) | WP-5 |
| [docs/agents/project-manifest/data-flows.md](docs/agents/project-manifest/data-flows.md) | WP-5 |

## Assumptions

- The `CurrencyNumberInfo` constructor signature change (`int $decimals` → `int|string $decimals`) is acceptable as a minor breaking change, justified by the data integrity bug it fixes. External consumers constructing `CurrencyNumberInfo` with integer decimals will still work correctly (the constructor casts to string).
- The `getDecimals()` return type change from `int` to `string` is an acceptable breaking change. Callers performing arithmetic on the return value will need to cast. This is the correct trade-off because the current `int` return type is semantically misleading — it cannot represent the actual decimal value faithfully.
- The example source files under `example/sources/` contain enough translatable strings to produce meaningful scanner test assertions.
- PHPUnit 12.5 supports all XML attributes used in the new `phpunit.xml`.
- The consumer application causing the JS regeneration issue is available for the developer to check call ordering if the library-side fixes don't resolve it.

## Constraints

- PHPStan level 8 must pass after all changes. The `CurrencyNumberInfo` type changes must be reflected consistently across all callers.
- No changes to auto-generated canned classes (`CannedCountries`, `CannedCurrencies`, `CannedLocales`).
- PHP 8.4+ syntax allowed.
- All new test classes must use `declare(strict_types=1)`.

## Out of Scope

- Adding tests for the Editor subsystem (untestable without refactoring `$_POST`/`$_SESSION` dependencies — flagged as technical debt, not addressed here).
- Adding formatting tests for the remaining 13 countries (audit item I1) — this is a completeness item, not a stability fix.
- Refactoring `Localization::reset()` to clear all static state (audit items M1, §4.2) — this is a larger architectural change that would affect all existing tests.
- Addressing the translator `load()`/`save()` round-trip tests (audit items H10, H12) — important but secondary to the bug fixes and scanner coverage.
- Translation function echo variant tests (audit items L19, L20).

## Acceptance Criteria

1. **BUG-1 fixed**: `CountryCurrency::isNumberValid("abc")` returns `false` (currently returns `true`).
2. **BUG-2 fixed**: `new CurrencyNumberInfo(1445, '05')->getString()` returns `"1445.05"` (currently returns `"1445.5"`).
3. **BUG-3 fixed**: Running `composer test` no longer produces a PHPUnit warning about class/filename mismatch, and all 3 PHP parser tests execute.
4. **`phpunit.xml` modernized**: No deprecated-attribute warnings. `composer test-suite -- Currencies` runs only currency tests.
5. **CurrencyNumberInfo tests pass**: 10 new tests covering `getString()`, `getFloat()`, `countDecimals()`, and `isNegative()`.
6. **Validation tests pass**: 7 new tests covering `isNumberValid()` with valid and invalid inputs.
7. **Scanner tests pass**: 5 new tests covering `scan()`, result inspection, `load()` from storage, and missing-file no-op.
8. **JS generation tests pass**: 3 new timing tests covering cache key persistence across calls, locale-change-triggered regeneration, and pre-configure cache key setting.
9. **Full test suite green**: `composer test` passes with 0 failures, 0 errors, 0 warnings.
10. **PHPStan clean**: `composer analyze` passes at level 8.
11. **JS regeneration addressed**: Either a library-side fix prevents unnecessary regeneration, or diagnostic tooling is in place to identify the consumer-side cause.

## Testing Strategy

1. **Run existing tests first** (`composer test`) to establish baseline and confirm the PHPUnit warning about `ParserPHPTest`.
2. **Apply BUG-1, BUG-2, BUG-3 fixes** → run `composer test` → verify no regressions and warning is gone.
3. **Update `phpunit.xml`** → run `composer test` → verify all suites discovered correctly.
4. **Add new test files** → run `composer test` → verify all new tests pass.
5. **Run `composer analyze`** after all changes to verify PHPStan level 8 compliance.
6. **For JS generation**: Enable `ClientFilesGenerator::setLoggingEnabled(true)` during test development to trace cache key comparisons. After adding diagnostic methods, write tests that assert on the diagnostics output.
7. **In the consumer application**: After the library changes, verify via `Localization::getClientFilesDiagnostics()` that the stored key matches the system key on the second+ request.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`CurrencyNumberInfo` breaking change** — Callers using `getDecimals()` as `int` will break. | The constructor still accepts `int`, so construction is backward compatible. Only `getDecimals()` changes return type. Search all call sites in the project (and document the change in the changelog) before releasing. |
| **Existing formatting tests may fail after BUG-2 fix** — The current `FormattingTests` pass because they test with `.45` decimals (2 digits, no leading zeros). | Verify that all 6 existing formatting tests still pass after the fix. The fix only changes behavior for leading-zero cases, which are not in the current test data. |
| **Scanner tests may be flaky** — Scanner execution time or file counts depend on the filesystem and example sources. | Use loose assertions (`assertGreaterThan(0, ...)`) rather than exact counts. Pin assertions to minimum expected counts. |
| **JS regeneration root cause is in consumer app** — Library-side changes may not fully resolve the issue. | The diagnostic method (`getClientFilesDiagnostics()`) provides a clear debugging path. Document the required call ordering prominently. |
| **PHPUnit suite name changes break CI** — If any CI pipeline uses `composer test-suite -- "Application Localization Tests"`. | The old catch-all suite name is being replaced with per-directory suites. Check CI configuration before merging. |
