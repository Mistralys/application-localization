<?php
/**
 * File containing the {@link Localization_Country_CA} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_CA
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Canada.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_CA extends Localization_Country
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
        return t('Canada');
    }

    public function getCurrencyID()
    {
        return 'CAD';
    }
}
