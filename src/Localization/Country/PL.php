<?php
/**
 * File containing the {@link Localization_Country_PL} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_PL
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Poland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_PL extends Localization_Country
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
        return t('Poland');
    }

    public function getCurrencyID()
    {
        return 'PLN';
    }
}
