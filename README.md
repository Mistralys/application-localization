# Application Localization

PHP and Javascript localization library, written in PHP. It is a simple localization layer that stores translated strings in ini files.

## Highlights

* Easy to configure for the application's source files structure
* Built-in UI that can be integrated into an existing UI
* Supports clientside translations with auto-generated include files

## Configuration

### 1) Adding locales

Note: The native application locale should be english. Any additional locales can be added like this:

```php
\AppLocalize\Localization::addAppLocale('de_DE');
\AppLocalize\Localization::addAppLocale('fr_FR');
````
### 2) Adding file source folders

This defines in which folders to search for PHP or Javascript files. These files will be analyzed to find all places where there are translation function calls. 

Register each folder to look in like this: 

```php
$source = \AppLocalize\Localization::addSourceFolder(
    'source-slug', 
    'Source label', 
    'Group label', 
    '/path/to/ini/files/', 
    '/path/to/source/files/'
);
```

For performance reasons, it is recommended to exclude any files or folders that do not need to be analyzed. The Javascript analysis in particular still has issues with minified files like Jquery or Bootstrap, so they should definitely be excluded.

To exclude folders or files by name:

```php
$source->excludeFolder('foldername');
$source->excludeFile('jquery'); // any file with "jquery" in its name
$source->excludeFile('jquery-ui.min.js'); // by exact file name match
```

Note: No need to specifiy the absolute path or file name, as long as the name is unique.

### 3) Main configuration settings

Note: This must be done last, after the locales and sources have been defined.

```php
\AppLocalize\Localization::configure(
    '/path/to/analysis/cache.json', 
    '/path/to/javascript/includes/'
);
```
This sets where the cache file for the file analysis may be stored, and where the javascript include files should be written.

### 4) Select the target locale

Note: How to store the selected locale is entirely up to your application logic.

The locale is english by default, and you can switch the locale anytime using this:

```php
\AppLocalize\Localization::selectAppLocale('de_DE');
```

### 5) Include the client libraries (optional)

The localization library automatically creates the necessary javascript include files in the folder you specified in step 3). In your application, include the following files to enable the translation functions:

* `locale-xx.js`
* `md5.min.js`
* `translator.js`

Where `xx` is the two-letter ISO code of the target language. There is one for each of the locales you added.

## Using the translation functions

### The t() function

Be it serverside or clientside, you may can use the `t()` function to have texts automatically translated to the target locale. Simply wrap any of the native english texts in the function call.

PHP:
```php
$text = t('Text to translate here');
```

JavaScript:
```javascript
var text = t('Text to translate here');
```

### Injecting dynamic variables

The `t()` function accepts any number of additional parameters, which are injected into the translated string using the `sprintf` PHP function. This means you can do things like this:

```php
$amount = 50;
$text = t('We found %1$s entries.', $amount);
```

Clientside, you may use the same syntax:

```javascript
var amount = 50;
var text = t('We found %1$s entries.', amount);
```

## Examples

To run the example editor, simply run a `composer update` in the package folder, and open the `example` folder in your browser. 

## Origins

There are other localization libraries out there, but this was historically integrated in several applications. It has been moved to a separate package to make maintaining it easier. 
