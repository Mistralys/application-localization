<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Locale\en_GB;
use AppLocalize\Localization\Locale\en_UK;
use AppLocalize\Localization\Currency\CurrencyGBP;
use function AppLocalize\t;

/**
 * Country class with the definitions for England.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryGB extends BaseCountry
{
    public const ISO_CODE = 'gb';
    public const ISO_ALIAS_UK = 'uk';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getAliases() : array
    {
        return array(
            self::ISO_ALIAS_UK
        );
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
        return t('Great Britain');
    }

    public function getLabelInvariant(): string
    {
        return 'Great Britain';
    }

    public function getCurrencyISO() : string
    {
        return CurrencyGBP::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return en_GB::LOCALE_NAME;
    }
}
