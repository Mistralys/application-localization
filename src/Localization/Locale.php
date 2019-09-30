<?php
/**
 * File containing the {@link Localization_Locale} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Locale
 */

namespace AppLocalize;

/**
 * Individual locale representation with information about
 * the country and currency.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Locale
{
    const ERROR_UNKNOWN_LOCALE_NAME = 39101;
    
    const ERROR_LOCALE_LABEL_MISSING = 39102;
    
    /**
     * @var string
     */
    private $localeName;

    /**
     * Indxed array with locale names supported by the application
     * @var array
     */
    protected static $knownLocales = array(
        'de_DE',
        'en_US',
        'en_UK',
        'es_ES',
        'fr_FR',
        'pl_PL',
        'it_IT',
        'de_AT',
        'de_CH'
    );

    /**
     * @var Localization_Country
     */
    protected $country;

    /**
     * @param string $localeName
     * @throws Localization_Exception
     */
    public function __construct($localeName)
    {
        if(!self::isLocaleKnown($localeName)) 
        {
            throw new Localization_Exception(
                'Invalid locale',
                sprintf(
                    'The locale [%s] is not known. Valid locales are: [%s].',
                    $localeName,
                    implode(', ', self::$knownLocales)
                ),
                self::ERROR_UNKNOWN_LOCALE_NAME
            );
        }

        $this->localeName = $localeName;

        $tokens = explode('_', $localeName);
        $country = strtolower(array_pop($tokens));
        $this->country = Localization::createCountry($country);
    }

    /**
     * Checks whether the specified locale name is known
     * (supported by the application).
     *
     * @param string $localeName
     * @return boolean
     */
    public static function isLocaleKnown($localeName)
    {
        return in_array($localeName, self::$knownLocales);
    }

    /**
     * Returns the locale name, e.g. "en_US"
     * @return string
     */
    public function getName()
    {
        return $this->localeName;
    }
    
   /**
    * Retrieves the shortened version of the locale name,
    * e.g. "en" or "de".
    *
    * @return string
    */
    public function getShortName()
    {
        return substr($this->localeName, 0, 2);
    }

    /**
     * Checks if this locale is the builtin application locale
     * (the one in which application strings are written).
     *
     * @return boolean
     * @see Localization::BUILTIN_LOCALE_NAME
     */
    public function isNative()
    {
        if ($this->getName() == Localization::BUILTIN_LOCALE_NAME) {
            return true;
        }

        return false;
    }

    /**
     * Returns the localized label for the locale, e.g. "German"
     * @return string
     */
    public function getLabel()
    {
        switch ($this->localeName) {
            case 'de_DE':
                return t('German');

            case 'en_US':
                return t('English (US)');

            case 'en_UK':
                return t('English (UK)');

            case 'es_ES':
                return t('Spanish');

            case 'pl_PL':
                return t('Polish');
                
            case 'fr_FR':
                return t('French');
        }

        throw new Localization_Exception(
            'Label is missing for the locale ' . $this->localeName,
            null,
            self::ERROR_LOCALE_LABEL_MISSING
        );
    }

    /**
     * Retrieves the country object for this locale
     * @return Localization_Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Retrieves the currency object for this locale
     * @return Localization_Currency
     */
    public function getCurrency()
    {
        return $this->country->getCurrency();
    }
}