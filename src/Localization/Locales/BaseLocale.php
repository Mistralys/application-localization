<?php
/**
 * @package Localization
 * @subpackage Core
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization;
use AppLocalize\Localization\Currencies\CurrencyInterface;

/**
 * Individual locale representation with information about
 * the country and currency.
 *
 * @package Localization
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseLocale implements LocaleInterface
{
    protected CountryInterface $country;
    protected string $countryCode;
    protected string $languageCode;

    public function __construct()
    {
        $tokens = explode('_', $this->getName());

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
    * @see BaseLocale::getLanguageCode()
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

    public function getCountry() : CountryInterface
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
