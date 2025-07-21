<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currency;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use function AppLocalize\t;

/**
 * Currency: Dollar (SGD)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencySGD extends BaseCurrency
{
    public const ISO_CODE = 'SGD';

    public function getSingular() : string
    {
        return t('Singapore Dollar');
    }

    public function getSingularInvariant() : string
    {
        return 'Singapore Dollar';
    }

    public function getSymbol() : string
    {
        return 'S$';
    }

    public function getPlural() : string
    {
        return t('Singapore Dollars');
    }

    public function getPluralInvariant() : string
    {
        return 'Singapore Dollars';
    }

    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function isSymbolOnFront() : bool
    {
        return true;
    }

    public function getStructuralTemplate(?CountryInterface $country=null) : string
    {
        return '-{symbol}{amount}';
    }
}