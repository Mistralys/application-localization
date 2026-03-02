# Public API — Translator & Editor

## Translator Subsystem

**Namespace:** `AppLocalize\Localization\Translator`

### `LocalizationTranslator`

The runtime translation engine. Loads INI files per locale, looks up text by MD5 hash,
and applies sprintf formatting.

```php
public const ERROR_NO_STRINGS_AVAILABLE_FOR_LOCALE = 333101;
public const ERROR_CANNOT_PARSE_LOCALE_FILE = 333103;

public function addSource(BaseLocalizationSource $source) : void;
public function addSources(array $sources) : void;
public function setTargetLocale(LocaleInterface $locale) : void;
public function save(BaseLocalizationSource $source, StringCollection $collection) : void;
public function getStrings(LocaleInterface $locale) : string[];
public function hasStrings(LocaleInterface $locale) : bool;
public function setTranslation(string $hash, string $text) : void;
public function clearTranslation(string $hash) : void;
public function translate(string $text, array $args) : string;
public function hashExists(string $hash) : bool;
public function translationExists(string $text) : bool;
public function getTargetLocale() : LocaleInterface;
public function getHashTranslation(string $hash, ?LocaleInterface $locale = null) : ?string;
public function getClientStrings(LocaleInterface $locale) : array; // array<string, string>
```

### `LocalizationWriter`

Writes translated strings to INI files on disk.

```php
public function __construct(LocaleInterface $locale, string $fileType, string $filePath);
public function makeEditable() : LocalizationWriter;
public function addHash(string $hash, string $text) : LocalizationWriter;
public function writeFile() : void;
```

### `ClientFilesGenerator`

Generates the JavaScript client library files (`locale-xx.js`, `translator.js`, `md5.min.js`)
into the configured client folder.

```php
public const ERROR_JS_FOLDER_NOT_FOUND = 39302;
public const ERROR_TARGET_FOLDER_NOT_WRITABLE = 39303;

public function __construct();
public static function setLoggingEnabled(bool $enabled) : void;
public function getCacheKey() : ?string;
public function getWriteSkipReason() : ?string;   // null = would write; descriptive string = would skip
public function writeFiles(bool $force = false) : void;
public function getFilesList() : string[];
public static function getSystemKey() : string;
public function areFilesWritten() : bool;
```

> **Dead-code resolved:** The instance properties `$targetFolder` and
> `$cacheKeyFile` have been removed. They were declared and reset in
> `handleFolderChanged()` but never read by `getTargetFolder()` or
> `getCacheKeyFile()`, which always construct fresh instances. After the fix,
> `handleFolderChanged()` also resets the in-memory `$cacheKey` and static
> `$systemKey`, making it consistent with `handleCacheKeyChanged()`.

---

## Editor Subsystem

**Namespace:** `AppLocalize\Localization\Editor`

A browser-based translation UI that can be integrated into an existing web application.

### `LocalizationEditor`

**Implements:** `AppUtils\Interfaces\OptionableInterface`

The main editor class. Renders an HTML page with source selection, locale tabs,
search/filter controls, pagination, and inline translation editing.

```php
public const MESSAGE_INFO = 'info';
public const MESSAGE_ERROR = 'danger';
public const MESSAGE_WARNING = 'warning';
public const MESSAGE_SUCCESS = 'success';
public const ERROR_NO_SOURCES_AVAILABLE = 40001;
public const ERROR_LOCAL_PATH_NOT_FOUND = 40002;
public const ERROR_STRING_HASH_WITHOUT_TEXT = 40003;
public const VARIABLE_STRINGS = 'strings';
public const VARIABLE_SAVE = 'save';
public const VARIABLE_SCAN = 'scan';
public const VARIABLE_WARNINGS = 'warnings';

public function __construct();
public function getRequest() : Request;
public function addRequestParam(string $name, string $value) : LocalizationEditor;
public function getActiveSource() : BaseLocalizationSource;
public function getVarName(string $name) : string;
public function getAppLocales() : LocaleInterface[];
public function getSources() : BaseLocalizationSource[];
public function getBackURL() : string;
public function getBackButtonLabel() : string;
public function getSaveVariableName() : string;
public function getStringsVariableName() : string;
public function getScanner() : LocalizationScanner;
public function render() : string;
public function display() : void;
public function getScannerWarnings() : CollectionWarning[];
public function hasAppLocales() : bool;
public function isShowWarningsEnabled() : bool;
public function getFilters() : EditorFilters;
public function getFilteredStrings() : StringHash[];
public function getRequestParams() : array;
public function getAmountPerPage() : int;
public function getPageNumber() : int;
public function getActiveLocale() : LocaleInterface;
public function getPaginationURL(int $page, array $params = []) : string;
/**
 * Detects all sprintf format placeholders in $string using FormatParser.
 * Returns an array of matched format-string tokens (e.g. ['%1$s', '%2$d']).
 * Used by PageScaffold::renderText() to highlight placeholders in the UI.
 */
public function detectVariables(string $string) : string[];
/**
 * Returns the sorted list of unique numbered argument indices found in $text
 * (e.g. [1, 2] for "Hello %1$s, you have %2$d messages").
 * Returns [] if no numbered placeholders exist or on FormatParser exception.
 */
public function getPlaceholderNumbers(string $text) : int[];
/**
 * Returns true if $text contains any sprintf placeholder without a positional
 * argument number (e.g. %s, %d). Returns false for fully numbered strings or
 * strings with no placeholders. Safe: returns false on FormatParser exception.
 */
public function hasUnnumberedPlaceholders(string $text) : bool;
public function getSourceURL(BaseLocalizationSource $source, array $params = []) : string;
public function getLocaleURL(LocaleInterface $locale, array $params = []) : string;
public function getScanURL() : string;
public function getWarningsURL() : string;
public function getURL(array $params = []) : string;
public function redirect(string $url) : void; // never returns
public function getDefaultOptions() : array;
public function setAppName(string $name) : LocalizationEditor;
public function getAppName() : string;
public function selectDefaultSource(string $sourceID) : LocalizationEditor;
public function setBackURL(string $url, string $label) : LocalizationEditor;
public function getInstallPath() : string;
```

### `EditorFilters`

Handles search and filter criteria for the editor's string list.

```php
public function __construct(LocalizationEditor $editor);
public function isStringMatch(StringHash $string) : bool;
public function renderForm() : string;
```

### `EditorException`

**Extends:** `LocalizationException`

No additional public members.

### Editor Assets

| Asset | Location | Purpose |
|---|---|---|
| `editor.css` | `src/css/editor.css` | Editor UI stylesheet |
| `editor.js` | `src/js/editor.js` | Client-side toggle/confirm for inline editing (jQuery) |
| `PageScaffold.php` | `src/Localization/Editor/Template/PageScaffold.php` | HTML page scaffold template |

---

## Translation Tools

**Namespace:** `AppLocalize\Tools`

Command-line / programmatic utilities for exporting and importing translated strings in
a portable JSON format. Both classes share the same config resolution strategy.

### Config Resolution Order

Both `TranslationExporter` and `TranslationImporter` resolve the bootstrap config file in
this order when invoked via their Composer entry point (`run()`):

1. PHP constant `LOCALIZATION_TOOLS_CONFIG` (absolute file path string).
2. Environment variable `LOCALIZATION_TOOLS_CONFIG` (absolute file path string).
3. Default fallback: `localization-tools-config.php` at the Composer project root.

If none is found, an error message is printed to stdout and the process exits with code `1`.

---

### `TranslationExporter`

**Location:** `src/Tools/TranslationExporter.php`

Generates per-locale, per-source JSON export files from `storage.json` scan results and
the existing INI translation files.

Output file naming convention (written to the source's storage folder):
```
{storageFolder}/{locale}-{source-alias}-translations.json
```

```php
/**
 * Composer entry point: `composer export-translations`
 * Resolves the config file, then exports all locales × sources.
 */
public static function run(): void;

/**
 * Programmatic / test factory.
 * Requires Localization to have been configured before calling.
 */
public static function create(): self;

/**
 * Run the export programmatically (without loading config).
 * Localization must have been configured before calling.
 */
public function export(): void;
```

#### Export JSON Format (`format_version: 1`)

Top-level keys:

| Key | Type | Description |
|---|---|---|
| `format_version` | `int` | Always `1` |
| `locale` | `string` | Locale name (e.g. `de_DE`) |
| `locale_label` | `string` | Human-readable locale label |
| `source_alias` | `string` | Source alias (used in file name) |
| `source_label` | `string` | Human-readable source label |
| `exported_at` | `string` | ISO 8601 timestamp |
| `strings` | `array` | Array of string entry objects |

Each object in `strings`:

| Key | Type | Description |
|---|---|---|
| `hash` | `string` | MD5 hash of the source text |
| `source_text` | `string` | Original translatable string |
| `context` | `string` | Optional translator hint / explanation |
| `files` | `string[]` | `"relative/path.php:line"` occurrences |
| `translation` | `string` | Translated text, or `""` if not yet translated |

#### Key Behaviours

- **Native locale excluded:** The built-in locale (`en_GB`, `Localization::BUILTIN_LOCALE_NAME`) is never exported.
- **Empty translation:** A string with no INI entry produces `"translation": ""`.
- **Relative file paths:** All occurrences in `files` are relative to the Composer project root.
- **Source deduplication:** Sources registered more than once (same alias + storage folder) are exported only once.

---

### `TranslationImporter`

**Location:** `src/Tools/TranslationImporter.php`

Reads per-locale, per-source JSON export files (produced by `TranslationExporter`) and
writes the translated strings into the corresponding INI translation files via
`LocalizationWriter`.

```php
/**
 * Composer entry point: `composer import-translations`
 * Resolves the config file, then imports all locales × sources.
 */
public static function run(): void;

/**
 * Programmatic / test factory.
 * Requires Localization to have been configured before calling.
 */
public static function create(): self;

/**
 * Run the import programmatically (without loading config).
 * Localization must have been configured before calling.
 */
public function import(): void;
```

#### Import Logic

1. Load `storage.json` via `Localization::createScanner()`.
2. For each non-native app locale × registered source, locate the JSON file at
   `{storageFolder}/{locale}-{source-alias}-translations.json`.
3. Validate `format_version === 1`; skip with a warning if mismatched or missing.
4. Build a `hash → translation` map:
   - Entries with an empty or missing `translation` field are silently skipped.
   - Entries whose hash is absent from the current `StringCollection` are logged as
     stale and skipped.
5. Write INI files:
   - **Server INI** (`{locale}-{source-alias}-server.ini`) — all hashes that have a
     non-empty translation.
   - **Client INI** (`{locale}-{source-alias}-client.ini`) — subset: only hashes where
     `StringHash::hasLanguageType('JavaScript')` is true.

#### Key Behaviours

- **Missing file warning:** If no JSON file exists for a locale/source, a warning is printed and processing continues without throwing.
- **Stale hash warning:** A hash in the JSON that is no longer in the current scan emits a warning and is skipped.
- **Source deduplication:** Same deduplication logic as `TranslationExporter` — the same physical source is imported only once.
