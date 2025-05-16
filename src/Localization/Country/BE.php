<?php
/**
 * @package Localization
 * @subpackage Countries
 */

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization_Currency_EUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Belgium.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class Localization_Country_BE extends BaseCountry
{
    public const ISO_CODE = 'be';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return '.';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel() : string
    {
        return t('Belgium');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }
}
