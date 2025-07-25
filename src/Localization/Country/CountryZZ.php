<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyUSD;
use AppLocalize\Localization\Locale\en_US;
use AppLocalize\Localization\TimeZone\Globals\GlobalUTCTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Worldwide (Country-Independent).
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryZZ extends BaseCountry
{
    public const ISO_CODE = 'zz';

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
        return t('Country-independent');
    }

    public function getLabelInvariant(): string
    {
        return 'Country-independent';
    }
    
    public function getCurrencyISO() : string
    {
        return CurrencyUSD::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return en_US::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return GlobalUTCTimeZone::ZONE_ID;
    }
}
