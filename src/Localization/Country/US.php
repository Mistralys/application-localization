<?php
/**
 * File containing the {@link Localization_Country_US} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_US
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_US extends Localization_Country
{
    public function getNumberThousandsSeparator() : string
    {
        return ',';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return '.';
    }

    public function getLabel()
    {
        return t('United States');
    }

    public function getCurrencyID()
    {
        return 'CAD';
    }
}
