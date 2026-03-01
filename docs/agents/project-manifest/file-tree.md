# File Tree

```
application-localization/
├── cache/                          # Class repository cache (auto-generated)
│   └── _readme.md
├── docs/
│   ├── overview.md                 # Supported countries, locales, currencies, timezones
│   ├── agents/
│   │   └── project-manifest/       # This manifest
│   └── changelog-history/          # Historical changelogs (v1.0–v1.5)
├── example/
│   ├── index.php                   # Standalone editor UI demo
│   ├── client-libraries/           # Generated JS client files (runtime output)
│   ├── localization/               # Translation INI files for the example
│   └── sources/                    # Example PHP/JS source files to scan
│       ├── js/
│       └── php/
├── localization/                   # INI translation files for this package's own UI strings
│   ├── de_DE-application-localization-client.ini
│   ├── de_DE-application-localization-server.ini
│   ├── fr_FR-application-localization-client.ini
│   ├── fr_FR-application-localization-server.ini
│   ├── index.php                   # Directory listing guard
│   └── storage.json                # Scanner results cache
├── src/
│   ├── Localization.php            # Main static facade class
│   ├── functions.php               # Global translation functions: t(), pt(), pts(), tex(), ptex(), ptexs()
│   ├── css/
│   │   └── editor.css              # Editor UI stylesheet
│   ├── js/
│   │   ├── editor.js               # Editor UI client-side interaction
│   │   ├── md5.min.js              # MD5 hashing for string keys
│   │   └── translator.js           # Client-side translation engine
│   ├── Localization/
│   │   ├── LocalizationException.php
│   │   ├── Countries/              # Country abstractions & collection
│   │   │   ├── BaseCountry.php
│   │   │   ├── CannedCountries.php
│   │   │   ├── CountryBasket.php
│   │   │   ├── CountryCollection.php
│   │   │   ├── CountryCurrency.php     # Country-specific currency formatting
│   │   │   ├── CountryException.php
│   │   │   └── CountryInterface.php
│   │   ├── Country/                # One class per country (19 countries)
│   │   │   ├── CountryAT.php .. CountryZZ.php
│   │   ├── Currencies/             # Currency abstractions & collection
│   │   │   ├── BaseCurrency.php
│   │   │   ├── CannedCurrencies.php
│   │   │   ├── CountryCurrencyInterface.php
│   │   │   ├── CurrencyCollection.php
│   │   │   ├── CurrencyInterface.php
│   │   │   └── CurrencyNumberInfo.php
│   │   ├── Currency/               # One class per currency (10 currencies)
│   │   │   ├── CurrencyCAD.php .. CurrencyUSD.php
│   │   ├── Editor/                 # Browser-based translation editor
│   │   │   ├── EditorException.php
│   │   │   ├── EditorFilters.php
│   │   │   ├── LocalizationEditor.php
│   │   │   └── Template/
│   │   │       └── PageScaffold.php
│   │   ├── Event/                  # Event classes
│   │   │   ├── BaseLocalizationEvent.php
│   │   │   ├── CacheKeyChanged.php
│   │   │   ├── ClientFolderChanged.php
│   │   │   ├── LocaleChanged.php
│   │   │   └── LocalizationEventInterface.php
│   │   ├── Locale/                 # One class per locale (18 locales)
│   │   │   ├── de_AT.php .. sv_SE.php
│   │   ├── Locales/                # Locale abstractions & collection
│   │   │   ├── BaseLocale.php
│   │   │   ├── CannedLocales.php
│   │   │   ├── LocaleBasket.php
│   │   │   ├── LocaleInterface.php
│   │   │   └── LocalesCollection.php
│   │   ├── Parser/                 # Source code parsing for translatable strings
│   │   │   ├── BaseLanguage.php
│   │   │   ├── BaseParsedToken.php
│   │   │   ├── LocalizationParser.php
│   │   │   ├── ParserWarning.php
│   │   │   ├── Text.php
│   │   │   ├── Language/
│   │   │   │   ├── JavaScriptLanguage.php
│   │   │   │   └── PHPLanguage.php
│   │   │   └── Token/
│   │   │       ├── JavaScriptToken.php
│   │   │       └── PHPToken.php
│   │   ├── Scanner/                # File scanning & string collection
│   │   │   ├── CollectionWarning.php
│   │   │   ├── LocalizationScanner.php
│   │   │   ├── StringCollection.php
│   │   │   ├── StringHash.php
│   │   │   └── StringInfo.php
│   │   ├── Source/                 # Source folder registration
│   │   │   ├── BaseLocalizationSource.php
│   │   │   ├── FolderLocalizationSource.php
│   │   │   └── SourceScanner.php
│   │   ├── TimeZone/               # One class per timezone (21 timezones)
│   │   │   ├── America/            # AmericaMexicoCityTimeZone, AmericaVancouverTimeZone
│   │   │   ├── Asia/               # AsiaSingaporeTimeZone
│   │   │   ├── Europe/             # 14 European city timezones
│   │   │   ├── Globals/            # GlobalCETTimeZone, GlobalCSTTimeZone, GlobalUTCTimeZone
│   │   │   └── US/                 # USEasternTimeZone
│   │   ├── TimeZones/              # TimeZone abstractions & collection
│   │   │   ├── BaseAmericaTimeZone.php
│   │   │   ├── BaseAsiaTimeZone.php
│   │   │   ├── BaseCountryTimeZone.php
│   │   │   ├── BaseEuropeTimeZone.php
│   │   │   ├── BaseGlobalTimeZone.php
│   │   │   ├── BaseTimeZone.php
│   │   │   ├── BaseUSTimeZone.php
│   │   │   ├── CountryTimeZoneInterface.php
│   │   │   ├── GlobalTimeZoneInterface.php
│   │   │   ├── TimeZoneCollection.php
│   │   │   ├── TimeZoneInterface.php
│   │   │   └── Baskets/
│   │   │       ├── CountryTimeZoneBasket.php
│   │   │       ├── GlobalTimeZoneBasket.php
│   │   │       └── TimeZoneBasket.php
│   │   └── Translator/            # Translation engine & file I/O
│   │       ├── ClientFilesGenerator.php
│   │       ├── LocalizationTranslator.php
│   │       └── LocalizationWriter.php
│   └── Tools/
│       ├── ReleaseBuilder.php         # Composer build script
│       └── Templates/                 # Code generation templates
│           ├── CannedCountriesTemplate.php.spf
│           ├── CannedCurrenciesTemplate.php.spf
│           └── CannedLocalesTemplate.php.spf
├── tests/
│   ├── bootstrap.php
│   ├── clear-class-cache.php
│   ├── assets/                        # Test fixture files
│   │   └── Parser/
│   ├── cache/
│   │   └── class-repository-v1.php
│   ├── phpstan/                       # PHPStan config (level 8)
│   ├── storage/                       # Test storage fixtures
│   └── testsuites/                    # PHPUnit test classes
│       ├── Core/
│       ├── Countries/
│       ├── Currencies/
│       ├── Events/
│       ├── Locale/
│       ├── Parser/
│       ├── TimeZones/
│       └── Translator/
├── vendor/                            # Composer dependencies (auto-generated)
├── changelog.md
├── composer.json
├── LICENSE
├── phpunit.xml
├── README.md
├── run-tests.bat
└── version.txt                        # Current version: 2.1.1
```
