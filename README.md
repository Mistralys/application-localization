# Application Localization

PHP and Javascript localization library, written in PHP. It is a simple localization layer that stores translated strings in ini files.

## Highlights

* Easy to configure for the application's source files structure
* Built-in translator UI that can be integrated into an existing UI
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

### Serverside setup

By default, to use the `t()` function, you have to add the namespace at the top of the files in which you wish to use it:

```php
use AppLocalize;
```

Otherwise, you would have to use the explicit syntax `\AppLocalize\t()`, which is not very practical.

The optimal way is to add aliases to the translation functions in your application:

```php
function t()
{
    return call_user_func_array('\AppLocalize\t', func_get_args());
}

function pt()
{
    return call_user_func_array('\AppLocalize\pt', func_get_args());
}
```

### The t() function

Be it serverside or clientside, you may use the `t()` function to have texts automatically translated to the target locale. Simply wrap any of the native english texts in the function call.

PHP:
```php
$text = t('Text to translate here');
```

JavaScript:
```javascript
var text = t('Text to translate here');
```

### The pt() function

This is the same as `t()`, except that it echos the translated string. This is handy for templates for example:

```php
<title><?php pt('Page title') ?></title>
```

Note: This is only available serverside.

### Using placeholders

The `t()` and `pt()` functions accept any number of additional parameters, which are injected into the translated string using the `sprintf` PHP function. This means you can use placeholders like this:

```php
$amount = 50;
$text = t('We found %1$s entries.', $amount);
```

Clientside, you may use the same syntax:

```javascript
var amount = 50;
var text = t('We found %1$s entries.', amount);
```

## Best practices

### Split sentences

The number of translateable texts in a typical application can grow very quickly. Whenever possible, try to split the texts into manageable chunks by splitting longer texts into smaller sentences. This has the added benefit of being able to reuse some of these text chunks in other places.

### Put HTML tags in placeholders

While tags like `<strong>` or `<em>` are harmless choices to include in texts to translate, it should still be avoided. HTML is in the layout domain, and on principle should not be handled by translators.

So ideally, a text with markup should look like this:

```php
$text = t('This is %1$sbold%2$s text.', '<strong>', '</strong>');
```
This way, the application can decide not only which tag to use, but also add classes to it if needed in the future - without having to touch any of the translated texts.

Same procedure for text links for example:

```php
$textWithLink = t(
    'Please refer to the %1$sdocumentation%2$s for further details.',
    '<a href="http://...">',
    '</a>'
);
```

## Examples

To run the example editor UI, simply run a `composer update` in the package folder, and open the `example` folder in your browser. 

## Origins

There are other localization libraries out there, but this was historically integrated in several applications. It has been moved to a separate package to make maintaining it easier. It has no pretention of rivalry with any of the established i18n libraries.
