<?php
/**
 * File containing the {@link Localization_Country_IT} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country_IT
 */

namespace AppLocalize;

/**
 * Country class with the definitions for Italy.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Country_IT extends Localization_Country
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
        return t('Italy');
    }

    public function getCurrencyID()
    {
        return 'EUR';
    }
}
