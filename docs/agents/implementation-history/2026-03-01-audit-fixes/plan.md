# Plan — Audit Issue Fixes

## Summary

Fix all issues identified in the March 2026 codebase audit. The library is stable (PHPStan level 8 clean, 93 tests passing), but the audit found: two INI handling bugs that silently corrupt translations, XSS vulnerabilities in the editor UI, missing `declare(strict_types=1)` in three files, three exceptions without error codes, an incomplete `reset()` method, a dead `log()` stub, stale manifest documentation, and no CSRF protection on the editor save action. This plan addresses all ten findings in priority order.

## Architectural Context

- **Static facade**: `Localization` (`src/Localization.php`) — entirely static, all state in `protected static`/`private static` properties.
- **INI storage**: Translations are written by `LocalizationWriter` (`src/Localization/Translator/LocalizationWriter.php`) and read by `LocalizationTranslator` (`src/Localization/Translator/LocalizationTranslator.php`) using `parse_ini_file()`.
- **Editor UI**: `LocalizationEditor` (`src/Localization/Editor/LocalizationEditor.php`) handles actions (scan, save); `PageScaffold` (`src/Localization/Editor/Template/PageScaffold.php`) renders the full HTML page.
- **Exception conventions**: All exceptions extend `AppUtils\BaseException` via `LocalizationException` (`src/Localization/LocalizationException.php`). Each subsystem has a distinct error code range (39xxx = Core, 1777xx = Countries). The third constructor parameter is always an integer error code constant.
- **Manifest docs**: `docs/agents/project-manifest/` is the canonical source of truth for AI agents.

## Approach / Architecture

The fixes are organized into five work packages, ordered by severity and dependency:

1. **WP-1: INI handling correctness** — Fix `parse_ini_file()` scanner mode and `LocalizationWriter` escaping.
2. **WP-2: XSS hardening** — Escape all dynamic output in `PageScaffold.php`, using `htmlspecialchars()` consistently.
3. **WP-3: Code correctness** — Add missing `declare(strict_types=1)`, add error codes to exceptions, fix `reset()`.
4. **WP-4: Cleanup** — Remove or implement `log()` method.
5. **WP-5: Manifest update** — Synchronize `tech-stack.md` with actual `composer.json` dependency versions.

CSRF protection (finding #10) is scoped as a follow-up recommendation due to its broader impact on the editor integration API.

## Rationale

- **INI fixes first** because they cause silent data corruption in production — users lose translations without any error.
- **XSS second** because the editor is typically used in trusted internal environments, but the risk is real and the fix is straightforward.
- **Code correctness third** because the strict_types/error codes are convention violations that don't cause runtime failures today.
- **Manifest last** because it only affects agent workflows, not runtime behavior.
- **CSRF deferred** because adding token verification requires changes to the editor integration API (consumers must include the token), which is a breaking change that needs a minor version bump and migration guidance.

## Detailed Steps

### WP-1: INI Handling Correctness

#### Step 1.1 — Add `INI_SCANNER_RAW` to `parse_ini_file()`

**File:** `src/Localization/Translator/LocalizationTranslator.php` (line 133)

Change:
```php
$data = parse_ini_file($file);
```
To:
```php
$data = parse_ini_file($file, false, INI_SCANNER_RAW);
```

**Why:** Without `INI_SCANNER_RAW`, PHP interprets bare values `yes`/`no`/`true`/`false`/`null`/`on`/`off`/`none` as PHP constants. A translation whose text is literally "Yes" would be read back as `"1"`, silently corrupting the translation.

#### Step 1.2 — Fix INI value escaping in `LocalizationWriter`

**File:** `src/Localization/Translator/LocalizationWriter.php` (lines 74–78, `renderHashes()`)

The current escaping only handles double quotes:
```php
str_replace('"', '\"', $entry['text'])
```

Replace with a proper escaping method that also handles:
- Backslashes: `\` → `\\` (must be escaped first to avoid double-escaping)
- Newlines: `\n` → `\\n`, `\r` → `\\r`
- Semicolons: `;` — Not actually dangerous inside double-quoted INI values (only in unquoted values), but for defense-in-depth, ensure values are always double-quoted (they already are).

Create a private `escapeIniValue(string $text): string` method:
```php
private function escapeIniValue(string $text) : string
{
    return str_replace(
        ['\\', '"', "\n", "\r"],
        ['\\\\', '\\"', '\\n', '\\r'],
        $text
    );
}
```

Update `renderHashes()` to use it:
```php
$lines[] = sprintf(
    '%s= "%s"',
    $entry['hash'],
    $this->escapeIniValue($entry['text'])
);
```

**Note:** Since `parse_ini_file()` with `INI_SCANNER_RAW` does NOT process escape sequences, we need to also add a corresponding unescape step in the reader. After Step 1.1, the reader will return raw strings including the literal `\\n` sequences. Add an unescape step in `LocalizationTranslator` after `parse_ini_file()`:

**File:** `src/Localization/Translator/LocalizationTranslator.php`, after the `parse_ini_file()` call and its error handling block.

Add unescaping of the read values:
```php
// Unescape INI escape sequences that the writer encodes
$data = array_map(
    static fn(string $value): string => str_replace(
        ['\\n', '\\r', '\\"', '\\\\'],
        ["\n", "\r", '"', '\\'],
        $value
    ),
    $data
);
```

**Important:** The order of replacements matters — `\\\\` → `\` must be last to avoid corrupting `\\n` → `\n` sequences.

#### Step 1.3 — Add a translator test for special characters

**File:** New file `tests/testsuites/Translator/TranslatorSpecialCharsTest.php`

Write a test that:
1. Creates a `LocalizationWriter`, adds a hash with text containing `"`, `\n`, `\`, and `;`.
2. Writes the file to a temp location.
3. Reads it back with `parse_ini_file($file, false, INI_SCANNER_RAW)` + the unescape logic.
4. Asserts round-trip fidelity.

Also add a test for the word "Yes" surviving a round-trip (regression for the `INI_SCANNER_RAW` fix).

---

### WP-2: XSS Hardening in Editor

#### Step 2.1 — Create an `esc()` helper method in `PageScaffold`

**File:** `src/Localization/Editor/Template/PageScaffold.php`

Add a private helper:
```php
private function esc(string $value) : string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

#### Step 2.2 — Escape all dynamic output in `PageScaffold`

Apply `$this->esc()` to every dynamic value echoed into HTML. The specific sites:

| Line(s) | Expression | Context | Fix |
|---------|-----------|---------|-----|
| ~58 | `$this->editor->getAppName()` | `<title>` | `$this->esc(...)` |
| ~69 | `$this->editor->getURL()` | `href` attribute | `$this->esc(...)` |
| ~69 | `$this->editor->getAppName()` | link text | `$this->esc(...)` |
| ~80 | `$backURL` | `href` attribute | `$this->esc(...)` |
| ~82 | `$this->editor->getBackButtonLabel()` | link text | `$this->esc(...)` |
| ~117 | warning file/line | `<dt>` text | `$this->esc(...)` |
| ~118 | `$warning->getMessage()` | `<dd>` text | `$this->esc(...)` |
| ~155 | `$name` / `$value` in hidden inputs | `name`/`value` attributes | `$this->esc(...)` |
| ~254 | `$hash` | `onclick` JS + `data-hash` | `$this->esc(...)` (hash is MD5, but defense-in-depth) |
| ~265 | `$string->getTranslatedText()` | `<textarea>` content | `$this->esc(...)` — **highest priority** |
| ~274 | `$explanation` | `<span>` content | `$this->esc(...)` |
| ~325 | `$file` | `<li>` content | `$this->esc(...)` |
| ~419 | `$activeLocale->getLabel()` | dropdown text | `$this->esc(...)` |
| ~425 | `$locale->getLabel()` | dropdown link text | `$this->esc(...)` |
| ~445 | tooltip title | `title` attr | `$this->esc(...)` |
| ~489 | `$source->getLabel()` | dropdown text | `$this->esc(...)` |
| ~497 | `$title` (tex result) | `title` attr | `$this->esc(...)` |
| ~624 | `$def['type']` | CSS class | `$this->esc(...)` |
| ~624 | `$def['text']` | alert body | `$this->esc(...)` |

**Approach:** Systematically go through the file and wrap each `echo $variable` with `$this->esc()`, except where the value is already passed through `renderText()` (which already calls `htmlspecialchars()`), or where the value is a known-safe integer (e.g., `$pager` page numbers, `$scanner->countWarnings()`).

URLs generated by `getURL()`, `getSourceURL()`, `getLocaleURL()`, `getPaginationURL()` use `http_build_query()` which already URL-encodes parameters — but when placed in `href` attributes they still need HTML-escaping for `&` → `&amp;`. The `$this->esc()` call handles this.

#### Step 2.3 — Escape session-based messages

**File:** `src/Localization/Editor/Template/PageScaffold.php` (line ~624)

The message types (`info`, `danger`, `warning`, `success`) come from constants in `LocalizationEditor`, so `$def['type']` is safe — but `$def['text']` contains the message string. Currently, message texts are built with `t()` calls using trusted developer strings, not user input. However, for defense-in-depth, escape it. If HTML is ever needed in messages, a follow-up could introduce a `renderMessage()` method.

---

### WP-3: Code Correctness

#### Step 3.1 — Add `declare(strict_types=1)` to three country files

**Files:**
- `src/Localization/Country/CountryAT.php`
- `src/Localization/Country/CountryNL.php`
- `src/Localization/Country/CountrySE.php`

Add `declare(strict_types=1);` after the opening `<?php` and docblock, before the `namespace` statement (matching the pattern of all other files like `CountryDE.php`).

#### Step 3.2 — Add error codes to three exceptions

**File:** `src/Localization/LocalizationException.php`

Add two new constants (next available in the 39xxx range):
```php
public const ERROR_UNKNOWN_SOURCE_ID = 39014;
public const ERROR_UNKNOWN_SOURCE_ALIAS = 39015;
```

**File:** `src/Localization/Countries/CountryException.php`

Add one new constant (next available in the 1777xx range):
```php
public const ERROR_NO_REGEX_DEFINED = 177703;
```

Then update the three throw sites:

1. **`src/Localization.php`** line ~1055 (`getSourceByID()`):
   Add third parameter `LocalizationException::ERROR_UNKNOWN_SOURCE_ID`

2. **`src/Localization.php`** line ~1081 (`getSourceByAlias()`):
   Add third parameter `LocalizationException::ERROR_UNKNOWN_SOURCE_ALIAS`

3. **`src/Localization/Countries/CountryCurrency.php`** line ~108 (`getRegex()`):
   Change `LocalizationException` to `CountryException` and add third parameter `CountryException::ERROR_NO_REGEX_DEFINED`

#### Step 3.3 — Complete `Localization::reset()`

**File:** `src/Localization.php` (line ~1351, `reset()` method)

Add resets for all static properties that hold mutable state:

```php
public static function reset() : void
{
    self::$locales = array();
    self::$selected = array();
    self::$listeners = array();
    self::$listenersCounter = 0;
    self::$translator = null;
    self::$generator = null;
    self::$configured = false;
    self::$storageFile = '';
    self::$clientFolder = '';
    self::$clientCacheKey = '';

    self::addAppLocale(self::BUILTIN_LOCALE_NAME);
    self::addContentLocale(self::BUILTIN_LOCALE_NAME);

    self::selectAppLocale(self::BUILTIN_LOCALE_NAME);
    self::selectContentLocale(self::BUILTIN_LOCALE_NAME);
}
```

**Do NOT reset:** `$initDone`, `$cacheFolder`, `$classRepository`, `$countries`, `$currencies`, `$version` — these are immutable singletons or environment-level config.

**Do NOT reset:** `$sources`, `$excludeFolders`, `$excludeFiles` — this is debatable, but resetting sources would break the self-registration done in `init()`. The doc says "Resets all locales to the built-in locale" which implies locale-only scope. **However**, the listener leak is real and should be fixed. Consider renaming the docblock to clarify scope.

**Revised approach:** Reset listeners and translator state (which depend on locale selection), but leave sources intact to match the documented contract. Update the docblock:

```php
/**
 * Resets all locales to the built-in locale and clears
 * event listeners and cached translator state. Source
 * registrations are preserved.
 */
```

---

### WP-4: Cleanup — `log()` Method

#### Step 4.1 — Remove the `log()` method

**File:** `src/Localization.php` (lines ~1113–1115)

The method is empty, has a `FIXME: TODO`, and is `public static`. A grep of the codebase shows it is not called from any production code. Remove it entirely.

If any external consumers call it, the removal will produce a clear fatal error, which is preferable to silently swallowing logs. If logging is needed in the future, it should be implemented properly (e.g., via PSR-3 `LoggerInterface` injection).

**Before removing:** Verify no call sites exist:
- Search src/, tests/, and example/ for `Localization::log(` or `->log(`.

---

### WP-5: Manifest Documentation Update

#### Step 5.1 — Update `tech-stack.md`

**File:** `docs/agents/project-manifest/tech-stack.md`

Update the Dependencies tables to match `composer.json`:

**Runtime:**
| Package | Actual version |
|---------|---------------|
| `mistralys/application-utils-core` | `>=2.5.0` |
| `mistralys/changelog-parser` | `>=1.1.0` |

**Dev:**
| Package | Actual version |
|---------|---------------|
| `phpunit/phpunit` | `>=12.0` |
| `phpstan/phpstan` | `>=2.1` |
| `phpstan/phpstan-phpunit` | `>=2.0` (NEW — add row) |
| `roave/security-advisories` | `dev-latest` (NEW — add row) |

#### Step 5.2 — Update `api-events-exceptions.md`

**File:** `docs/agents/project-manifest/api-events-exceptions.md`

Add the three new error code constants:
- `ERROR_UNKNOWN_SOURCE_ID = 39014` under `LocalizationException`
- `ERROR_UNKNOWN_SOURCE_ALIAS = 39015` under `LocalizationException`
- `ERROR_NO_REGEX_DEFINED = 177703` under `CountryException`

---

## Dependencies

- Step 1.2 (writer escaping) and Step 1.1 (reader `INI_SCANNER_RAW`) **must be deployed together** — changing one without the other will break existing translations. The unescape step added in 1.1 must match the escape step added in 1.2.
- Step 1.3 (tests) depends on 1.1 + 1.2.
- Step 2.2 depends on 2.1 (the `esc()` helper).
- Step 3.2 error codes depend on the constants being defined first.
- Step 5.2 depends on 3.2 (new error codes must exist before documenting them).
- All other steps are independent.

## Required Components

### Modified Files
- `src/Localization/Translator/LocalizationTranslator.php` — INI_SCANNER_RAW + unescape
- `src/Localization/Translator/LocalizationWriter.php` — escapeIniValue method + usage
- `src/Localization/Editor/Template/PageScaffold.php` — esc() helper + all echo sites
- `src/Localization/Country/CountryAT.php` — strict_types
- `src/Localization/Country/CountryNL.php` — strict_types
- `src/Localization/Country/CountrySE.php` — strict_types
- `src/Localization.php` — reset() expansion, log() removal, exception error codes
- `src/Localization/LocalizationException.php` — two new error code constants
- `src/Localization/Countries/CountryException.php` — one new error code constant
- `src/Localization/Countries/CountryCurrency.php` — exception type + error code
- `docs/agents/project-manifest/tech-stack.md` — version updates
- `docs/agents/project-manifest/api-events-exceptions.md` — new error codes

### New Files
- `tests/testsuites/Translator/TranslatorSpecialCharsTest.php` — round-trip INI test

## Assumptions

- The editor UI is deployed in trusted (intranet) environments, but XSS fixes are still warranted because the translations are user-supplied content.
- No external consumers call `Localization::log()`. This will be verified by grep before removal.
- Existing INI files on disk do not contain text that relies on PHP's magic constant interpretation (e.g., no translation is intentionally stored as bare `yes` expecting to get `"1"` back). This is a safe assumption given that all values are double-quoted in the writer output.
- The `reset()` method is primarily used in tests. Expanding it will not break production code where `reset()` is not typically called.

## Constraints

- PHP 8.4+ target — all code uses modern syntax.
- PHPStan level 8 must remain clean after changes.
- All 93 existing tests must continue to pass.
- `CannedCountries`, `CannedCurrencies`, `CannedLocales` must NOT be edited directly.
- Numbered sprintf placeholders (`%1$s`) must be used in any new translatable strings.

## Out of Scope

- **CSRF protection on editor save** — Requires a breaking change to the editor integration API (consumers must pass a token). Should be done in a future minor version bump with migration guidance.
- **`$_POST` input validation** — Beyond the XSS escaping, deeper validation of hash keys and translation text structure would require defining acceptance criteria for translation content, which is a separate design decision.
- **Logging implementation** — If a proper logging facility is needed, that should be a separate feature plan using PSR-3 `LoggerInterface` injection.
- **Non-Latin script support** — Noted in constraints as untested; not addressed here.
- **jQuery/Bootstrap version updates** — The editor uses jQuery 3.3.1 Slim and Bootstrap 4.3.1 from CDN, which are outdated but functional. Upgrading is a separate effort.

## Acceptance Criteria

1. `parse_ini_file()` is called with `INI_SCANNER_RAW` — translation text "Yes", "No", "None" round-trips correctly.
2. INI writer properly escapes `\`, `"`, `\n`, `\r` — multi-line translations round-trip correctly.
3. New test `TranslatorSpecialCharsTest` passes, proving round-trip fidelity for special characters.
4. All `echo` of dynamic values in `PageScaffold.php` use `htmlspecialchars()` (via `esc()` helper) — a `</textarea>` sequence in translation text does not break out of the textarea.
5. All three country files include `declare(strict_types=1)`.
6. All exception throw sites include an integer error code as third parameter.
7. `Localization::reset()` clears listeners, translator, generator, configured state, client settings.
8. `Localization::log()` is removed (or implemented, if the decision changes).
9. `tech-stack.md` matches `composer.json` dependency versions exactly.
10. `api-events-exceptions.md` lists the new error code constants.
11. PHPStan level 8 passes with zero errors.
12. All existing tests (93) continue to pass, plus new test(s) from Step 1.3.

## Testing Strategy

| Area | Test Approach |
|------|------|
| INI round-trip (WP-1) | New `TranslatorSpecialCharsTest` with cases: `"Yes"`, `"line1\nline2"`, `'quote "here"'`, `'back\\slash'`, `'semi;colon'` |
| XSS escaping (WP-2) | Manual: insert `</textarea><script>alert(1)</script>` as a translation, verify it renders escaped in the editor. Consider a future integration test. |
| strict_types (WP-3.1) | Existing country tests in `tests/testsuites/Countries/` cover these classes. |
| Error codes (WP-3.2) | Existing tests + verify PHPStan passes (constants are referenced in throw sites). |
| reset() (WP-3.3) | Existing tests use reset() via bootstrap. Add a focused test that verifies listeners are cleared after reset. |
| log() removal (WP-4) | PHPStan will flag any remaining call sites as undefined method. |
| Full suite | `composer test` — all tests green. `composer analyze` — zero errors. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Existing INI files on disk have bare (unquoted) values that were written by an older version** | All values in `renderHashes()` are always double-quoted (`"%s"`). Re-reading with `INI_SCANNER_RAW` will return the literal string including quotes only if they were part of the value. Since the writer always quotes values, this is safe. Verify with the existing translator test suite. |
| **The unescape step in the reader mismatches the escape step in the writer** | Step 1.3 adds a dedicated round-trip test. Deploy writer + reader changes atomically. |
| **Escaping editor output breaks legitimate HTML in app name or messages** | The `setAppName()` and `addMessage()` APIs accept plain strings. If HTML is needed, a `setAppNameHTML()` or `addMessageHTML()` variant can be added later. This is a safe default (escape by default, opt-in to raw). |
| **`reset()` expansion breaks tests that depend on leaked state** | Run the full test suite after the change. If tests fail, they were relying on improper test isolation, which should be fixed. |
| **Removing `log()` breaks external consumers** | Grep the codebase first. The method is empty, so any consumer calling it was getting no-op behavior anyway. A clear fatal error is better than silent no-op. |
