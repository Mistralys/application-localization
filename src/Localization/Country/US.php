<?php
/**
 * File containing the {@link Localization_Country_US} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_US
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
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

    public function getCurrencyISO() : string
    {
        return Localization_Currency_USD::ISO_CODE;
    }
}
