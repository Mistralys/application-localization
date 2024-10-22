## v1.5.0 - Country and Currency collections (Deprecation)
- Countries: Added a country collection accessible via `Localization::createCountries()`.
- Countries: Added the `ISO_CODE` constant to all country classes.
- Countries: Fixed some separator character mixups.
- Locales: Added the `LOCALE_NAME` constant to all locale classes.
- Locales: Added the `LocaleInterface` interface.
- Locales: Added the `es_MX` locale.
- Currencies: Added the `CurrencyInterface` interface.
- Currencies: Added the `ISO_CODE` constant to all currency classes.
- Currencies: Added `getCountries()`.
- Currencies: Added `isNamePreferred()`.
- Currencies: Added `getPreferredSymbol()`.
- Currencies: Improved template-based formatting.
- Generator: Fixed clientside files being regenerated every time.

### Currency changes

Currencies can now be accessed without a country context for accessing basic
information. For number formatting, a country context is still required:
Get the currency from a country object for the added functionality.

### Deprecations
- `Localization_Currency::getCountry()`, no replacement planned.
- `Localization::createCountry()` => `Localization::createCountries()->getByID()`
- `CountryInterface::getCurrencyID()` => `getCurrencyISO()`
- `Localization_CountryInterface` => `CountryInterface`
- `Localization_Country` => `CountryInterface` or `BaseCountry`
- `Localization_Currency` => `CurrencyInterface` or `BaseCurrency`
- `BaseCurrency::isCurrencyKnown()` => `Localization::createCurrencies()->idExists()`
- `Localization_Currency_Number` => `CurrencyNumberInfo`
- `Localization_Event_LocaleChanged` => `LocaleChanged`

-----

Older change log entries can be found under `docs/changelog-history`.
