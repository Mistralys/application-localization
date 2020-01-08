<?php
/**
 * File containing the {@link Localization_Country_FR} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_FR
 */

namespace AppLocalize;

/**
 * Country class with the definitions for France.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_FR extends Localization_Country
{
    public function getNumberThousandsSeparator() : string
    {
        return '.';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel()
    {
        return t('France');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
