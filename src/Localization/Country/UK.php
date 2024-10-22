<?php
/**
 * File containing the {@link Localization_Country_UK} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_UK
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for England.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_UK extends BaseCountry
{
    public const ISO_CODE = 'uk';

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
        return t('United Kingdom');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_GBP::ISO_CODE;
    }
}
