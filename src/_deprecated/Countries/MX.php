<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Country\CountryMX;
use AppLocalize\Localization\Currency\CurrencyMXN;
use AppLocalize\Localization\Locale\es_MX;

/**
 * Country class with the definitions for Mexico.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryMX} instead.
 */
class Localization_Country_MX extends BaseCountry
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

    public function getLabelInvariant(): string
    {
        return 'Mexico';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyMXN::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return es_MX::LOCALE_NAME;
    }
}
