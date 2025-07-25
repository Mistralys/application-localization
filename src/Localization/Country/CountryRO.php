<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\ro_RO;
use AppLocalize\Localization\Currency\CurrencyRON;
use AppLocalize\Localization\TimeZone\Europe\EuropeBucharestTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Romania.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryRO extends BaseCountry
{
    public const ISO_CODE = 'ro';

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
        return t('Romania');
    }

    public function getLabelInvariant(): string
    {
        return 'Romania';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyRON::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return ro_RO::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeBucharestTimeZone::ZONE_ID;
    }
}
