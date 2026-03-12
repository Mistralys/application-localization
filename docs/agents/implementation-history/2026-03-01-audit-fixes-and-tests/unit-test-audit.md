# Unit Test Audit: Application Localization

**Audited:** 2026-03-01
**Agent:** Unit Test Auditor v1.0.1
**Baseline:** 65 tests, 260 assertions | 1 PHPUnit warning (class/filename mismatch)

---

## 1. Executive Summary

* **Current State:** The existing test suite covers the "happy path" for core locale management, event dispatch, parser text extraction, basic currency formatting for 6 countries, collection enumeration, client file generation, INI writer quote escaping, and basic translation function behavior. Missing entirely: Scanner subsystem tests, Translator `load()`/`save()` round-trip tests, `CountryCurrency` number parsing edge cases, `CurrencyNumberInfo` decimal precision, `Localization` facade error paths, Source exclusion logic, and all Editor/Filter logic.
* **Top Risk:** **`CountryCurrency.normalizeNumber()` / `tryParseNumber()` and `CurrencyNumberInfo` have data-integrity bugs** — leading-zero decimals are silently lost (e.g., `"1,445.05"` → `CurrencyNumberInfo(1445, 5)` → `getString()` returns `"1445.5"` instead of `"1445.05"`), and `isNumberValid()` uses `preg_match(...) !== false` instead of `=== 1`, so it always returns `true` for non-empty input. These are used in financial calculations across the entire application.

---

## 2. Existing Coverage Map

| Test Suite | File | Test Count | What's Covered |
|---|---|---|---|
| Core | `LocalizationCoreTests.php` | 15 | Default locales, add/select locales (app/content/custom), locale name lists, locale existence checks, form select injection, currency shortcuts |
| Countries | `CountryCollectionTests.php` | 3 | Alias resolution (`uk`→`gb`), ISO filtering, all 19 countries enumerated |
| Currencies | `CurrencyTests.php` | 4 | `choose()`, `getByISO()`, `isoExists()`, EUR country list |
| Currencies | `FormattingTests.php` | 6 | Format/normalize/makeReadable for US, DE, FR, MX, CA, UK |
| Events | `EventTests.php` | 4 | Locale changed event dispatch, args passthrough, unchanged no-op, namespace info |
| Locale | `LocaleCollectionTests.php` | 1 | All 18 locales enumerated |
| Locale | `LocaleTests.php` | 5 | Language code, country code, name normalization, `isNative()`, label |
| Parser | `ParserTests.php` | 2 | Unsupported file exception, file-not-found exception |
| Parser | `PHPParserTests.php` | 3 | PHP text extraction, warnings, string parsing |
| Parser | `JavaScriptParserTests.php` | 3 | JS text extraction, warnings, string parsing |
| TimeZones | `TimeZoneTests.php` | 5 | Find by ISO, find by locale, UTC contains all countries, UTC contains all locales, CET European countries |
| Translator | `TranslatorTests.php` | 1 | Bundled `de_DE` translations load correctly |
| Translator | `TranslationTests.php` | 4 | `t()` passthrough, placeholder substitution, `tex()` context, missing `%s` exception |
| Translator | `WriterTests.php` | 1 | INI file quote escaping |
| Translator | `GeneratorTests.php` | 4 | File list count, write + no-rewrite, disabled folder, cache key rewrite |
| **Total** | | **61** (+4 data-driven loops) | |

---

## 3. Recommended Tests (Categorized)

### 3.1 — CRITICAL (HIGH Stability Value)

| # | Component / Function | Test Description | Reasoning |
|---|---|---|---|
| H1 | `CurrencyNumberInfo::getString()` | Test with `new CurrencyNumberInfo(1445, 5)` — assert returns `"1445.05"` not `"1445.5"`. Test `(100, 0)` → `"100.0"`. Test `(0, 50)` → `"0.50"`. | **Known bug:** `int` decimals lose leading zeros. `5` should represent `05` (2 decimal places), but `getString()` returns `"1445.5"`. This propagates to `getFloat()` and all downstream formatting. Financial data integrity at stake. |
| H2 | `CurrencyNumberInfo::countDecimals()` | Test `(100, 5)` vs `(100, 50)` vs `(100, 500)` — verify correct decimal digit counting. | `countDecimals()` uses `strlen((string)$this->decimals)` — `5` → 1 digit, `50` → 2 digits. But the original value `0.05` had 2 decimal places, yet `(int)"05"` = `5` (1 digit). |
| H3 | `CurrencyNumberInfo::isNegative()` | Test with negative number, zero, and positive number. | Simple but never tested; guards all negative-sign rendering in `makeReadable()`. |
| H4 | `CountryCurrency::isNumberValid()` | Test with valid numbers, invalid input (`"abc"`, `"12.34.56"`), and empty values. Assert that invalid numbers return `false`. | **Known bug:** `preg_match() !== false` always returns `true` except on regex engine errors. Should be `=== 1`. Until fixed, tests should document the broken behavior and track the fix. |
| H5 | `CountryCurrency::normalizeNumber()` | Test all 4 branches: (a) Both `.` and `,` present: `"1.445,50"` → `"1445.50"` for DE. (b) Full notation with country separators: `"1.445,50"` for DE → `"1445.50"`. (c) Comma-only fallback: `"1445,50"` → `"1445.50"`. (d) Already normalized: `"1445.50"` → `"1445.50"`. (e) Spaces stripped: `"1 445,50"` → `"1445.50"`. (f) `CurrencyNumberInfo` passthrough. (g) Empty/null → `""`. | The core normalization function with 4+ conditional branches has only been tested implicitly through `formatNumber()`. Direct testing of each branch would catch regressions. |
| H6 | `CountryCurrency::tryParseNumber()` | Test: `null` → `null`, `""` → `null`, `CurrencyNumberInfo` passthrough, `"1,445.50"` for US, `"1.445,50"` for DE, `"50,-"` (European dash notation), integer `0`. | Core parsing with multiple type-accepting input. |
| H7 | `CountryCurrency::parseNumber()` | Test that invalid unparseable input throws `CountryException` with code `ERROR_CANNOT_PARSE_CURRENCY_NUMBER`. | Exception path is never tested. |
| H8 | `CountryCurrency::formatNumber()` | Test decimal position parameter: `formatNumber(1445.456, 0)` → `"1,445"` (US), `formatNumber(1445.456, 3)` → `"1,445.456"` (US). Test `null`/empty → `""`. | Only tested with default 2 decimals via `FormattingTests`. |
| H9 | `LocalizationTranslator::translate()` | Test: (a) English fallback when no translation exists. (b) Multiple numbered placeholders `%1$s`, `%2$d` with args. (c) `sprintf` failure with mismatched placeholders throws `ERROR_INCORRECTLY_TRANSLATED_STRING`. (d) Translated text replaces original when translation is loaded. (e) Hash caching: same text translates the same way on second call. | Only `t()` wrapper is tested, not the underlying `translate()` method directly. The `sprintf` error handling, hash caching, and locale-specific lookup are untested. |
| H10 | `LocalizationTranslator::load()` | Test: (a) Loading from a valid INI file populates strings. (b) Malformed INI throws `ERROR_CANNOT_PARSE_LOCALE_FILE`. (c) Missing file is silently skipped. (d) Multiple sources merge correctly (later overrides earlier for same hash). | The `load()` method has never been tested. It's the core data loading path. |
| H11 | `LocalizationTranslator::setTargetLocale()` | Test: (a) Setting same locale twice does not reload. (b) Changing locale triggers reload. (c) Translator state is updated. | State management logic untested. |
| H12 | `LocalizationTranslator::save()` | Test round-trip: set translations → save → new translator instance → load → verify translations match. Test that server file contains all hashes but client file only contains JS hashes. | The save/load round-trip is never tested end-to-end. |

### 3.2 — HIGH (Medium-High Stability Value)

| # | Component / Function | Test Description | Reasoning |
|---|---|---|---|
| M1 | `Localization::reset()` | Test that `reset()` clears locales and selections but preserves sources, storageFile, clientFolder, configured state, and listeners. Verify built-in locale is re-added and re-selected in both namespaces. | `reset()` is called in many test `setUp()` methods but its behavior is never directly verified. The incomplete reset (not clearing sources/listeners) is a footgun for test isolation. |
| M2 | `Localization::configure()` | Test: (a) Sets `isConfigured()` to `true`. (b) Triggers `writeClientFiles()` only when folder is non-empty. (c) Calling without sources should still succeed (lazy validation). | `configure()` is called in bootstrap but never tested as a unit. |
| M3 | `Localization::addSourceFolder()` | Test: (a) Source is retrievable by ID and alias after adding. (b) Sources are sorted by label. (c) Duplicate alias handling. | Source management has zero dedicated tests. |
| M4 | `Localization::getSourceByID()` / `getSourceByAlias()` | Test that invalid ID/alias throws an exception. | Error path untested. |
| M5 | `Localization::addEventListener()` | Test: (a) Custom event name accepted. (b) Multiple listeners on same event. (c) Listener receives correct arguments. | Only `onLocaleChanged` is tested. The generic `addEventListener` and other event types (`CacheKeyChanged`, `ClientFolderChanged`) are untested. |
| M6 | `Localization::onCacheKeyChanged` | Test that setting a new cache key fires the event, and setting the same key does not. | Event never tested. |
| M7 | `Localization::onClientFolderChanged` | Test that setting a new folder fires the event, and setting the same folder does not. | Event never tested. |
| M8 | `Localization::requireConfiguration()` | Test the three error paths: not configured (`ERROR_CONFIGURE_NOT_CALLED`), no storage file (`ERROR_NO_STORAGE_FILE_SET`), no sources (`ERROR_NO_SOURCES_ADDED`). | None of the configuration-requirement exceptions are tested. |
| M9 | `Localization::countLocalesByNS()` | Test counting app, content, and custom namespace locales. | Count methods never tested. |
| M10 | `LocalizationScanner::scan()` | Test end-to-end: register source folder → scan → verify hashes found → verify file count → verify execution time > 0. | The scanner subsystem has **zero** tests. |
| M11 | `LocalizationScanner::load()` | Test: (a) Load from existing storage.json. (b) Load when file doesn't exist (no-op). (c) Load with version mismatch deletes file. (d) Idempotent (second call is no-op). | Scanner loading never tested. |
| M12 | `StringCollection::addFromFile()` / `getHashes()` | Test: Add strings from multiple files → verify hash grouping, source ID filtering, language ID filtering. | StringCollection has zero tests. |
| M13 | `StringCollection::toArray()` / `fromArray()` | Test round-trip serialization. Test version mismatch returns `false`. | Serialization round-trip never tested. |
| M14 | `StringHash` | Test: `getText()` on empty hash returns `null`. `countFiles()` deduplicates. `hasSourceID()` filters correctly. `getSearchString()` includes text and file names. | StringHash has zero tests. |
| M15 | `FolderLocalizationSource::excludeFolder()` | Test that excluded folders are actually skipped during scan. Test that `excludeFiles()` skips specific files. | Exclusion is configured in bootstrap but never verified. |
| M16 | `LocalizationWriter::writeFile()` | Test with INI-special characters: semicolons (`;`), equals (`=`), square brackets (`[`), newlines in translation text. Verify the resulting file can be parsed by `parse_ini_file()`. | Only double-quote escaping is tested. Other INI-problematic characters could corrupt the file. |
| M17 | `LocalizationTranslator::setTranslation()` / `clearTranslation()` | Test set → verify exists → clear → verify gone. | Mutable state operations never tested. |
| M18 | `LocalizationTranslator::hashExists()` / `translationExists()` | Test with existing and non-existing hashes/texts. | Lookup methods never tested. |
| M19 | `LocalizationTranslator::getClientStrings()` | Test that only client INI file strings are returned. | Client-specific string retrieval never tested. |

### 3.3 — MEDIUM (Standard Value)

| # | Component / Function | Test Description | Reasoning |
|---|---|---|---|
| L1 | `Text::toArray()` / `fromArray()` | Test round-trip serialization preserves text, line, explanation, and hash. | Value object serialization never tested. |
| L2 | `Text::isEmpty()` | Test with empty string and non-empty string. | Trivial but used as a filter in parsing. |
| L3 | `Text::getHash()` | Test that hash is stable MD5 of text. | Hash consistency is critical for the entire translation system. |
| L4 | `LocalizationParser::getLanguageIDs()` | Test returns expected IDs. Currently returns extension keys (`js`, `php`) — verify and document. | API documentation says IDs are like `"PHP"`, `"Javascript"`, but implementation may return extensions. |
| L5 | `LocalizationParser::isExtensionSupported()` | Test `php` → true, `js` → true, `txt` → false, `jsx` → false. | Only tested implicitly via exception path. |
| L6 | `LocalizationParser::createLanguage()` | Test that instances are cached (same object returned on second call). Test case-insensitive ID matching. | Parser caching behavior untested. |
| L7 | `CountryCurrency::makeReadable()` | Test with `$addSymbol = false`. Test with zero amount. Test with very large numbers. | Only tested with symbol. The no-symbol path and edge values are untested. |
| L8 | `CountryCurrency::getExamples()` | Test with `$decimalPositions = 0` and `$decimalPositions = 2`. Verify output matches country formatting. | Never tested. |
| L9 | `LocaleInterface::getCountry()` | Test that each locale returns the correct country instance. | Locale-to-country mapping never directly tested. |
| L10 | `LocaleInterface::getCurrency()` | Test that each locale returns the correct currency instance. | Locale-to-currency mapping never directly tested. |
| L11 | `LocaleInterface::getAliases()` | Test alias resolution for locales with known aliases (`en_UK` → `en_GB`). | Alias behavior indirectly tested but never as a unit. |
| L12 | `LocalesCollection::filterName()` | Test normalization: `en_UK` → `en_GB`, pass-through for standard names, unknown name handling. | Alias normalization never directly tested on the collection. |
| L13 | `CountryCollection::getAliases()` | Test the full alias map. | Never tested. |
| L14 | `CountryInterface::getTimeZone()` | Test that each country returns a valid timezone instance. | Country-to-timezone links never tested. |
| L15 | `TimeZoneCollection::getCountryTimeZones()` / `getGlobalTimeZones()` | Test basket contents and counts. | Basket accessors never tested. |
| L16 | `ClientFilesGenerator::getFilesList()` | Test with no non-native locales (should return only library files). Test file paths are correctly constructed. | Only tested indirectly in `GeneratorTests`. |
| L17 | `BaseCountry::getMainLocale()` | Test that each country returns the expected locale instance. | Country-locale bidirectional links never tested. |
| L18 | `Localization::getVersion()` | Test returns non-empty string matching `version.txt`. | `getVersionFile()->exists()` is tested but not the actual version string. |
| L19 | `functions.php` — `pt()`, `pts()` | Test that `pt()` echoes the text (use output buffering). Test that `pts()` appends a trailing space. | Echo variants never tested. |
| L20 | `functions.php` — `ptex()`, `ptexs()` | Test echo variants with context parameter. | Echo+context variants never tested. |

### 3.4 — LOW (Informational / Completeness)

| # | Component / Function | Test Description | Reasoning |
|---|---|---|---|
| I1 | `CountryCurrency` for remaining countries | Add formatting tests for AT, BE, CH, ES, FI, IE, IT, NL, PL, RO, SE, SG, ZZ. | Only 6 of 19 countries have formatting tests. |
| I2 | `Localization::isActiveAppLocale()` / `isActiveContentLocale()` | Test with active and inactive locales. | Simple boolean checks, never tested. |
| I3 | `BaseLocalizationEvent::getArgument()` | Test with valid and out-of-bounds indices. | Argument access never tested. |
| I4 | `CollectionWarning` | Test serialization and accessors. | Simple data class, never tested. |
| I5 | `ParserWarning` | Test accessors return expected values. | Warnings are checked for count but individual accessor results are untested. |
| I6 | `StringInfo::isJavascript()` / `isPHP()` | Test language type detection. | Never tested. |

---

## 4. Technical Debt Observations

### 4.1 — Potential Bugs Found During Audit

| ID | File | Issue | Severity |
|---|---|---|---|
| BUG-1 | [CountryCurrency.php](src/Localization/Countries/CountryCurrency.php#L97) | `isNumberValid()` uses `preg_match(...) !== false` — this returns `true` even when the regex doesn't match (returns `0`). Should use `=== 1`. | **HIGH** — validation never rejects invalid numbers. |
| BUG-2 | [CurrencyNumberInfo.php](src/Localization/Currencies/CurrencyNumberInfo.php#L88) | `getString()` returns `$number . '.' . $decimals` where `$decimals` is `int` — leading zeros are lost. `new CurrencyNumberInfo(100, 5)` → `"100.5"` instead of `"100.05"`. This propagates through `getFloat()`, `formatNumber()`, and all formatting. | **HIGH** — financial data silently corrupted. |
| BUG-3 | [PHPParserTests.php](tests/testsuites/Parser/PHPParserTests.php) | Class is named `ParserPHPTest` but file is `PHPParserTests.php` — PHPUnit cannot find the class, resulting in a warning and the 3 tests in this file being silently skipped. | **MEDIUM** — 3 tests are not actually running. |

### 4.2 — Test Infrastructure Issues

| Issue | Impact | Recommendation |
|---|---|---|
| **Static global state in `Localization`** | `reset()` only clears locales/selections but not sources, listeners, configured flag, storageFile, clientFolder, translator, or generator. Tests leak state. | Consider a `fullReset()` for testing that clears all static properties, or use `@runInSeparateProcess` for critical isolation tests. |
| **`phpunit.xml` uses deprecated attributes** | `convertErrorsToExceptions`, `convertNoticesToExceptions`, `convertWarningsToExceptions` are deprecated in PHPUnit 10+ and removed in 12. `backupStaticAttributes` is also deprecated. | Update `phpunit.xml` to remove deprecated attributes. The project uses PHPUnit 12.5 but the config has PHPUnit 9 options. |
| **No test suites defined in `phpunit.xml`** | Only a single catch-all suite exists. Cannot run targeted suite groups via `composer test-suite`. | Define per-directory suites matching the folder structure (Core, Countries, Currencies, Events, Locale, Parser, TimeZones, Translator). |
| **Bootstrap calls `Localization::configure()`** | Every test inherits a pre-configured `Localization` with a source folder — no way to test the unconfigured state or test with different source configurations. | Add a separate bootstrap or helper that delays `configure()`, or accept the limitation and test error paths via mocking/process isolation. |
| **Editor is untestable** | `LocalizationEditor::executeSave()` reads `$_POST` directly; `redirect()` calls `exit`; `EditorFilters` depends on `$_SESSION`. | Not a test gap per se, but a testability debt. Refactoring to accept a Request object would enable testing. Flag only — no immediate action needed. |

### 4.3 — Class/File Naming Mismatch

The file [tests/testsuites/Parser/PHPParserTests.php](tests/testsuites/Parser/PHPParserTests.php) contains class `ParserPHPTest` instead of `PHPParserTests`. This causes PHPUnit to skip the file with a warning. **3 tests are silently not running.** Rename the class to `PHPParserTests` to match the file name (or vice versa).

---

## 5. Prioritized Implementation Roadmap

### Phase 1 — Fix Bugs & Restore Missing Tests (Immediate)
1. Fix the class name mismatch in `PHPParserTests.php` (restores 3 tests)
2. Write tests for `CurrencyNumberInfo` to document the decimal precision bug (H1, H2, H3)
3. Write tests for `CountryCurrency::isNumberValid()` to document the validation bug (H4)
4. File bug reports / fix BUG-1 and BUG-2

### Phase 2 — Critical Business Logic Tests (1-2 days)
5. `CountryCurrency` normalization and parsing edge cases (H5, H6, H7, H8)
6. `LocalizationTranslator` core methods (H9, H10, H11, H12)
7. `LocalizationWriter` INI special character escaping (M16)

### Phase 3 — Integration & State Management Tests (2-3 days)
8. `Localization` facade tests: reset behavior, configure, source management, error paths (M1–M9)
9. Scanner subsystem: scan, load, StringCollection, StringHash (M10–M14)
10. Source exclusion logic (M15)
11. Translator state operations (M17–M19)

### Phase 4 — Coverage Completeness (Ongoing)
12. Parser utility methods (L1–L6)
13. Remaining currency formatting countries (I1)
14. Translation function echo variants (L19, L20)
15. Collection/basket accessors (L13–L17)

---

## 6. PHPUnit Configuration Fix

The current `phpunit.xml` contains deprecated attributes from PHPUnit 9 that are no longer valid in PHPUnit 12.5. Recommended replacement:

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
