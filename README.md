# Application Localization

A PHP and JavaScript localization library that automatically discovers translatable
strings in your source files and stores translations in simple INI files. Drop in
the built-in editor UI and your team can start translating without touching code.

## Features

- **Automatic string discovery** — scans PHP and JavaScript source files for translation function calls
- **Built-in translator UI** — a ready-made editor that integrates into any existing interface
- **Clientside translations** — auto-generated JavaScript include files bring `t()` to the browser
- **Translation context** — optional context strings help translators understand ambiguous texts
- **Countries, locales & currencies** — classes for 19 countries, 18 locales, 10 currencies, and 21 time zones with country-specific formatting
- **Locale alias handling** — automatically normalizes `uk`/`gb` and `en_UK`/`en_GB` mix-ups

> **Scope note:** The included countries, locales, and currencies reflect what is
> commonly needed in typical european-centric web applications, not a complete i18n
> universe. See the [full list of supported countries and locales](docs/overview.md).

## Requirements

- PHP 8.4 or higher
- [Composer](https://getcomposer.org/)

## Quick Start

**1. Install via Composer**

```bash
composer require mistralys/application-localization
```

**2. Configure the library**

```php
use AppLocalize\Localization;

// Register the locales your app supports (english is the native locale)
Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');

// Register a source folder to scan for translatable strings
Localization::addSourceFolder(
    'my-app',
    'My Application',
    'Core',
    '/path/to/ini/files/',
    '/path/to/source/files/'
);

// Finalize configuration (always last)
Localization::configure('/path/to/cache.json');
```

**3. Translate strings in PHP**

```php
use function AppLocalize\t;
use function AppLocalize\pt;

$message = t('Welcome back, %1$s!', $username);
pt('Save changes');
```

**4. Translate strings in JavaScript**

```javascript
var message = t('Welcome back, %1$s!', username);
```

**5. Switch the active locale**

```php
Localization::selectAppLocale('de_DE');
```

## Learn More

| Topic | Document |
|---|---|
| Full setup walkthrough | [Configuration Guide](docs/configuration.md) |
| Translation functions, placeholders & best practices | [Translation Functions](docs/translation-functions.md) |
| Countries, currencies & time zones API | [Countries, Currencies & Time Zones](docs/countries-currencies-timezones.md) |
| Supported countries, locales & currencies | [Supported countries and locales](docs/overview.md) |
| Changelog | [changelog.md](changelog.md) |
| Packagist | [mistralys/application-localization](https://packagist.org/packages/mistralys/application-localization) |

## Example

Run `composer update` in the package folder and open the `example/` directory in your
browser (package must be inside your webserver's document root) to see the editor UI live.

## Origins

This library grew out of several internal applications before being extracted into a
standalone package. It makes no claim of replacing established i18n libraries — it simply
solves a specific set of localization needs cleanly and pragmatically.
