<?php
/**
 * File containing the {@link Localization_Country_CA} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_CA
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for Canada.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_CA extends BaseCountry
{
    public const ISO_CODE = 'ca';

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
        return t('Canada');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_CAD::ISO_CODE;
    }
}
