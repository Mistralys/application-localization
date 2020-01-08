<?php
/**
 * File containing the {@link Localization_Country_DE} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_DE
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
class Localization_Country_DE extends Localization_Country
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
        return t('Germany');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
