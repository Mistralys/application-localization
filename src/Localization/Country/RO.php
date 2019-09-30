<?php
/**
 * File containing the {@link Localization_Country_RO} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_RO
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Romania.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_RO extends Localization_Country
{
    public function getNumberThousandsSeparator()
    {
        return '.';
    }

    public function getNumberDecimalsSeparator()
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
