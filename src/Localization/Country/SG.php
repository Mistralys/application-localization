<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization_Currency_SGD;
use function AppLocalize\t;

/**
 * Country class with the definitions for Singapore.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_SG extends BaseCountry
{
    public const ISO_CODE = 'sg';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return ',';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return '.';
    }

    public function getLabel() : string
    {
        return t('Singapore');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_SGD::ISO_CODE;
    }
}
