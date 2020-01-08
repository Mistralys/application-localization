<?php
/**
 * File containing the {@link Localization_Country_UK} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_UK
 */

namespace AppLocalize;

/**
 * Country class with the definitions for England.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
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
