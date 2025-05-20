<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Locale\en_US;
use AppLocalize\Localization_Country_US;
use AppLocalize\Localization\Currency\CurrencyUSD;
use function AppLocalize\t;

/**
 * Country class with the definitions for Germany.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryUS extends Localization_Country_US
{
    public const ISO_CODE = 'us';

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
        return t('United States');
    }

    public function getLabelInvariant(): string
    {
        return 'United States';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyUSD::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return en_US::LOCALE_NAME;
    }
}
