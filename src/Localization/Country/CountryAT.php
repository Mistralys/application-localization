<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\Locale\de_AT;
use AppLocalize\Localization\TimeZone\Europe\EuropeViennaTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Austria.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryAT extends BaseCountry
{
    public const ISO_CODE = 'at';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return '.';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel() : string
    {
        return t('Austria');
    }

    public function getLabelInvariant(): string
    {
        return 'Austria';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return de_AT::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeViennaTimeZone::ZONE_ID;
    }
}
