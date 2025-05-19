<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Country\CountryCH;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Currency\CurrencyEUR;

/**
 * Country class with the definitions for Switzerland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @deprecated Use {@see CountryCH} instead.
 */
class Localization_Country_CH extends CountryDE
{
    public const ISO_CODE = 'ch';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getLabel() : string
    {
        return t('Switzerland');
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return "'";
    }
}
