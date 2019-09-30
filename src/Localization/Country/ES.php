<?php
/**
 * File containing the {@link Localization_Country_ES} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_ES
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Spain.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_ES extends Localization_Country
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
        return t('Spain');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
