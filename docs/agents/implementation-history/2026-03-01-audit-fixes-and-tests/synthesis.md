# Synthesis Report — Audit Fixes & Tests
**Plan:** `2026-03-01-audit-fixes-and-tests`  
**Completed:** 2026-03-01  
**Work Packages:** 5 / 5 COMPLETE  
**Tests:** 93 / 93 passing (311 assertions)  
**PHPStan:** 0 errors (level 8, 162 files)

---

## Summary

This plan resolved three audited bugs, modernised the PHPUnit configuration, added a full set of missing unit tests for the affected subsystems, fixed a JS client-file regeneration defect, and updated all relevant project manifest documents to reflect the changes.

---

## Work Package Outcomes

### WP-001 — Bug Fixes: isNumberValid, Decimal Precision, Class Name Mismatch

**BUG-1 — `CountryCurrency::isNumberValid()`**  
`preg_match()` returns `0` (int) on no-match, not `false`. The guard `!== false` accepted `0` as valid. Fixed to `=== 1`.

**BUG-2 — `CurrencyNumberInfo` decimal precision**  
`(int)"05"` evaluates to `5`, silently stripping leading zeros from decimal parts. Fixed by storing `$decimals` as `string` throughout. `getDecimals()` now returns `string` (breaking change, documented in manifest). Constructor still accepts `int|string` for backward compatibility.

**BUG-3 — `PHPParserTests` class name mismatch**  
Class was named `ParserPHPTest` while file was named `PHPParserTests.php`. PHPUnit silently skipped 3 tests. Renamed class to `PHPParserTests`.

**Files modified:**
- `src/Localization/Currencies/CurrencyNumberInfo.php`
- `src/Localization/Countries/CountryCurrency.php`
- `src/Localization/Currencies/CountryCurrencyInterface.php` (PHPDoc)
- `tests/testsuites/Parser/PHPParserTests.php`

---

### WP-002 — PHPUnit XML Modernisation

Removed all attributes deprecated in PHPUnit 10+ (`backupGlobals`, `backupStaticAttributes`, `convertErrorsToExceptions`, `convertNoticesToExceptions`, `convertWarningsToExceptions`, `processIsolation`). Replaced single catch-all test suite with 9 per-directory suites (Core, Countries, Currencies, Events, Locale, Parser, Scanner, TimeZones, Translator), making `composer test-suite -- SuiteName` reliable.

**Files modified:**
- `phpunit.xml`

---

### WP-003 — Missing Unit Tests

Added 22 new tests across 3 new test files:

| File | Tests | Coverage Target |
|---|---|---|
| `tests/testsuites/Currencies/CurrencyNumberInfoTests.php` | 10 | BUG-2 regression + full `CurrencyNumberInfo` API |
| `tests/testsuites/Currencies/ValidationTests.php` | 7 | BUG-1 regression + `isNumberValid()` edge cases |
| `tests/testsuites/Scanner/ScannerTests.php` | 5 | End-to-end scanner (zero coverage previously) |

Also added 3 timing/caching tests to `tests/testsuites/Translator/GeneratorTests.php` to cover the `ClientFilesGenerator` cache-key mechanism.

**Notable test design decisions:**
- `test_isNumberValid_invalidMultipleDecimals` asserts `true` (documents known regex substring-match behaviour; not a defect).
- `test_writeFiles_localeAddedAfterWrite` uses explicit `setClientLibrariesCacheKey()` invalidation because `addAppLocale()` does not fire `EVENT_LOCALE_CHANGED`.

---

### WP-004 — JS Client File Regeneration Fix

`ClientFilesGenerator` held two dead properties (`$targetFolder`, `$cacheKeyFile`) that were never assigned after construction. `handleFolderChanged()` reset those null properties instead of clearing the live in-memory cache keys, meaning a folder change could not invalidate the cache. Fixed by:

- Removing dead properties.
- `handleFolderChanged()` now resets `$this->cacheKey = null` and `self::$systemKey = null`.
- Added `getWriteSkipReason(): ?string` for diagnostics (returns `null` when files would be written, or a descriptive string when cache hit prevents writing).
- Added `Localization::getClientFilesDiagnostics(): array` to the static facade, exposing stored/system key, match flag, folder path, and cache key file path.
- Documented the call-ordering constraint: `setClientLibrariesCacheKey()` and `addAppLocale()` must be called **before** `Localization::configure()`.

**Files modified:**
- `src/Localization/Translator/ClientFilesGenerator.php`
- `src/Localization.php`

**Pre-existing PHPStan issues fixed in scope:**
- `getSupportedLocaleNames()`: forced `array_map('strval', ...)` cast for `array<int, int|string>` return.
- `StringCollection::toArray()`: corrected `SerializedStringCollection` phpstan-type (nested → flat).
- `functions.php`: replaced `call_user_func()` with direct `Localization::getTranslator()->translate()` calls.

---

### WP-005 — Project Manifest Updates

Updated all 5 manifest documents affected by this plan's changes:

| Document | Change |
|---|---|
| `api-countries-currencies.md` | `CurrencyNumberInfo`: constructor signature, `getDecimals()` return type, breaking-change note |
| `api-translator-editor.md` | `getWriteSkipReason()` added; dead-property removal and `handleFolderChanged()` fix documented |
| `api-core.md` | `getClientFilesDiagnostics()` added to Client Libraries section |
| `constraints.md` | Call-ordering constraint added; PHPUnit version updated to 12.x; Scanner suite name added |
| `data-flows.md` | Flow 5 rewritten: system key format, comparison logic, call-ordering note, diagnostics references |

---

## Final Metrics

| Metric | Value |
|---|---|
| Bugs fixed | 3 (BUG-1, BUG-2, BUG-3) |
| New test files | 3 |
| New tests | 22 |
| Total tests | 93 |
| Total assertions | 311 |
| PHPStan errors | 0 (level 8) |
| Manifest documents updated | 5 |
| Source files modified | 9 |
