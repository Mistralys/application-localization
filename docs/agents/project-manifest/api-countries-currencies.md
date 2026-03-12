# Public API — Countries & Currencies

## Countries

### `CountryInterface`

**Namespace:** `AppLocalize\Localization\Countries`
**Extends:** `AppUtils\Interfaces\StringPrimaryRecordInterface`

```php
public function getNumberThousandsSeparator() : string;
public function getNumberDecimalsSeparator() : string;
public function getCurrencyISO() : string;
public function getCurrency() : CountryCurrencyInterface;
public function getLabel() : string;
public function getLabelInvariant() : string;
public function getCode() : string;
public function getMainLocaleCode() : string;
public function getMainLocale() : LocaleInterface;
public function getAliases() : string[];
public function getTimeZone() : TimeZoneInterface;
public function getTimeZoneID() : string;
```

### `BaseCountry` (abstract)

**Implements:** `CountryInterface`

Each concrete country class (e.g., `CountryDE`, `CountryFR`) extends this and defines:
- ISO code, label, invariant label
- Currency ISO, number formatting separators
- Main locale code, timezone ID, aliases

```php
public function getAliases() : array;
public function getCurrency() : CountryCurrencyInterface;
public function getID() : string;
public function getCurrencyID() : string;
public function __toString();
public function getMainLocale() : LocaleInterface;
public function getTimeZone() : TimeZoneInterface;
```

### `CountryCollection`

**Extends:** `AppUtils\Collections\BaseClassLoaderCollection`

Singleton. Dynamically loads all country classes from `src/Localization/Country/`.

```php
public static function getInstance() : CountryCollection;
public function getByISO(string $iso) : CountryInterface;
public function getByID(string $id) : StringPrimaryRecordInterface;
public function idExists(string $id) : bool;
public function filterCode(string $code) : string;
public function isoExists(string $iso) : bool;
public function getDefaultID() : string;
public function choose() : CannedCountries;
public function getAliases() : array; // array<string, string>
public function getInstanceOfClassName() : ?string;
public function isRecursive() : bool;
public function getClassesFolder() : FolderInfo;
```

### `CannedCountries`

Type-safe accessor with one method per supported country.

```php
public function at() : CountryAT;
public function be() : CountryBE;
public function ca() : CountryCA;
public function ch() : CountryCH;
public function de() : CountryDE;
public function es() : CountryES;
public function fi() : CountryFI;
public function fr() : CountryFR;
public function gb() : CountryGB;
public function ie() : CountryIE;
public function it() : CountryIT;
public function mx() : CountryMX;
public function nl() : CountryNL;
public function pl() : CountryPL;
public function ro() : CountryRO;
public function se() : CountrySE;
public function sg() : CountrySG;
public function uk() : CountryGB;   // alias for gb()
public function us() : CountryUS;
public function zz() : CountryZZ;   // country-independent
```

### `CountryBasket`

**Extends:** `AppUtils\Baskets\GenericStringPrimaryBasket`

Freeform collection of selected countries.

```php
public function getAllowedItemClasses() : array;
// Inherited: getAll(), getByID(), static create(), add(), remove(), has(), count(), etc.
```

### Supported Countries

`AT` Austria, `BE` Belgium, `CA` Canada, `CH` Switzerland, `DE` Germany, `ES` Spain, `FI` Finland, `FR` France, `GB` Great Britain (alias `UK`), `IE` Ireland, `IT` Italy, `MX` Mexico, `NL` Netherlands, `PL` Poland, `RO` Romania, `SE` Sweden, `SG` Singapore, `US` United States, `ZZ` Country-independent.

---

## Currencies

### `CurrencyInterface`

**Namespace:** `AppLocalize\Localization\Currencies`
**Extends:** `StringableInterface`, `StringPrimaryRecordInterface`

```php
public function getID() : string;
public function getCountries() : CountryInterface[];
public function getSingular() : string;
public function getSingularInvariant() : string;
public function getPlural() : string;
public function getPluralInvariant() : string;
public function getSymbol() : string;
public function getPreferredSymbol() : string;
public function getISO() : string;
public function getStructuralTemplate(?CountryInterface $country = null) : string;
public function isSymbolOnFront() : bool;
public function isNamePreferred() : bool;
```

### `BaseCurrency` (abstract)

**Implements:** `CurrencyInterface`

```php
public function __construct();
public function getID() : string;
public function getCountries() : CountryInterface[];
public function __toString();
public function getPreferredSymbol() : string;
```

### `CurrencyCollection`

**Extends:** `BaseClassLoaderCollection`

Singleton. Dynamically loads all currency classes from `src/Localization/Currency/`.

```php
public static function getInstance() : CurrencyCollection;
public function getDefaultID() : string;
public function getByISO(string $iso) : CurrencyInterface;
public function isoExists(string $iso) : bool;
public function choose() : CannedCurrencies;
public function getInstanceOfClassName() : ?string;
public function isRecursive() : bool;
public function getClassesFolder() : FolderInfo;
```

### `CannedCurrencies`

```php
public function cad() : CurrencyCAD;
public function chf() : CurrencyCHF;
public function eur() : CurrencyEUR;
public function gbp() : CurrencyGBP;
public function mxn() : CurrencyMXN;
public function pln() : CurrencyPLN;
public function ron() : CurrencyRON;
public function sek() : CurrencySEK;
public function sgd() : CurrencySGD;
public function usd() : CurrencyUSD;
```

### Supported Currencies

`CAD` Canadian Dollars ($), `CHF` Swiss Francs (F), `EUR` Euros (€), `GBP` Pounds (£), `MXN` Mexican Peso ($), `PLN` Złotys (zł), `RON` Lei, `SEK` Kronor (kr), `SGD` Singapore Dollars (S$), `USD` Dollars ($).

---

## Country-Specific Currency Formatting

### `CountryCurrencyInterface`

**Extends:** `CurrencyInterface`

Adds country-aware number parsing and formatting methods.

```php
public function getCountry() : CountryInterface;
public function isNumberValid($number) : bool;
public function getFormatHint() : ?string;
public function getExamples(int $decimalPositions = 0) : string[];
public function tryParseNumber($number) : ?CurrencyNumberInfo;
public function parseNumber($number) : CurrencyNumberInfo;
public function normalizeNumber($number) : string;
public function formatNumber($number, int $decimalPositions = 2) : string;
public function getThousandsSeparator() : string;
public function getDecimalsSeparator() : string;
public function makeReadable($number, int $decimalPositions = 2, bool $addSymbol = true) : string;
```

### `CountryCurrency`

**Implements:** `CountryCurrencyInterface`

Wraps a `CurrencyInterface` with a `CountryInterface` to provide localized number parsing and formatting (e.g., `1.445,42 €` for DE vs. `€1,445.42` for US).

```php
public function __construct(CurrencyInterface $currency, CountryInterface $country);
// All CountryCurrencyInterface methods are implemented here.
```

### `CurrencyNumberInfo`

Represents a parsed currency number. The `$decimals` field is stored as a
`string` to preserve leading zeros (e.g., `'05'` stays `'05'`, not `5`).

> **Breaking change (v1.5+):** `getDecimals()` now returns `string` instead
> of `int`. Callers performing arithmetic on the return value must cast to int.
> The constructor still accepts `int` (backward-compatible), but stores it as `string`.

```php
// Constructor — accepts int or string for $decimals (stored as string internally)
public function __construct(int $number, int|string $decimals = 0);
public function isNegative() : bool;
public function getFloat() : float;
public function getNumber() : int;
public function getDecimals() : string;   // was int — now string to preserve leading zeros
public function countDecimals() : int;    // returns 0 when decimals === '0'
public function getString() : string;
```

### `CountryException`

**Extends:** `LocalizationException`

```php
public const ERROR_CANNOT_PARSE_CURRENCY_NUMBER = 177701;
public const ERROR_INVALID_BASKET_COUNTRY_SELECTION = 177702;
```
