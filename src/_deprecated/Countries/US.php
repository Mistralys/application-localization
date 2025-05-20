<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Currency\CurrencyUSD;
use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryUS;
use AppLocalize\Localization\Locale\en_US;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryUS} instead.
 */
class Localization_Country_US extends BaseCountry
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
}
