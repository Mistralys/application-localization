<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Switzerland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_CH extends Localization_Country_DE
{
    public const ISO_CODE = 'ch';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getLabel() : string
    {
        return t('Switzerland');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return "'";
    }
}
