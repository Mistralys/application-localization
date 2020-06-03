<?php
/**
 * File containing the {@link Localization_Country_ES} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_ES
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Spain.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_ES extends Localization_Country
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
        return t('Spain');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
