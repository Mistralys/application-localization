<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Currency\CurrencyCAD;
use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryCA;

/**
 * Country class with the definitions for Canada.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryCA} instead.
 */
class Localization_Country_CA extends BaseCountry
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

    public function getLabelInvariant(): string
    {
        return 'Canada';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyCAD::ISO_CODE;
    }
}
