<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\de_CH;
use AppLocalize\Localization\Currency\CurrencyEUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Switzerland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryCH extends BaseCountry
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

    public function getLabelInvariant(): string
    {
        return 'Switzerland';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyEUR::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return "'";
    }

    public function getNumberDecimalsSeparator(): string
    {
        return ".";
    }

    public function getMainLocaleCode(): string
    {
        return de_CH::LOCALE_NAME;
    }
}
