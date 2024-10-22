<?php
/**
 * File containing the {@link Localization_Country_IT} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_IT
 */

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;

/**
 * Country class with the definitions for Italy.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_IT extends BaseCountry
{
    public const ISO_CODE = 'it';

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
        return t('Italy');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }
}
