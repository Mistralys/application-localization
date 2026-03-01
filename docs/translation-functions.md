# Translation Functions

## Serverside setup

To use the translation functions, add `use` statements for those you need:

```php
use function AppLocalize\t;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\tex;
use function AppLocalize\ptex;
use function AppLocalize\ptexs;
```

## The `t()` function

Be it serverside or clientside, use the `t()` function to have texts automatically
translated to the target locale. Simply wrap any native English text in the function call.

PHP:
```php
$text = t('Text to translate here');
```

JavaScript:
```javascript
var text = t('Text to translate here');
```

## The `pt()` and `pts()` functions

> Note: These are only available serverside.

These are the same as `t()`, except that they echo the translated string. Handy for templates:

```php
<title><?php pt('Page title') ?></title>
```

The `pts()` function adds a space after the translated string, useful for chaining:

```php
<div>
    <?php
        pts('First sentence here.');
        pts('Second sentence here.');
    ?>
</div>
```

## Using placeholders

The translation functions accept any number of additional parameters, which are injected
into the translated string using PHP's [sprintf][] function:

```php
$amount = 50;
$text = t('We found %1$s entries.', $amount);
```

Clientside, the same syntax applies:

```javascript
var amount = 50;
var text = t('We found %1$s entries.', amount);
```

> When using placeholders, always use numbered placeholders like `%1$s` or `%02d`.
> Their order often changes in translated texts, so numbered placeholders are critical
> for correct translation in all languages.

## Providing translation context information

When knowing the context of a text is critical for correct translation, use the
context-flavored translation functions:

| Without context | With context |
|---|---|
| `t()` | `tex()` |
| `pt()` | `ptex()` |
| `pts()` | `ptexs()` |

The context string comes directly after the text to translate:

```php
use function AppLocalize\ptex;

ptex(
    'Text to translate',
    'Context explanation for translators.'
);

// Placeholder values come after the context string
ptex(
    '%1$s records',
    'The placeholder contains the amount of records.',
    42
);
```

The context text must be a plain string — no variables or functions, since it is
extracted offline rather than evaluated at runtime. Linebreaks and string
concatenation are allowed. Basic HTML tags for formatting are supported.

---

## Tips & Best Practices

### Split sentences

The number of translatable texts in a typical application can grow very quickly.
Whenever possible, split longer texts into smaller sentences. This also allows
reusing text chunks in other places.

### Use numbered placeholders

Even if the syntax is more cumbersome than a simple `%s`, using numbered placeholders
is critical to allow for different sentence structures across languages.
A placeholder at the end of an English sentence may have to move to the beginning
in another language. Numbered placeholders make this easy.

> Note: Placeholders are highlighted in the localization UI so complex texts stay readable.

### Put HTML tags in placeholders

Tags like `<strong>` or `<em>` should not be embedded in translated strings — HTML is
in the layout domain, not the translation domain.

Wrap markup in placeholders instead:

```php
use function AppLocalize\t;

$text = t('This is %1$sbold%2$s text.', '<strong>', '</strong>');
```

This lets the application choose the exact tag (and add CSS classes later) without
touching any translated strings. The same applies to links:

```php
$textWithLink = t(
    'Please refer to the %1$sdocumentation%2$s for further details.',
    '<a href="http://...">',
    '</a>'
);
```

### Template texts

To use a translated text as a reusable template, replace the final value with
a placeholder of its own:

```php
use function AppLocalize\t;

$template = t('Entry number %1$s', '%1$s');
```

Translated to German, `$template` will contain `Eintrag Nummer %1$s`. You can then
use `sprintf` to fill it in repeatedly without calling `t()` on every iteration:

```php
for ($i = 1; $i <= 10; $i++) {
    echo sprintf($template, $i);
}
```

---

## Events

### Reacting to locale changes

The `LocaleChanged` event fires whenever a different locale is selected at runtime.
Register a listener with:

```php
use AppLocalize\Localization;
use AppLocalize\Localization\Event\LocaleChanged;

Localization::onLocaleChanged(function(LocaleChanged $event) {
    // react to the change
});
```

---

## Going further

The [Application Utils][] package provides a string builder class that integrates
with this package out of the box. Building complex translated sentences — including
in an HTML context — becomes straightforward:

```php
use function AppUtils\sb;

$html = (string)sb()
    ->bold(t('Easy string concatenation'))
    ->nl()
    ->t('For more information, see here:')
    ->link(t('Application Utils'), 'https://github.com/Mistralys/application-utils');
```

[sprintf]: https://www.php.net/manual/en/function.sprintf.php
[Application Utils]: https://github.com/Mistralys/application-utils
