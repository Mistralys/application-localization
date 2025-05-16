<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryAT;

/**
 * Country class with the definitions for Austria.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see \AppLocalize\Localization\Country\CountryAT} instead.
 */
class Localization_Country_AT extends BaseCountry
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

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }
}
