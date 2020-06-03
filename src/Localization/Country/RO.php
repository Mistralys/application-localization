<?php
/**
 * File containing the {@link Localization_Country_RO} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_RO
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Romania.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_RO extends Localization_Country
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
        return t('Romania');
    }

    public function getCurrencyID()
    {
        return 'RON';
    }
}
