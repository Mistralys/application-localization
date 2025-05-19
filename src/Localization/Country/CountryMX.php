<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_MX;
use AppLocalize\Localization\Currency\CurrencyMXN;
use function AppLocalize\t;

/**
 * Country class with the definitions for Mexico.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryMX extends Localization_Country_MX
{
    public const ISO_CODE = 'mx';

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
        return t('Mexico');
    }

    public function getCurrencyISO() : string
    {
        return CurrencyMXN::ISO_CODE;
    }
}
