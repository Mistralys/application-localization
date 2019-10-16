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
     * @var Localization_Country
     */
    protected $country;
    
   /**
    * @var string
    */
    protected $countryCode;
    
   /**
    * @var string
    */
    protected $languageCode;

    /**
     * @param string $localeName
     * @throws Localization_Exception
     */
    public function __construct(string $localeName)
    {
        if(!self::isLocaleKnown($localeName)) 
        {
            throw new Localization_Exception(
                'Invalid locale',
                sprintf(
                    'The locale [%s] is not known. Valid locales are: [%s].',
                    $localeName,
                    implode(', ', Localization::getSupportedLocaleNames())
                ),
                self::ERROR_UNKNOWN_LOCALE_NAME
            );
        }

        $tokens = explode('_', $localeName);
        
        $this->localeName = $localeName;
        $this->countryCode = strtolower($tokens[1]);
        $this->languageCode = $tokens[0];
    }
    
   /**
    * Retrieves the two-letter language code of the locale.
    * 
    * @return string Language code, e.g. "en", "de"
    */
    public function getLanguageCode() : string
    {
        return $this->languageCode;
    }

    /**
     * Checks whether the specified locale name is known
     * (supported by the application).
     *
     * @param string $localeName
     * @return boolean
     */
    public static function isLocaleKnown(string $localeName) : bool
    {
        return Localization::isLocaleSupported($localeName);
    }

    /**
     * Returns the locale name, e.g. "en_US"
     * @return string
     */
    public function getName() : string
    {
        return $this->localeName;
    }
    
   /**
    * Retrieves the shortened version of the locale name,
    * e.g. "en" or "de".
    *
    * @return string
    * @deprecated
    * @see Localization_Locale::getLanguageCode()
    */
    public function getShortName() : string
    {
        return $this->getLanguageCode();
    }
    
   /**
    * Retrieves the two-letter country code of
    * the locale.
    * 
    * @return string Lowercase code, e.g. "uk"
    */
    public function getCountryCode() : string
    {
        return $this->countryCode;
    }

    /**
     * Checks if this locale is the builtin application locale
     * (the one in which application strings are written).
     *
     * @return boolean
     * @see Localization::BUILTIN_LOCALE_NAME
     */
    public function isNative() : bool
    {
        return $this->getName() == Localization::BUILTIN_LOCALE_NAME;
    }

    /**
     * Returns the localized label for the locale, e.g. "German"
     * 
     * @return string
     * @throws Localization_Exception
     */
    public function getLabel() : string
    {
        switch($this->localeName) 
        {
            case 'de_DE':
                return t('German');

            case 'en_US':
                return t('English (US)');

            case 'en_UK':
                return t('English (UK)');
                
            case 'en_CA':
                return t('English (CA)');

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
    public function getCountry() : Localization_Country
    {
        if(!isset($this->country)) {
            $this->country = Localization::createCountry($this->countryCode);
        }
        
        return $this->country;
    }

    /**
     * Retrieves the currency object for this locale
     * @return Localization_Currency
     */
    public function getCurrency() : Localization_Currency
    {
        return $this->getCountry()->getCurrency();
    }
}