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
public function detectVariables(string $string) : string[];
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
