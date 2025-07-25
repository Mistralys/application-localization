<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\Locale\fi_FI;
use AppLocalize\Localization\TimeZone\Europe\EuropeHelsinkiTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Finland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryFI extends BaseCountry
{
    public const ISO_CODE = 'fi';

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
        return t('Finland');
    }

    public function getLabelInvariant(): string
    {
        return 'Finland';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return fi_FI::LOCALE_NAME;
    }

    public function getTimeZoneID(): string
    {
        return EuropeHelsinkiTimeZone::ZONE_ID;
    }
}
