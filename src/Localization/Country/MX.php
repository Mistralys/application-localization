<?php
/**
 * File containing the {@link Localization_Country_MX} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_MX
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Mexico.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_MX extends Localization_Country
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
        return t('Mexico');
    }

    public function getCurrencyID()
    {
        return 'MXN';
    }
}
