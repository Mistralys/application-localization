<?php
/**
 * File containing the {@link Localization_Country_MX} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_MX
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Mexico.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_MX extends Localization_Country
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
        return t('Mexico');
    }

    public function getCurrencyID()
    {
        return 'MXN';
    }
}
