<?php
/**
 * File containing the {@link Localization_Country_RO} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_RO
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for Romania.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_RO extends BaseCountry
{
    public const ISO_CODE = 'ro';

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
        return t('Romania');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_RON::ISO_CODE;
    }
}
