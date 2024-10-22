<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for Poland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_PL extends BaseCountry
{
    public const ISO_CODE = 'pl';

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
        return t('Poland');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_PLN::ISO_CODE;
    }
}
