<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryRO;
use AppLocalize\Localization\Currency\CurrencyRON;
use AppLocalize\Localization\Locale\ro_RO;

/**
 * Country class with the definitions for Romania.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryRO} instead.
 */
class Localization_Country_RO extends BaseCountry
{
    public const ISO_CODE = 'ro';

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
        return t('Romania');
    }

    public function getLabelInvariant(): string
    {
        return 'Romania';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyRON::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return ro_RO::LOCALE_NAME;
    }
}
