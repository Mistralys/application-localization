# Public API — Events & Exceptions

## Events

**Namespace:** `AppLocalize\Localization\Event`

The localization system fires events when configuration changes occur at runtime.

### `LocalizationEventInterface`

```php
public function getArgument(int $index); // returns mixed|null
```

### `BaseLocalizationEvent` (abstract)

**Implements:** `LocalizationEventInterface`

```php
public function __construct(array $args);
public function getArgument(int $index);
```

### `LocaleChanged`

**Extends:** `BaseLocalizationEvent`

Fired when a locale is selected in any namespace via `selectLocaleByNS()`, `selectAppLocale()`, or `selectContentLocale()`.

```php
public const ERROR_NO_CURRENT_LOCALE_SPECIFIED = 91401;

public function getPrevious() : ?BaseLocale;
public function getCurrent() : BaseLocale;
public function getNamespace() : string;
public function isAppLocale() : bool;
public function isContentLocale() : bool;
```

### `CacheKeyChanged`

**Extends:** `BaseLocalizationEvent`

Fired when `Localization::setClientLibrariesCacheKey()` is called with a new value.
No additional public members.

### `ClientFolderChanged`

**Extends:** `BaseLocalizationEvent`

Fired when `Localization::setClientLibrariesFolder()` is called with a new path.
No additional public members.

### Registering Listeners

Listeners are registered via the `Localization` facade:

```php
Localization::onLocaleChanged(callable $callback, array $args = []) : int;
Localization::onClientFolderChanged(callable $callback, array $args = []) : int;
Localization::onCacheKeyChanged(callable $callback, array $args = []) : int;
Localization::addEventListener(string $eventName, callable $callback, array $args = []) : int;
```

All listener registrations return a listener ID (integer).

---

## Exceptions

### `LocalizationException`

**Namespace:** `AppLocalize\Localization`
**Extends:** `AppUtils\BaseException`

The base exception for all localization errors.

```php
public const ERROR_NO_STORAGE_FILE_SET = 39003;
public const ERROR_CONFIGURE_NOT_CALLED = 39004;
public const ERROR_NO_SOURCES_ADDED = 39005;
public const ERROR_NO_LOCALE_SELECTED_IN_NS = 39006;
public const ERROR_NO_LOCALES_IN_NAMESPACE = 39007;
public const ERROR_UNKNOWN_NAMESPACE = 39008;
public const ERROR_UNKNOWN_LOCALE_IN_NS = 39009;
public const ERROR_UNKNOWN_EVENT_NAME = 39010;
public const ERROR_LOCALE_NOT_FOUND = 39011;
public const ERROR_COUNTRY_NOT_FOUND = 39012;
public const ERROR_INCORRECTLY_TRANSLATED_STRING = 39013;
public const ERROR_UNKNOWN_SOURCE_ID = 39014;
public const ERROR_UNKNOWN_SOURCE_ALIAS = 39015;
```

### Exception Hierarchy

```
AppUtils\BaseException
└── LocalizationException
    ├── CountryException
    │   ├── ERROR_CANNOT_PARSE_CURRENCY_NUMBER (177701)
    │   ├── ERROR_INVALID_BASKET_COUNTRY_SELECTION (177702)
    │   └── ERROR_NO_REGEX_DEFINED (177703)
    └── EditorException
```

### Error Code Ranges

| Range | Subsystem |
|---|---|
| 39xxx | Core Localization |
| 3331xx | Translator |
| 392xx | Scanner / StringCollection |
| 393xx | ClientFilesGenerator |
| 394xx | TranslationExporter |
| 395xx | TranslationImporter |
| 400xx | Editor |
| 405xx | Parser (BaseLanguage) |
| 406xx | Parser (LocalizationParser) |
| 914xx | Events (LocaleChanged) |
| 1777xx | Countries |
