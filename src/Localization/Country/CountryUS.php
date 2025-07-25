<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\en_US;
use AppLocalize\Localization\Currency\CurrencyUSD;
use AppLocalize\Localization\TimeZone\US\USEasternTimeZone;
use function AppLocalize\t;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryUS extends BaseCountry
{
    public const ISO_CODE = 'us';

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
        return t('United States');
    }

    public function getLabelInvariant(): string
    {
        return 'United States';
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
        return USEasternTimeZone::ZONE_ID;
    }
}
