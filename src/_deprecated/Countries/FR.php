<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization\Currency\CurrencyEUR;

/**
 * Country class with the definitions for France.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryFR} instead.
 */
class Localization_Country_FR extends BaseCountry
{
    public const ISO_CODE = 'fr';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return ' ';
    }

    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }

    public function getLabel() : string
    {
        return t('France');
    }

    public function getLabelInvariant(): string
    {
        return 'France';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }
}
