<?php
/**
 * File containing the {@link Localization_Country_PL} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_PL
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Poland.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_PL extends Localization_Country
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
        return t('Poland');
    }

    public function getCurrencyID()
    {
        return 'PLN';
    }
}
