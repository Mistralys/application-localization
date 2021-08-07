<?php
/**
 * File containing the global translation functions.
 *
 * @package AppLocalize
 * @see \AppLocalize\t()
 * @see \AppLocalize\pt()
 * @see \AppLocalize\pts()
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * Translates the specified string by looking up the
 * translations table. Returns the translated string
 * according to the current application locale.
 *
 * Not to confound with content locales! This function
 * serves only for translations within the UI itself,
 * not user contents.
 *
 * If no translation is found, returns the original string.
 *
 * Use the sister function {@link pt()} to translate
 * and echo a string directly.
 *
 * @package AppLocalize
 * @param string $text
 * @param mixed ...$args
 * @return string
 * @see \AppLocalize\pt()
 */
function t(string $text, ...$args) : string
{
    return call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );
}

/**
 * Same as the {@link t()} function, but echos the
 * translated string.
 *
 * @see \AppLocalize\t()
 */
function pt(string $text, ...$args) : void
{
    echo call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );
}

/**
 * Same as the {@link pt()} function, but adds a space after
 * the string, so several texts can be chained in an HTML
 * document, without having to manually add spaces in between.
 *
 * @param string $text
 * @param mixed ...$args
 * @see \AppLocalize\pt()
 */
function pts(string $text, ...$args) : void
{
    echo call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );
    
    echo ' ';
}

/**
 * Like {@see \AppLocalize\t()}, but the second parameter
 * allows specifying context for translators, in cases
 * where a text to translate can be ambiguous, or to
 * explain the content of placeholders.
 *
 * @param string $text
 * @param string $context Translation context hints, shown in the translation UI.
 * @param mixed ...$args
 * @return string
 * @see \AppLocalize\t()
 */
function tex(string $text, string $context, ...$args) : string
{
    unset($context); // Only used by the parser.

    return call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );
}

/**
 * Like {@see \AppLocalize\pt()}, but with translation
 * context information.
 *
 * @param string $text
 * @param string $context Translation context hints, shown in the translation UI.
 * @param mixed ...$args
 * @see \AppLocalize\pt()
 */
function ptex(string $text, string $context, ...$args) : void
{
    unset($context); // Only used by the parser.

    echo call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );
}

/**
 * Like {@see \AppLocalize\pts()}, but with translation
 * context information.
 *
 * @param string $text
 * @param string $context Translation context hints, shown in the translation UI.
 * @param mixed ...$args
 * @see \AppLocalize\pts()
 */
function ptexs(string $text, string $context, ...$args) : void
{
    unset($context); // Only used by the parser.

    echo call_user_func(
        array(Localization::getTranslator(), 'translate'),
        $text,
        $args
    );

    echo ' ';
}
