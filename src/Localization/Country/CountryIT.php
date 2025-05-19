<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_IT;
use AppLocalize\Localization\Currency\CurrencyEUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Italy.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryIT extends Localization_Country_IT
{
    public const ISO_CODE = 'it';

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
        return t('Italy');
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }
}
