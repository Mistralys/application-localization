# Configuration Guide

## 1) Adding locales

The native application locale should be English. Any additional locales can be added like this:

```php
use AppLocalize\Localization;

Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');
```

> Note: The package has not been tested with non-latin scripts, like Sinic
> (Chinese, Japanese, Korean, Vietnamese) or Brahmic (Indian, Tibetan, Thai, Lao).

## 2) Adding file source folders

This defines in which folders to search for PHP or JavaScript files. These files will be
analyzed to find all places where there are translation function calls.

Register each folder to look in like this:

```php
use AppLocalize\Localization;

$source = Localization::addSourceFolder(
    'source-slug', // Must be unique: used in file names and to access the source programmatically
    'Source label', // Human readable label
    'Group label',  // Group labels are used to group sources in the UI
    '/path/to/ini/files/',    // The localization files will be stored here
    '/path/to/source/files/'  // The PHP and JavaScript source files to search through are here
);
```

## 3) Configure class loading (optional)

The package uses a class loader to load its classes dynamically.
This requires a cache folder to be set, whose default fallback
location is `./cache` unless otherwise specified.

> The default cache location is fine to use if this folder is writable.

The default can be overridden with the constant `LOCALIZATION_CACHE_FOLDER`,
which has to be present when the Composer autoload file is included.
The advantage is being able to use the same cache folder for all
class loaders to avoid multiple cache files to be loaded.

```php
// Must be defined before the autoloader
const LOCALIZATION_CACHE_FOLDER = '/path/to/cache';

require_once __DIR__ . '/vendor/autoload.php';
```

### Managing cache contents

The cache must be invalidated when the package is updated with new
countries, locales or the like. This can be easily done via the
Class Helper:

```php
use AppLocalize\Localization;

Localization::clearClassCache();
```

A good way to handle this is with Composer scripts.
Look at the [composer.json](../composer.json) and the
[clear-class-cache.php](../tests/clear-class-cache.php) files
for an example.

### Excluding files and folders

For performance reasons, it is recommended to exclude any files or folders that do not
need to be analyzed. The JavaScript analysis in particular still has issues with minified
library files like jQuery or Bootstrap, so they should definitely be excluded.

To exclude folders or files by name:

```php
$source->excludeFolder('foldername');
$source->excludeFile('jquery');           // any file with "jquery" in its name
$source->excludeFile('jquery-ui.min.js'); // by exact file name match
```

> Note: No need to specify the absolute path or file name, as long as the name is unique.

## 4) Main configuration settings

> Note: This must be done last, after the locales and sources have been defined.

```php
use AppLocalize\Localization;

Localization::configure(
    '/path/to/analysis/cache.json',    // Where the text information cache may be saved
    '/path/to/javascript/includes/'    // Where the clientside files should be stored (optional)
);
```

If no path is specified for the clientside includes, they will be disabled.

## 5) Select the target locale

The locale is English by default, and you can switch the locale anytime using this:

```php
use AppLocalize\Localization;

Localization::selectAppLocale('de_DE');
```

> Note: Your application logic must handle the decision of which locale to use.

## 6) Include the client libraries (optional)

The localization library automatically creates the necessary JavaScript include files
in the folder you specified in step 4. In your application, include the following
files to enable the translation functions:

- `locale-xx.js` — one file per locale, where `xx` is the two-letter ISO language code
- `md5.min.js`
- `translator.js`

> Once the JavaScript include files have been written, they are only refreshed
> whenever texts are updated in the localization editor UI. We recommend using
> a cache key (see below).

### Using a cache key to update libraries

The libraries cache key is an arbitrary string that can be set. Whenever this changes, the
JavaScript include files are refreshed automatically. A good way to keep them up to date
is to use your application's version number as cache key.

```php
$myAppVersion = 'v1.5.1';

Localization::setClientLibrariesCacheKey($myAppVersion);
```

Refreshing the libraries is then done automatically with each release of your application.
