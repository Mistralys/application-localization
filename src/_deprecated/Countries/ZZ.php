<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Currency\CurrencyUSD;
use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryZZ;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryZZ} instead.
 */
class Localization_Country_ZZ extends BaseCountry
{
    public const ISO_CODE = 'zz';

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
        return t('Country-independent');
    }

    public function getLabelInvariant(): string
    {
        return 'Country-independent';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyUSD::ISO_CODE;
    }
}
