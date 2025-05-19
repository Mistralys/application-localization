<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_CA;
use AppLocalize\Localization\Currency\CurrencyCAD;
use function AppLocalize\t;

/**
 * Country class with the definitions for Canada.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryCA extends Localization_Country_CA
{
    public const ISO_CODE = 'ca';

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
        return t('Canada');
    }

    public function getCurrencyISO() : string
    {
        return CurrencyCAD::ISO_CODE;
    }
}
