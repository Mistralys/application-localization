# Application Localization

PHP and Javascript localization library, written in PHP. It is a simple localization layer that stores translated strings in ini files.

## Highlights

* Easy to configure for the application's source files structure
* Built-in UI that can be integrated into an existing UI

## Configuration

### 1) Adding locales

Note: The native application locale should be english. Any additional locales can be added like this:

```php
\AppLocalize\Localization::addAppLocale('de_DE');
\AppLocalize\Localization::addAppLocale('fr_FR');
````
### 2) Adding file source folders

Source folders are any folders in which to search for PHP or Javascript files to analyze for translation function calls. Register each folder to look in like this: 

```php
$source = \AppLocalize\Localization::addSourceFolder(
    'source-slug', 
    'Source label', 
    'Group label', 
    '/path/to/ini/files/', 
    '/path/to/source/files/'
);
```

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

### 4) Include the client libraries

The localization library automatically creates the necessary javascript include files in the folder you specified in step 3). In your application, include the following files to enable the translation:

* `locale-xx.js`
* `md5.min.js`
* `translator.js`

Where `xx` is the two-letter ISO code of the target language.

### 5) Use the t() function to translate texts

Be it serverside or clientside, you may now use the `t()` function to have texts automatically translated to the target locale.

## Examples

To run the example editor, simply run a `composer update` in the package folder, and open the `example` folder in your browser. 

## Origins

There are other localization libraries out there, but this was historically integrated in several applications. It has been moved to a separate package to make maintaining it easier. 
