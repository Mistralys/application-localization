<?php

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
 * @return string
 * @see pt()
 */
function t()
{
    $arguments = func_get_args();
    
    return call_user_func_array(
        array(Localization::getTranslator(), 'translate'),
        $arguments
    );
}

/**
 * Same as the {@link t()} function, but echos the
 * translated string.
 *
 * @see t()
 */
function pt()
{
    $arguments = func_get_args();
    
    echo call_user_func_array(
        array(Localization::getTranslator(), 'translate'),
        $arguments
    );
}

/**
 * Same as the {@link pt()} function, but adds a space after
 * the string, so several texts can be chained in an HTML 
 * document, without having to manually add spaces inbetween.
 * 
 * @see pt()
 */
function pts()
{
    $arguments = func_get_args();
    
    echo call_user_func_array(
        array(Localization::getTranslator(), 'translate'),
        $arguments
    );
    
    echo ' ';
}
