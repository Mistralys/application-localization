<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\Locale\nl_NL;
use AppLocalize\Localization\TimeZone\Europe\EuropeAmsterdamTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for the Netherlands.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CountryNL extends BaseCountry
{
    public const ISO_CODE = 'nl';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return '.';
    }

    public function getNumberDecimalsSeparator(): string
    {
        return ',';
    }

    public function getLabel(): string
    {
        return t('Netherlands');
    }

    public function getLabelInvariant(): string
    {
        return 'Netherlands';
    }

    public function getCurrencyISO(): string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return nl_NL::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeAmsterdamTimeZone::ZONE_ID;
    }
}
