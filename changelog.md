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

## v1.4.2
- Editor: Replaced output buffer handling with the AppUtils one.
- Editor: Split the HTML scaffold rendering to a separate class.
- Scanner: Separated some logic in the sources to a separate scanner class.
- Scanner: Added string hash `getTextAsString()` to avoid null checks.

## v1.4.1
- Editor: Fixed the error message shown when using the search.
- Tests: Added tests to the PHPStan analysis; Added type hints.
- Example: Updated the example files to be fully valid PHP files.
- Locales: Moved locales to separate files for easier maintenance.
- Core: Updated `createLocale()` and `createCountry()` to check the created objects.
- Contexts: Partially sanitizing the context information to remove unsupported HTML tags.

## v1.4.0
- Added support for adding translation context information.
- When available, context information is shown in the translation UI.
- Added the global functions `tex()`, `ptex()` and `ptexs()`.
- Updated the entire codebase to pass level 7 PHPStan checks.
- Internal parser improvements.
- Fixed duplicate slashes being added to single quotes.
- Added `changelog.txt` and `version.txt`.

## v1.3.0
- Minimum PHP version increased to PHP7.3.
- Currency: `parseNumber()` now always returns a value (and throws an exception if it fails).
- Currency: Added `tryParseNumber()` method, which behaves like `parseNumber()` did previously.
- Javascript parsing: Now using the separate `JTokenizer` package instead of bundling it.
- Solved deprecated warnings and errors in PHP7.4+.

## v1.2.2
- Fixed double-quotes not being escaped correctly when saving the INI file.

## v1.2.1
- Saving texts now redirects to the exact page number you were on.
- Added a hint for how the search works.
- Internal code quality changes.
- Linked to AppUtils v1.2.

## v1.2.0
- Linked to AppUtils v1.1.

## v1.1.3
- Added event handling.
- Added the `Localization::onLocaleChanged()` method to track locale changes.
- Bugfix: The package's own translations were never used.

## v1.1.2
- Added the cache key for client libraries to refresh them automatically.

## v1.1.1
- PHP `pts()` function calls are now detected as intended.

## v1.1.0
- The PHP and JavaScript parsing mechanisms were upgraded.
- Fixed finding a number of false positives.
- Added support for double-quoted texts.
- Added support for multi-line concatenated texts.
- Several enhancements in the editor UI.
- Now tracking warning messages and displaying them in the editor.

## v1.0.2
- Updated dependencies.
- Now using the stable release of the AppUtils package.

## v1.0.1
- Removed repository, package moved to packagist.

## v1.0.0
- Initial release.
