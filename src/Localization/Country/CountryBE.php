<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\Locale\fr_BE;
use AppLocalize\Localization\TimeZone\Europe\EuropeBrusselsTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Belgium.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryBE extends BaseCountry
{
    public const ISO_CODE = 'be';

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
        return t('Belgium');
    }

    public function getLabelInvariant(): string
    {
        return 'Belgium';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return fr_BE::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeBrusselsTimeZone::ZONE_ID;
    }
}
