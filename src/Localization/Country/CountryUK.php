<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_UK;
use AppLocalize\Localization_Currency_GBP;
use function AppLocalize\t;

/**
 * Country class with the definitions for England.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryUK extends Localization_Country_UK
{
    public const ISO_CODE = 'uk';

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
        return t('United Kingdom');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_GBP::ISO_CODE;
    }
}
