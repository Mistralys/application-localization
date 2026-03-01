# Constraints & Conventions

## PHP Coding Standards

- **Strict types**: Every PHP file uses `declare(strict_types=1)`.
- **Target platform**: PHP 8.4+. Modern PHP features (union types, enums, readonly properties, etc.) are allowed.
- **PHPStan level 8**: Static analysis must pass cleanly. Configuration is in `tests/phpstan/config.neon`.

## Namespace Layout

- Root namespace: `AppLocalize`
- All classes under `src/Localization/` map to `AppLocalize\Localization\...`
- Global functions live in `AppLocalize` namespace (`src/functions.php`)
- Tools live under `AppLocalize\Tools` (`src/Tools/`)

## Static Architecture

- The `Localization` class is **entirely static**. It must never be instantiated.
- State is stored in static properties and reset via `Localization::reset()`.
- The `init()` method is called automatically at the bottom of `Localization.php` on file load.

## Dynamic Class Loading

- Countries (`Country/*.php`), currencies (`Currency/*.php`), locales (`Locale/*.php`), and time zones (`TimeZone/**/*.php`) are loaded dynamically via `BaseClassLoaderCollection` from the `application-utils-collections` package.
- A class repository cache file is generated at `cache/class-repository-v1.php`.
- **The cache must be cleared** whenever new country/locale/currency/timezone classes are added. This is done automatically on `composer dump-autoload` via the `post-autoload-dump` script, or manually with `composer clear-class-cache`.
- The cache folder defaults to `./cache` but can be overridden with the `LOCALIZATION_CACHE_FOLDER` constant (must be defined **before** requiring the autoloader).

## Translation Storage Format

- Translations are stored in **INI files** with the naming convention:
  `{locale}-{source-alias}-{type}.ini` (e.g., `de_DE-application-localization-server.ini`).
- Two file types per locale per source:
  - `server` — Strings used only in PHP
  - `client` — Strings used in JavaScript (also included in server files)
- Hash keys are **MD5 hashes** of the original English text.
- A `storage.json` file caches the full scanner results (serialized `StringCollection`).

## Translation Functions

- The English text is always the first parameter and serves as the **fallback** if no translation exists.
- Placeholders must use **numbered sprintf syntax** (`%1$s`, `%2$d`) to support reordering across languages.
- The `$context` parameter in `tex()`/`ptex()`/`ptexs()` is **never evaluated at runtime** — it is extracted statically by the parser for display in the editor UI.
- Translation functions are recognized by name: `t`, `pt`, `pts`, `tex`, `ptex`, `ptexs`. Only these names are scanned.

## Client-Side Libraries

- The three JS files (`translator.js`, `md5.min.js`, generated `locale-xx.js`) must all be included for client-side translations to work.
- Files are only regenerated when:
  1. The cache key changes (typically set to the app version via `setClientLibrariesCacheKey()`), **or**
  2. Translations are saved in the editor UI, **or**
  3. `writeClientFiles(true)` is called with `$force = true`.
- If `clientLibrariesFolder` is empty/omitted in `configure()`, client-side features are disabled entirely.
- **Call-ordering constraint:** `setClientLibrariesCacheKey()` and ALL `addAppLocale()` calls MUST be
  made **before** calling `configure()`. The system key (`Lib:x|System:x|Locales:x`) is computed at
  write time from the cache key and registered locale IDs. Calling `configure()` first results in an
  incomplete system key being persisted (e.g., missing locales in the `Locales:` segment), which will
  cause unnecessary file regeneration on every request.
  > **Note:** `addAppLocale()` does **not** fire any event. Only `selectAppLocale()` and
  > `setClientLibrariesCacheKey()` fire events that invalidate the in-memory system key. If locales
  > are added after `configure()`, you must also call `setClientLibrariesCacheKey()` with the same or
  > a new value to force the system key to be recomputed with the full locale list.

## Source Folder Registration

- Each source folder has a **unique alias** (slug) used in filenames and programmatic access.
- Sources are **sorted alphabetically by label** on registration.
- Source groups are used for UI grouping in the editor (e.g., "Composer packages", "Application").
- Folders and files can be excluded from scanning per source, which is recommended for minified vendor libraries.

## Locale Aliases

- The system handles common locale mistakes:
  - `uk` → `gb` (ISO country code)
  - `en_UK` → `en_GB` (locale name)
- This normalization happens in `LocalesCollection::filterName()` and `CountryCollection::filterCode()`.

## Built-in Native Locale

- The native locale is **always `en_GB`** (`Localization::BUILTIN_LOCALE_NAME`).
- It is automatically registered in both the application and content namespaces on `init()`.
- Texts in source code must be written in English.

## Error Code Convention

- Every exception class defines integer error code constants.
- Each subsystem uses a distinct numeric range (see `api-events-exceptions.md` for the complete map).
- Error codes are passed as the third parameter to exceptions, enabling programmatic identification.

## Canned Classes (Code Generation)

- `CannedCountries`, `CannedCurrencies`, and `CannedLocales` are **auto-generated** by `ReleaseBuilder::build()` using templates in `src/Tools/Templates/*.php.spf`.
- These should not be manually edited — they are regenerated on each release build.

## Testing

- Tests use **PHPUnit 12.x** with bootstrap at `tests/bootstrap.php`.
- The `phpunit.xml` configuration uses per-directory suites (no deprecated attributes):
  `Core`, `Countries`, `Currencies`, `Events`, `Locale`, `Parser`, `Scanner`, `TimeZones`, `Translator`.
- Run a named suite: `composer test-suite -- SuiteName`.
- Test fixtures are in `tests/assets/` and `tests/storage/`.
- Static analysis runs via `composer analyze` (`phpstan.neon`, level 8).

## No Non-Latin Script Support

- The package has **not been tested** with non-latin scripts (Chinese, Japanese, Korean, Vietnamese, Indian, Thai, etc.).
- Supported locales are limited to European languages and English variants.

## Editor UI Dependencies

- The editor UI uses:
  - **Bootstrap CSS** (loaded from CDN in the page scaffold)
  - **Font Awesome** (icons)
  - **jQuery** (for `editor.js` interaction)
  - **HTML_QuickForm2** (form rendering for locale selectors and filters)
