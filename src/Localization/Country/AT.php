<?php
/**
 * File containing the {@link Localization_Country_AT} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_AT
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Austria.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_AT extends Localization_Country
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
        return t('Austria');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
