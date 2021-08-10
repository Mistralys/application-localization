<?php
/**
 * File containing the {@link Localization_Locale} class.
 * @package Localization
 * @subpackage Core
 * @see Localization_Locale
 */

namespace AppLocalize;

use AppUtils\FileHelper;

/**
 * Individual locale representation with information about
 * the country and currency.
 *
 * @package Localization
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Localization_Locale
{
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

    public function __construct()
    {
        $localeName = explode('\\', get_class($this));
        $localeName = array_pop($localeName);
        $tokens = explode('_', $localeName);

        $this->localeName = $localeName;
        $this->countryCode = strtolower($tokens[1]);
        $this->languageCode = strtolower($tokens[0]);
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
     */
    abstract public function getLabel() : string;

    /**
     * Retrieves the country object for this locale
     *
     * @return Localization_Country
     *
     * @throws Localization_Exception
     * @see Localization::ERROR_COUNTRY_NOT_FOUND
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
     *
     * @return Localization_Currency
     *
     * @throws Localization_Exception
     * @see Localization::ERROR_COUNTRY_NOT_FOUND
     */
    public function getCurrency() : Localization_Currency
    {
        return $this->getCountry()->getCurrency();
    }
}