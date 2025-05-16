<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * Country class with the definitions for Spain.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryES extends Localization_Country_ES
{
    public const ISO_CODE = 'es';

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
        return t('Spain');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }
}
