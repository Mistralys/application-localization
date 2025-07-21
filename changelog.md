## v2.0.1 - Added the Netherlands and Sweden
- Countries: Added the Netherlands (`NL`).
- Countries: Added Sweden (`SE`).
- Currencies: Added the Swedish Krona (`SEK`).
- Currencies: Added methods `getSingularInvariant()` and `getPluralInvariant()`.
- Currencies: Canadian Dollars are now called "Canadian Dollars" instead of just "Dollars".
- Currencies: Singapore Dollars are now called "Singapore Dollars" instead of just "Dollars".
- Locales: Added the `choose()` method to access known locales by methods.
- Build: Now generating the canned locales class automatically.

## v2.0.0 - Core revamp and new countries (Breaking-XL)
- Countries: Added Finland (`FI`).
- Countries: Added Belgium (`BE`).
- Countries: Added Singapore (`SG`). 
- Countries: Added Ireland (`IE`).
- Countries: Added `getLabelInvariant()` to get the label in invariant locale.
- Countries: Now using dynamic class loading.
- Countries: Renamed and namespaced all country classes.
- Countries: Added `getMainLocale()`.
- Countries: Now handling ISO code aliases like `uk` for `gb`.
- Currencies: Now using dynamic class loading.
- Currencies: Renamed and namespaced all currency classes.
- Currencies: Added the Singapore Dollar (`SGD`).
- Locales: Renamed and namespaced all locale classes.
- Locales: Added `getLabelInvariant()` to get the label in the invariant locale.
- Locales: Normalized the labels that were not consistently showing the country name.
- Locales: Now handling locale code aliases like `en_UK` for `en_GB`.
- Tests: Namespaced and organized all test classes.
- Tests: Added a trait text translation test ([#2](https://github.com/Mistralys/application-localization/issues/2)).
- Editor: Better path display for files with warnings.
- Parser: Replaced the obsolete JTokenizer with [Peast](https://github.com/mck89/peast).
- Translator: Improved exception message for mismatched placeholders, now also with error code.
- Composer: Added the `build-release` command.
- Composer: Added the `clear-class-cache` command.
- Composer: The class cache is now automatically cleared on autoloader generation.
- Documentation: An overview of countries, locales and currencies is now generated.
- Code: PHPStan analysis is now clean, up to level 6.
- Code: PHP8.4 compatible.
- Core: Switched license to MIT (was GPL3).
- Dependencies: Updated AppUtils Core to [v2.3.11](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.11).
- Dependencies: Updated AppUtils Collections to [v1.1.5](https://github.com/Mistralys/application-utils-collections/releases/tag/1.1.5).

### Breaking changes

Virtually all classes have been renamed and namespaced, and any
references to them must be updated in your applications. 
I recommend using a static code analysis tool like [PHPStan](https://phpstan.org/)
to find all references to the old class names.

All deprecated methods have been removed.

-----

Older change log entries can be found under `docs/changelog-history`.
