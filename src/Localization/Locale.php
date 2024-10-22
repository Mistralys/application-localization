<?php
/**
 * File containing the {@link Localization_Locale} class.
 * @package Localization
 * @subpackage Core
 * @see Localization_Locale
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currencies\CurrencyInterface;

/**
 * Individual locale representation with information about
 * the country and currency.
 *
 * @package Localization
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Localization_Locale implements LocaleInterface
{
    public const ERROR_LOCALE_LABEL_MISSING = 39102;
    
    /**
     * @var string
     */
    private $localeName;

    /**
     * @var BaseCountry
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
    
    public function getLanguageCode() : string
    {
        return $this->languageCode;
    }

    public static function isLocaleKnown(string $localeName) : bool
    {
        return Localization::isLocaleSupported($localeName);
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
        return $this->getName() === Localization::BUILTIN_LOCALE_NAME;
    }

    public function getCountry() : BaseCountry
    {
        if(!isset($this->country)) {
            $this->country = Localization::createCountries()->getByID($this->getCountryCode());
        }
        
        return $this->country;
    }

    public function getCurrency() : CurrencyInterface
    {
        return $this->getCountry()->getCurrency();
    }
}
