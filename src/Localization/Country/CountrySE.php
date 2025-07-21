<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\sv_SE;
use AppLocalize\Localization\Currency\CurrencySEK;
use function AppLocalize\t;

/**
 * Country class with the definitions for Sweden.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CountrySE extends BaseCountry
{
    public const ISO_CODE = 'se';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return ' ';
    }

    public function getNumberDecimalsSeparator(): string
    {
        return ',';
    }

    public function getLabel(): string
    {
        return t('Sweden');
    }

    public function getLabelInvariant(): string
    {
        return 'Sweden';
    }

    public function getCurrencyISO(): string
    {
        return CurrencySEK::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return sv_SE::LOCALE_NAME;
    }
}
