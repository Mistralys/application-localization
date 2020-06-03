<?php
/**
 * File containing the {@link Localization_Country_UK} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country_UK
 */

namespace AppLocalize;

/**
 * Country class with the definitions for England.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_UK extends Localization_Country
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
        return t('United Kingdom');
    }

    public function getCurrencyID()
    {
        return 'GBP';
    }
}
