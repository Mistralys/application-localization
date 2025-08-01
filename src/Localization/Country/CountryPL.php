<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\pl_PL;
use AppLocalize\Localization\Currency\CurrencyPLN;
use AppLocalize\Localization\TimeZone\Europe\EuropeWarsawTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Poland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryPL extends BaseCountry
{
    public const ISO_CODE = 'pl';

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
        return t('Poland');
    }

    public function getLabelInvariant(): string
    {
        return 'Poland';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyPLN::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return pl_PL::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeWarsawTimeZone::ZONE_ID;
    }
}
