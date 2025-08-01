<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyCAD;
use AppLocalize\Localization\Locale\en_CA;
use AppLocalize\Localization\TimeZone\America\AmericaVancouverTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Canada.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryCA extends BaseCountry
{
    public const ISO_CODE = 'ca';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return ',';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return '.';
    }

    public function getLabel() : string
    {
        return t('Canada');
    }

    public function getLabelInvariant(): string
    {
        return 'Canada';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyCAD::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return en_CA::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return AmericaVancouverTimeZone::ZONE_ID;
    }
}
