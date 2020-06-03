<?php
/**
 * File containing the {@link Localization_Country_AT} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_AT
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Austria.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_AT extends Localization_Country
{
    public function getNumberThousandsSeparator() : string
    {
        return '.';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel()
    {
        return t('Austria');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
