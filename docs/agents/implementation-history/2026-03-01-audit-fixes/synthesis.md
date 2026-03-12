# Synthesis — Audit Issue Fixes

**Project:** `2026-03-01-audit-fixes`
**Date:** 2026-03-01
**Status:** All 5 work packages COMPLETE

---

## Summary

All ten audit findings have been addressed (nine directly implemented; CSRF protection deferred by design to a follow-up minor version bump). 5 work packages were planned, implemented, reviewed, and completed. 95/95 tests pass and PHPStan level 8 reports zero errors throughout.

---

## Work Packages — Outcomes

| WP | Title | Result | Files Changed |
|---|---|---|---|
| WP-001 | INI handling correctness | COMPLETE | `LocalizationTranslator.php`, `LocalizationWriter.php`, `TranslatorSpecialCharsTest.php` (new) |
| WP-002 | XSS hardening in editor | COMPLETE | `PageScaffold.php` |
| WP-003 | Code correctness (strict_types, error codes, reset()) | COMPLETE | `LocalizationException.php`, `CountryException.php`, `CountryCurrency.php`, `Localization.php` |
| WP-004 | Remove dead log() stub | COMPLETE | `Localization.php` |
| WP-005 | Manifest accuracy verification | COMPLETE | No changes — already accurate |

---

## Changes by File

### `src/Localization/Translator/LocalizationTranslator.php`
- Added `INI_SCANNER_RAW` flag to `parse_ini_file()` call (prevents PHP from coercing boolean-like strings such as "Yes" → `"1"`).
- Added unescape step immediately after `parse_ini_file()` to reverse the writer's escape encoding.

### `src/Localization/Translator/LocalizationWriter.php`
- Added private `escapeIniValue()` method encoding `\` → `\\`, `"` → `\"`, `\n` → `\n`, `\r` → `\r` in the correct order (backslash first to prevent double-encoding).

### `tests/testsuites/Translator/TranslatorSpecialCharsTest.php` (new)
- `test_roundTrip_specialCharacters()` — verifies double-quote, backslash, newlines, CR+LF, and semicolon survive a write/read cycle.
- `test_roundTrip_yesValue()` — regression test confirming `"Yes"` is preserved via `INI_SCANNER_RAW`.

### `src/Localization/Editor/Template/PageScaffold.php`
- Added private `esc(string $value): string` helper using `htmlspecialchars(ENT_QUOTES, 'UTF-8')`.
- Applied `$this->esc()` to all dynamic echo sites: translated text in `<textarea>`, name/hash attributes, URLs, file paths, source labels, warning messages, and hidden inputs.
- No public API changes; `esc()` is a private implementation detail.

### `src/Localization/LocalizationException.php`
- Added `ERROR_UNKNOWN_SOURCE_ID = 39014`
- Added `ERROR_UNKNOWN_SOURCE_ALIAS = 39015`

### `src/Localization/Countries/CountryException.php`
- Added `ERROR_NO_REGEX_DEFINED = 177703`

### `src/Localization/Countries/CountryCurrency.php`
- Updated `getRegex()` throw to use `CountryException::ERROR_NO_REGEX_DEFINED`.

### `src/Localization.php`
- Updated `getSourceByID()` throw to use `LocalizationException::ERROR_UNKNOWN_SOURCE_ID`.
- Updated `getSourceByAlias()` throw to use `LocalizationException::ERROR_UNKNOWN_SOURCE_ALIAS`.
- Implemented `Localization::reset()`: clears `$locales`, `$selected`, `$listeners`, `$listenersCounter`, `$translator`, `$generator`, `$clientCacheKey`, resets `ClientFilesGenerator` static cache, then re-adds and selects `BUILTIN_LOCALE_NAME` as both app and content locale. Configuration properties (`$configured`, `$storageFile`, `$clientFolder`) are intentionally preserved.
- Removed empty `log()` stub (had FIXME/TODO comment, no implementation, no call sites).

---

## Design Decisions

### reset() Configuration Preservation
`reset()` preserves `$configured`, `$storageFile`, and `$clientFolder`. Resetting `$configured` would cause `requireConfiguration()` to throw an exception in generator tests that call `reset()` mid-test without re-calling `configure()`. This is documented in the `reset()` docblock: _"Source registrations and configuration are preserved."_

### CSRF Protection Deferred
Adding CSRF token verification to the editor save action requires consumers to include the token in their integration code, which is a breaking API change. This is tracked as a follow-up recommendation for a minor version bump.

### renderText() bare htmlspecialchars()
`renderText()` in `PageScaffold.php` uses `htmlspecialchars($text)` without `ENT_QUOTES`. This is acceptable since its output appears only in block-level `<td>/<p>` contexts, not attribute contexts. Recommended for alignment to `esc()` in a future housekeeping pass.

---

## Manifest Documents — Final State

All manifest documents are accurate. No updates were required by WP-001 through WP-005 as the prior Developer agent had kept them current during implementation. Specific verification:

| Document | Verified Accurate |
|---|---|
| `api-events-exceptions.md` | ERROR_UNKNOWN_SOURCE_ID=39014, ERROR_UNKNOWN_SOURCE_ALIAS=39015, ERROR_NO_REGEX_DEFINED=177703 all present |
| `api-core.md` | `reset()` listed in Initialization & Configuration section |
| `api-translator-editor.md` | `LocalizationWriter` public API unchanged and accurate |
| `tech-stack.md` | Runtime and dev dependency tables match `composer.json` exactly |

---

## Test & Analysis Results

- **PHPUnit:** 95/95 tests pass throughout all WPs
- **PHPStan:** Level 8, zero errors throughout all WPs
- **Regressions:** None

---

## Follow-up Recommendations

1. **CSRF protection** — Add CSRF token verification to the editor save/scan action. Requires minor version bump and migration guidance for consumers. Medium priority.
2. **`renderText()` alignment** — Update `PageScaffold::renderText()` to use `$this->esc()` instead of bare `htmlspecialchars()` for consistency. Low priority, no security impact in current usage context.
