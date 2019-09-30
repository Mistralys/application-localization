<?php
/**
 * File containing the {@link Localization_Country_ZZ} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_ZZ
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Germany.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_ZZ extends Localization_Country
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
        return t('Country-independent');
    }
    
    public function getCurrencyID()
    {
        return 'EUR';
    }
}
