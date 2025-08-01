<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\es_ES;
use AppLocalize\Localization\TimeZone\Europe\EuropeMadridTimeZone;
use AppLocalize\Localization_Country_ES;
use AppLocalize\Localization\Currency\CurrencyEUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Spain.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryES extends BaseCountry
{
    public const ISO_CODE = 'es';

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
        return t('Spain');
    }

    public function getLabelInvariant(): string
    {
        return 'Spain';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return es_ES::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeMadridTimeZone::ZONE_ID;
    }
}
