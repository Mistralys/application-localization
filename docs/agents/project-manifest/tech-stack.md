# Tech Stack & Patterns

## Runtime & Language

| Item | Value |
|---|---|
| Language | PHP |
| Target Platform | PHP 8.4+ |
| Type Declarations | `declare(strict_types=1)` in every file |
| Client-side | JavaScript (vanilla, no framework) |

## Package Manager & Autoloading

| Item | Value |
|---|---|
| Package Manager | Composer |
| Package Name | `mistralys/application-localization` |
| Autoloading | PSR-4 (`AppLocalize\` → `src/`), classmap (`src/`), files (`src/functions.php`) |
| Minimum Stability | `dev` (prefer-stable) |

## Dependencies (Runtime)

| Package | Purpose |
|---|---|
| `mistralys/application-utils-core` ≥ 2.3.11 | Base utilities (ClassHelper, FileHelper, BaseException, etc.) |
| `mistralys/application-utils-collections` ≥ 1.2.1 | `BaseClassLoaderCollection`, `GenericStringPrimaryBasket` base classes |
| `mistralys/php-sprintf-parser` ^1.0 | Placeholder validation in translated strings |
| `mistralys/html_quickform2` ≥ 2.3.5 | Form elements for the Editor UI locale selectors |
| `mistralys/changelog-parser` ≥ 1.0.2 | Changelog parsing for release tooling |
| `mck89/peast` ≥ 1.17.0 | JavaScript AST parser (replaced legacy JTokenizer) |
| `ext-json` | JSON encode/decode for storage |
| `ext-mbstring` | Multibyte string handling |

## Dependencies (Dev)

| Package | Purpose |
|---|---|
| `phpunit/phpunit` ≥ 9.6 | Unit testing |
| `phpstan/phpstan` ≥ 0.12 | Static analysis (clean at level 8) |

## Architectural Patterns

- **Static Facade**: The central `Localization` class is an entirely static API. All configuration, locale management, source registration, and factory methods are static. No instantiation required.
- **Dynamic Class Loading (Class Repository)**: Countries, currencies, locales, and time zones are loaded via `BaseClassLoaderCollection` from the AppUtils Collections package. Each entity type has a dedicated `src/Localization/<Type>/` folder containing one PHP class per entity, discovered at runtime and cached via `ClassRepositoryManager`.
- **Canned Access Pattern**: Each collection exposes a `choose()` method returning a "Canned" helper class with named accessor methods (e.g., `->choose()->de()`) for type-safe IDE-friendly access.
- **Basket Collections**: Freeform subsets of records (countries, locales, timezones) are represented via `*Basket` classes extending `GenericStringPrimaryBasket`.
- **INI-based Translation Storage**: Translated strings are stored in `.ini` files per locale and source, split into client/server variants.
- **Event-Driven**: Three events (`LocaleChanged`, `CacheKeyChanged`, `ClientFolderChanged`) notify listeners of configuration changes.
- **Namespace Separation**: Locales are organized into namespaces (`__application`, `__content`, or custom) to separate UI translations from content translations.

## Build & Tooling

| Tool | Command | Description |
|---|---|---|
| PHPUnit | `vendor/bin/phpunit` | Runs test suites from `tests/testsuites/` |
| PHPStan | `vendor/bin/phpstan` | Static analysis via `tests/phpstan/config.neon` at level 8 |
| Class Cache Clear | `composer clear-class-cache` | Clears the dynamic class loader cache (also runs on `post-autoload-dump`) |
| Release Builder | `composer build` | Invokes `ReleaseBuilder::build()` to regenerate canned classes and docs |
| Tests (Windows) | `run-tests.bat` | Batch shortcut for running PHPUnit |

## JavaScript Components

The JavaScript side consists of three files distributed to client applications:

| File | Purpose |
|---|---|
| `translator.js` | Client-side `t()` function + `AppLocalize_Translator` object with sprintf support |
| `md5.min.js` | MD5 hashing for string lookup (matches server-side hash keys) |
| `locale-xx.js` | Auto-generated per-locale file registering translated strings via `AppLocalize_Translator.a()` |
| `editor.js` | Editor UI toggle/confirm interaction (jQuery-based) |
