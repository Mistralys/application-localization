# Public API — Locales & TimeZones

## Locales

### `LocaleInterface`

**Namespace:** `AppLocalize\Localization\Locales`
**Extends:** `AppUtils\Interfaces\StringPrimaryRecordInterface`

```php
public function getLanguageCode() : string;
public static function isLocaleKnown(string $localeName) : bool;
public function getName() : string;
public function getCountryCode() : string;
public function isNative() : bool;
public function getLabel() : string;
public function getLabelInvariant() : string;
public function getCountry() : CountryInterface;
public function getCurrency() : CurrencyInterface;
public function getAliases() : string[];
```

### `BaseLocale` (abstract)

**Implements:** `LocaleInterface`

Each concrete locale class (e.g., `de_DE`, `fr_FR`) extends this and defines:
- Locale name (e.g., `de_DE`), label, invariant label
- Country code, language code
- Whether it is the native/built-in locale

```php
public function __construct();
public function getID() : string;
public function getLanguageCode() : string;
public static function isLocaleKnown(string $localeName) : bool;
public function getCountryCode() : string;
public function isNative() : bool;
public function getCountry() : CountryInterface;
public function getCurrency() : CurrencyInterface;
public function getAliases() : array;
```

### `LocalesCollection`

**Extends:** `BaseClassLoaderCollection`

Singleton. Dynamically loads all locale classes from `src/Localization/Locale/`.

```php
public static function getInstance() : self;
public function getInstanceOfClassName() : ?string;
public function isRecursive() : bool;
public function getClassesFolder() : FolderInfo;
public function getDefaultID() : string;
public function idExists(string $id) : bool;
public function getByName(string $name) : LocaleInterface;
public function nameExists(string $name) : bool;
public function getByID(string $id) : StringPrimaryRecordInterface;
public function filterName(string $name) : string;  // handles aliases like en_UK → en_GB
public function choose() : CannedLocales;
```

### `CannedLocales`

Type-safe accessor with one method per supported locale.

```php
public function de_AT() : de_AT;
public function de_CH() : de_CH;
public function de_DE() : de_DE;
public function en_CA() : en_CA;
public function en_GB() : en_GB;
public function en_IE() : en_IE;
public function en_SG() : en_SG;
public function en_US() : en_US;
public function es_ES() : es_ES;
public function es_MX() : es_MX;
public function fi_FI() : fi_FI;
public function fr_BE() : fr_BE;
public function fr_FR() : fr_FR;
public function it_IT() : it_IT;
public function nl_NL() : nl_NL;
public function pl_PL() : pl_PL;
public function ro_RO() : ro_RO;
public function sv_SE() : sv_SE;
```

### `LocaleBasket`

**Extends:** `AppUtils\Baskets\GenericStringPrimaryBasket`

Freeform collection of selected locales.

```php
public function getAllowedItemClasses() : array;
// Inherited: getAll(), getByID(), static create(), add(), remove(), has(), count(), etc.
```

### Supported Locales

`de_AT`, `de_CH`, `de_DE`, `en_CA`, `en_GB`, `en_IE`, `en_SG`, `en_US`, `es_ES`, `es_MX`, `fi_FI`, `fr_BE`, `fr_FR`, `it_IT`, `nl_NL`, `pl_PL`, `ro_RO`, `sv_SE`.

---

## TimeZones

### `TimeZoneInterface`

**Namespace:** `AppLocalize\Localization\TimeZones`
**Extends:** `StringPrimaryRecordInterface`

```php
public function getLabel() : string;
public function getLabelInvariant() : string;
public function getZoneLabel() : string;            // e.g., "Europe"
public function getZoneLabelInvariant() : string;
public function getLocationLabel() : string;        // e.g., "Berlin"
public function getLocationLabelInvariant() : string;
```

### `CountryTimeZoneInterface`

**Extends:** `TimeZoneInterface`

For timezones tied to a specific country/locale.

```php
public function getLocaleCode() : string;
public function getLocale() : LocaleInterface;
public function getCountryCode() : string;
public function getCountry() : CountryInterface;
```

### `GlobalTimeZoneInterface`

**Extends:** `TimeZoneInterface`

For meta-timezones (CET, CST, UTC) spanning multiple countries.

```php
public function getCountries() : CountryBasket;
public function getLocales() : LocaleBasket;
```

### Class Hierarchy

```
TimeZoneInterface
├── BaseTimeZone (abstract)
│   ├── BaseCountryTimeZone (abstract) implements CountryTimeZoneInterface
│   │   ├── BaseEuropeTimeZone (abstract)   — zone = "Europe" / "Europa"
│   │   ├── BaseAmericaTimeZone (abstract)  — zone = "America"
│   │   │   └── BaseUSTimeZone (abstract)   — country = US
│   │   └── BaseAsiaTimeZone (abstract)     — zone = "Asia" / "Asien"
│   └── BaseGlobalTimeZone (abstract) implements GlobalTimeZoneInterface
│       — zone = "Global"
```

### `TimeZoneCollection`

**Extends:** `BaseClassLoaderCollection`

Singleton. Recursively loads all timezone classes from `src/Localization/TimeZone/`.

```php
public static function getInstance() : TimeZoneCollection;
public function getDefaultID() : string;
public function findByLocale(string|LocaleInterface $locale) : ?CountryTimeZoneInterface;
public function findByCountry(string|CountryInterface $country) : ?CountryTimeZoneInterface;
public function getCountryTimeZones() : CountryTimeZoneBasket;
public function getGlobalTimeZones() : GlobalTimeZoneBasket;
// Inherited: getAll(), getByID(), getDefault()
```

### Timezone Baskets

| Class | Contents |
|---|---|
| `CountryTimeZoneBasket` | All country-specific timezones |
| `GlobalTimeZoneBasket` | All global/meta timezones |
| `TimeZoneBasket` | All timezones (base basket) |

### Supported TimeZones

**Country timezones:** Europe/Amsterdam, Europe/Berlin, Europe/Brussels, Europe/Bucharest, Europe/Dublin, Europe/Helsinki, Europe/London, Europe/Madrid, Europe/Paris, Europe/Rome, Europe/Stockholm, Europe/Vienna, Europe/Warsaw, Europe/Zurich, America/Mexico_City, America/Vancouver, Asia/Singapore, US/Eastern.

**Global timezones:** CET, CST, UTC.
