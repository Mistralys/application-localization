<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_AT;
use AppLocalize\Localization\Currency\CurrencyEUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Austria.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryAT extends Localization_Country_AT
{
    public const ISO_CODE = 'at';

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
        return t('Austria');
    }

    public function getLabelInvariant(): string
    {
        return 'Austria';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }
}
