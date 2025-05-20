<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryUK;
use AppLocalize\Localization\Currency\CurrencyGBP;
use AppLocalize\Localization\Locale\en_UK;

/**
 * Country class with the definitions for England.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryUK} instead.
 */
class Localization_Country_UK extends BaseCountry
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

    public function getLabelInvariant(): string
    {
        return 'United Kingdom';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyGBP::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return en_UK::LOCALE_NAME;
    }
}
