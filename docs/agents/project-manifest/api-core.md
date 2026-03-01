# Public API — Core

## `AppLocalize\Localization` (Static Facade)

The central entry point. All methods are `public static`. No instantiation.

**Namespace:** `AppLocalize`
**File:** `src/Localization.php`

### Constants

```php
public const BUILTIN_LOCALE_NAME = 'en_GB'; // via en_GB::LOCALE_NAME
public const NAMESPACE_APPLICATION = '__application';
public const NAMESPACE_CONTENT = '__content';
public const EVENT_LOCALE_CHANGED = 'LocaleChanged';
public const EVENT_CLIENT_FOLDER_CHANGED = 'ClientFolderChanged';
public const EVENT_CACHE_KEY_CHANGED = 'CacheKeyChanged';
```

### Initialization & Configuration

```php
public static function init() : void;
public static function configure(string $storageFile, string $clientLibrariesFolder = '') : void;
public static function isConfigured() : bool;
public static function reset() : void;
public static function getVersion() : string;
public static function getVersionFile() : FileInfo;
```

### Locale Management (Application Namespace)

```php
public static function addAppLocale(string $localeName) : LocaleInterface;
public static function selectAppLocale(string $localeName) : LocaleInterface;
public static function getAppLocale() : LocaleInterface; // (via getSelectedLocaleByNS)
public static function getAppLocales() : LocaleInterface[];
public static function getAppLocaleName() : string;      // (via getLocaleNameByNS)
public static function isActiveAppLocale(LocaleInterface $locale) : bool;
```

### Locale Management (Content Namespace)

```php
public static function addContentLocale(string $localeName) : LocaleInterface;
public static function selectContentLocale(string $localeName) : LocaleInterface;
public static function getContentLocale() : LocaleInterface;
public static function getContentLocales() : LocaleInterface[];
public static function getContentLocaleName() : string;
public static function isActiveContentLocale(LocaleInterface $locale) : bool;
```

### Locale Management (Generic / By Namespace)

```php
public static function addLocaleByNS(string $localeName, string $namespace) : LocaleInterface;
public static function selectLocaleByNS(string $localeName, string $namespace) : LocaleInterface;
public static function getSelectedLocaleByNS(string $namespace) : LocaleInterface;
public static function getLocalesByNS(string $namespace) : LocaleInterface[];
public static function getLocaleNamesByNS(string $namespace) : string[];
public static function localeExistsInNS(string $localeName, string $namespace) : bool;
public static function getLocaleByNameNS(string $localeName, string $namespace) : LocaleInterface;
public static function countLocalesByNS(string $namespace) : int;
```

### Locale Support Queries

```php
public static function getSupportedLocaleNames() : string[];
public static function isLocaleSupported(string $localeName) : bool;
public static function appLocaleExists(string $localeName) : bool;
public static function contentLocaleExists(string $localeName) : bool;
```

### Source Management

```php
public static function addSourceFolder(string $alias, string $label, string $group, string $storageFolder, string $path) : FolderLocalizationSource;
public static function getSources() : BaseLocalizationSource[];
public static function getSourcesGrouped() : array; // array<string, BaseLocalizationSource[]>
public static function getSourceIDs() : string[];
public static function getSourceAliases() : string[];
public static function sourceExists(string $sourceID) : bool;
public static function sourceAliasExists(string $sourceAlias) : bool;
public static function getSourceByID(string $sourceID) : BaseLocalizationSource;
public static function getSourceByAlias(string $sourceAlias) : BaseLocalizationSource;
public static function addExcludeFolder(string $folderName) : void;
public static function addExcludeFile(string $fileName) : void;
```

### Factory Methods

```php
public static function createScanner() : LocalizationScanner;
public static function createEditor() : LocalizationEditor;
public static function createGenerator() : ClientFilesGenerator;
public static function createCountries() : CountryCollection;
public static function createCurrencies() : CurrencyCollection;
public static function getTranslator(?LocaleInterface $locale = null) : LocalizationTranslator;
public static function getClassRepository() : ClassRepositoryManager;
```

### Client Libraries

```php
public static function setClientLibrariesCacheKey(string $key) : void;
public static function getClientLibrariesCacheKey() : string;
public static function setClientLibrariesFolder(string $folder) : void;
public static function getClientLibrariesFolder() : string;
public static function getClientFolder() : string;
public static function writeClientFiles(bool $force = false) : void;

/**
 * Returns diagnostics for the client file cache key comparison.
 * Keys: storedKey (?string), systemKey (string), match (bool),
 *       folder (string), cacheKeyFile (?string)
 * @return array{storedKey:string|null,systemKey:string,match:bool,folder:string,cacheKeyFile:string|null}
 */
public static function getClientFilesDiagnostics() : array;
```

### Events

```php
public static function addEventListener(string $eventName, callable $callback, array $args = []) : int;
public static function onLocaleChanged(callable $callback, array $args = []) : int;
public static function onClientFolderChanged(callable $callback, array $args = []) : int;
public static function onCacheKeyChanged(callable $callback, array $args = []) : int;
```

### Currency Shortcuts

```php
public static function getAppCurrency() : CurrencyInterface;
public static function getContentCurrency() : CurrencyInterface;
public static function getCurrencyNS(string $namespace) : CurrencyInterface;
```

### Form Integration

```php
public static function injectContentLocalesSelector(string $elementName, HTML_QuickForm2_Container $container, string $label = '') : HTML_QuickForm2_Element_Select;
public static function injectAppLocalesSelector(string $elementName, HTML_QuickForm2_Container $container, string $label = '') : HTML_QuickForm2_Element_Select;
public static function injectLocalesSelectorNS(string $elementName, string $namespace, HTML_QuickForm2_Container $container, string $label = '') : HTML_QuickForm2_Element_Select;
```

### Cache

```php
public static function getCacheFolder() : string;
public static function clearClassCache() : void;
```

---

## Global Translation Functions

**Namespace:** `AppLocalize`
**File:** `src/functions.php`

These are the primary API for translating strings in application code.

```php
function t(string $text, mixed ...$args) : string;
function pt(string $text, mixed ...$args) : void;     // echo variant
function pts(string $text, mixed ...$args) : void;    // echo + trailing space
function tex(string $text, string $context, mixed ...$args) : string;   // with context
function ptex(string $text, string $context, mixed ...$args) : void;    // echo with context
function ptexs(string $text, string $context, mixed ...$args) : void;   // echo + space with context
```

### How They Work

1. All functions delegate to `Localization::getTranslator()->translate()`
2. The `$text` parameter is the English source string (also used as fallback)
3. The `$args` use `sprintf`-style numbered placeholders (`%1$s`, `%2$d`, etc.)
4. The `$context` parameter in `tex`/`ptex`/`ptexs` is metadata for translators only (stripped at runtime)

---

## Client-Side Translation API

**File:** `src/js/translator.js`

### `t(text, ...placeholders) → string`

Client-side counterpart of the PHP `t()` function.

### `AppLocalize_Translator` Object

```javascript
AppLocalize_Translator.a(hash, text)          // Register a translated string
AppLocalize_Translator.Translate(text, ...)   // Look up and format a translation
AppLocalize_Translator.sprintf(format, ...)   // sprintf implementation
```

The auto-generated `locale-xx.js` files chain `.a()` calls to populate the string registry.
