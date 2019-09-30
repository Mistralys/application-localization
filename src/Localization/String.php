<?php
/**
 * File containing the {@link Localization_String} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_String
 */

namespace AppLocalize;

/**
 * Container for localized user data. Used only in conjunction
 * with user-provided data, as this uses only the content
 * locales.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_String
{
    protected $locales = array();

    protected $contentLocales;

    protected $totalContentLocales = 0;

    /**
     * It is possible to set the contents for a range of locales
     * directly here by providing an array in the format required
     * by the {@link fromArray()} method.
     *
     * @param array $localesArray
     */
    public function __construct($localesArray = array())
    {
        // keep a local copy for performance reasons
        $this->contentLocales = Localization::getContentLocales();
        $this->totalContentLocales = count($this->contentLocales);
        $this->fromArray($localesArray);
    }

    /**
     * Sets the content for the specified locale.
     * @param Localization_Locale $locale
     * @param string $value
     */
    public function set(Localization_Locale $locale, $value)
    {
        $this->locales[$locale->getName()] = $value;
    }

    /**
     * Retrieves the content for the specified locale.
     * Returns NULL if none is avaiable.
     *
     * @param Localization_Locale $locale
     * @return mixed|NULL
     */
    public function get(Localization_Locale $locale)
    {
        $name = $locale->getName();
        if (isset($this->locales[$name])) {
            return $this->locales[$name];
        }

        return null;
    }

    /**
     * Retrieves the content for the current content locale.
     * Returns NULL if none is available.
     * @return mixed|NULL
     */
    public function getCurrent()
    {
        return $this->get(Localization::getContentLocale());
    }

    public function setCurrent($value)
    {
        $this->set(Localization::getContentLocale(), $value);
    }

    /**
     * Updates this localized string with the data from
     * the specified string (merges and overwrites, ignores
     * empty locales)
     *
     * @param Localization_String $string
     */
    public function updateWith(Localization_String $string)
    {
        foreach ($this->contentLocales as $locale) {
            $value = $string->get($locale);
            if (!is_null($value)) {
                $this->set($locale, $value);
            }
        }
    }

    /**
     * Serializes the string to an associative array
     * with locale names > texts.
     *
     * array(
     *     'en_UK' => 'English content',
     *     'es_ES' => 'Spanish content',
     *     [...]
     * )
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->contentLocales as $localeName => $locale) {
            $result[$localeName] = $this->get($locale);
        }

        return $result;
    }

    /**
     * Sets translated texts from a locales array
     * previously created with toArray().
     *
     * @param array $localesArray
     */
    public function fromArray($localesArray)
    {
        foreach ($this->contentLocales as $localeName => $locale) {
            if (isset($localesArray[$localeName])) {
                $this->set($locale, $localesArray[$localeName]);
            }
        }
    }

    /**
     * Checks if there is content for the specified locale.
     * @param Localization_Locale $locale
     * @return boolean
     */
    public function isLocaleComplete(Localization_Locale $locale)
    {
        $value = $this->get($locale);

        return !empty($value);
    }

    /**
     * Checks if content is available for the current content locale.
     * @return boolean
     */
    public function isCurrentLocaleComplete()
    {
        return $this->isLocaleComplete(Localization::getContentLocale());
    }

    /**
     * Checks if content is available for all currently available
     * content locales.
     *
     * @return boolean
     */
    public function isComplete()
    {
        foreach ($this->contentLocales as $locale) {
            if (!$this->isLocaleComplete($locale)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the text for the current content locale.
     * @return string
     */
    public function __toString()
    {
        $value = $this->get(Localization::getContentLocale());
        if (is_string($value)) {
            return $value;
        }

        return '';
    }
}