<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\fr_FR;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\TimeZone\Europe\EuropeParisTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for France.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryFR extends BaseCountry
{
    public const ISO_CODE = 'fr';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return ' ';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel() : string
    {
        return t('France');
    }

    public function getLabelInvariant(): string
    {
        return 'France';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return fr_FR::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeParisTimeZone::ZONE_ID;
    }
}
