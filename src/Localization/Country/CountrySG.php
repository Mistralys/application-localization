<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Currency\CurrencySGD;
use function AppLocalize\t;

/**
 * Country class with the definitions for Singapore.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountrySG extends BaseCountry
{
    public const ISO_CODE = 'sg';

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
        return t('Singapore');
    }

    public function getLabelInvariant(): string
    {
        return 'Singapore';
    }

    public function getCurrencyISO() : string
    {
        return CurrencySGD::ISO_CODE;
    }

    public function getMainLocaleCode(): string
    {
        return 'en_SG';
    }
}
