# Application Localization

PHP and JavaScript localization library, written in PHP. It is a simple but powerful
localization layer that stores translated strings in ini files.

## Highlights

* Easy to adjust to an application's file structure
* Automatic discovery of texts in PHP and JavaScript source files
* Built-in translator UI that can be integrated into an existing UI
* Clientside translations with auto-generated include files
* Translations with or without translation context information for translators

## Installation

Require the package via composer:

```
composer require mistralys/application-localization
```

Also see the [Packagist page][].

## Configuration

### 1) Adding locales

The native application locale should be english. Any additional locales can be added like this:

```php
use AppLocalize\Localization;

Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');
````

> Note: The package has not been tested with non-latin scripts, like Sinic 
> (Chinese, Japanese, Korean, Vietnamese) or Brahmic (Indian, Tibetan, Thai, Lao).

### 2) Adding file source folders

This defines in which folders to search for PHP or Javascript files. These files will be 
analyzed to find all places where there are translation function calls. 

Register each folder to look in like this: 

```php
use AppLocalize\Localization;

$source = Localization::addSourceFolder(
    'source-slug', // Must be unique: used in file names and to access the source programmatically
    'Source label', // Human readable label
    'Group label', // Group labels are used to group sources in the UI
    '/path/to/ini/files/', // The localization files will be stored here
    '/path/to/source/files/' // The PHP and JavaScript source files to search through are here
);
```

#### Excluding files and folders

For performance reasons, it is recommended to exclude any files or folders that do not 
need to be analyzed. The Javascript analysis in particular still has issues with minified 
library files like jQuery or Bootstrap, so they should definitely be excluded.

To exclude folders or files by name:

```php
$source->excludeFolder('foldername');
$source->excludeFile('jquery'); // any file with "jquery" in its name
$source->excludeFile('jquery-ui.min.js'); // by exact file name match
```

> Note: No need to specify the absolute path or file name, as long as the name is unique.

### 3) Main configuration settings

> Note: This must be done last, after the locales and sources have been defined.

```php
use AppLocalize\Localization;

Localization::configure(
    '/path/to/analysis/cache.json', // Where the text information cache may be saved
    '/path/to/javascript/includes/' // Where the clientside files should be stored (Optional)
);
```

If no path is specified for the clientside includes, they will be disabled.

### 4) Select the target locale

The locale is english by default, and you can switch the locale anytime using this:

```php
use AppLocalize\Localization;

Localization::selectAppLocale('de_DE');
```

> Note: Your application logic must handle the decision of which locale to use.

### 5) Include the client libraries (optional)

The localization library automatically creates the necessary javascript include files
in the folder you specified in step 3). In your application, include the following 
files to enable the translation functions:

* `locale-xx.js`
* `md5.min.js`
* `translator.js`

Where `xx` is the two-letter ISO code of the target language. There will be one for 
each of the locales you added.

> Once the javascript include files have been written, they are only refreshed 
> whenever texts are updated in the localization editor UI. We recommend using
> a cache key (see below).

#### Using a cache key to update libraries 

The libraries cache key is an arbitrary string that can be set. Whenever this changes, the 
javascript include files are refreshed automatically. A good way to keep them up to date 
is to use your application's version number as cache key.

```php
$myAppVersion = 'v1.5.1';

Localization::setClientLibrariesCacheKey($myAppVersion);
```

Refreshing the libraries is then done automatically with each release of your application.

## Using the translation functions

### Serverside setup

To use the translation functions, you have to add use statements for those you need: 

```php
use function AppLocalize\t;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\tex;
use function AppLocalize\ptex;
use function AppLocalize\ptexs;
```

### The `t()` function

Be it serverside or clientside, you may use the `t()` function to have texts automatically 
translated to the target locale. Simply wrap any of the native english texts in the 
function call.

PHP:
```php
$text = t('Text to translate here');
```

JavaScript:
```javascript
var text = t('Text to translate here');
```

### The `pt()` and `pts()` functions

> Note: This is only available serverside.

These are the same as `t()`, except that they echo the translated string. This is 
handy for templates for example:

```php
<title><?php pt('Page title') ?></title>
```

The `pts()` function adds a space after the translated string, so that you do not have 
to manually add spaces when chaining several strings:

```php
<div>
    <?php
        pts('First sentence here.');
        pts('Second sentence here.');
    ?>
</div>   
```

### Using placeholders

The translation functions accept any number of additional parameters, which are injected 
into the translated string using the [sprintf][] PHP function. This means you can use 
placeholders like this:

```php
$amount = 50;
$text = t('We found %1$s entries.', $amount);
```

Clientside, you may use the same syntax:

```javascript
var amount = 50;
var text = t('We found %1$s entries.', amount);
```

> When using placeholders, we recommend systematically using numbered placeholders
> like `%1$s` or `%02d`. Primarily because the order of placeholders often changes 
> in translated texts, but also to avoid confusion for whoever does the translating.

### Providing translation context information

In some cases, knowing in which context a text is used will be critical to correctly
translate it. The context flavored translation functions offer a second parameter
dedicated to adding this context information.

- `t()` -> `tex()`
- `pt()` -> `ptex()`
- `pts()` -> `ptexs()`

```php
use function AppLocalize\ptex;

// Context information comes directly after the text.
ptex(
    'Text to translate', 
    'Context explanation for translators.'
);

// Placeholder values come after the context information.
ptex(
    '%1$s records', 
    'The placeholder contains the amount of records.', 
    42
);
```

The context text must be a string, just like the text to translate.
Linebreaks and string concatenation are allowed, but no variables or functions,
since this extracted offline, instead of being evaluated at runtime.

> Hint: It is possible to use basic HTML tags for formatting.

## Tips & best practices

### Split sentences

The number of translatable texts in a typical application can grow very quickly. 
Whenever possible, try to split the texts into manageable chunks by splitting longer 
texts into smaller sentences. This has the added benefit of being able to reuse some 
of these text chunks in other places.

### Use numbered placeholders

Even if the syntax is more cumbersome than a simple `%s`, using numbered placeholders 
is critical to allow for different sentence structures depending on the language. 
A placeholder placed at the end of a sentence may have to be moved to the beginning 
of the text in another language. Using numbered placeholders makes this easy.

> Note: placeholders are highlighted in the localization UI, so that complex texts stay readable.

### Put HTML tags in placeholders

While tags like `<strong>` or `<em>` are harmless choices to include in texts to 
translate, it should still be avoided. HTML is in the layout domain, and on 
principle should not be handled by translators.

So ideally, a text with markup should look like this:

```php
use function AppLocalize\t;

$text = t('This is %1$sbold%2$s text.', '<strong>', '</strong>');
```

This way, the application can decide not only which tag to use, but also add classes 
to it if needed in the future - without having to touch any of the translated texts.

Same procedure for text links for example:

```php
use function AppLocalize\t;

$textWithLink = t(
    'Please refer to the %1$sdocumentation%2$s for further details.',
    '<a href="http://...">',
    '</a>'
);
```

### Template texts

To use a translated text as a template to re-use multiple times, simply replace 
the placeholders with placeholders.

Sounds strange? Look at this example:

```php
use function AppLocalize\t;

$template = t('Entry number %1$s', '%1$s');
```

Translated to german, the text in the variable `$template` would look like this:

```
Eintrag Nummer %1$s
```

This means you can now use the template multiple times without calling the 
translation function each time, with the `sprintf` PHP function:

```php
for($i=1; $i <= 10; $i++)
{
    echo sprintf($template, $i);
}
```

## Going further

The [Application Utils][] package has a string builder class used for concatenating 
strings, and which supports this package out of the box. Building complex sentences 
is easy with this, including in an HTML context.

Example:

```php
use function AppUtils\sb;

$html = (string)sb()
  ->bold(t('Easy string concatenation'))
  ->nl()
  ->t('For more information, see here:')
  ->link(t('Application Utils'), 'https://github.com/Mistralys/application-utils');
```

## Events

### When the active locale is changed

The `LocaleChanged` event is triggered when a different locale is selected
at runtime. It is possible to add a listener to this event, and react to
locale changes.

Here is an example:

```php
use AppLocalize\Localization;
use AppLocalize\Localization\Event\LocaleChanged;

Localization::onLocaleChanged(function(LocaleChanged $event) {
    // do something
});
```

## Countries and Currencies

The library comes with a collection of countries and currencies for the 
supported locales. These allow accessing general information about countries
and currencies, like names, symbols, and codes.

### Countries

To work with countries, use the factory method:

```php
use AppLocalize\Localization;
use AppLocalize\Localization_Country_ES;

$countries = Localization::createCountries();

// Get a country by its two-letter ISO code
$germany = $countries->getByISO('de');

// Every country has a constant for its ISO code
$spain = $countries->getByISO(Localization_Country_ES::ISO_CODE);

// Or use the predefined list using choose():
$france = $countries->choose()->fr();
```

### Currencies

To work with currencies, use the factory method:

```php
use AppLocalize\Localization;
use AppLocalize\Localization_Currency_EUR;

$currencies = Localization::createCurrencies();

// Get a currency by its three-letter ISO code
$dollar = $currencies->getByISO('USD');

// Every currency has a constant for its ISO code
$euro = $currencies->getByISO(Localization_Currency_EUR::ISO_CODE);

// Or use the predefined list using choose():
$pound = $currencies->choose()->gbp();
```

#### Country-specific currencies

When getting a currency from a country, the currency offers formatting
features that are adjusted to the country's preferences.

```php
use AppLocalize\Localization;

$eurDE = Localization::createCountries()
    ->choose()
    ->de()
    ->getCurrency();

echo $eurDE->makeReadable(1445.42);
```

This will output:

```
1.445,42 €
```

## Examples

To run the example editor UI, simply run a `composer update` in the package folder, 
and open the `example` folder in your browser (provided the package is in your 
webserver's webroot). 

## Origins

There are other localization libraries out there, but this was historically integrated 
in several applications. It has been moved to a separate package to make maintaining 
it easier. It has no pretension of rivalry with any of the established i18n libraries.



[Packagist page]: https://packagist.org/packages/mistralys/application-localization
[sprintf]: https://www.php.net/manual/en/function.sprintf.php
[Application Utils]: https://github.com/Mistralys/application-utils
