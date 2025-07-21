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
use function AppLocalize\tex;

/**
 * Currency: Canadian Dollar (CAD)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyCAD extends BaseCurrency
{
    public const ISO_CODE = 'CAD';

    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function getSingular() : string
    {
        return tex('Canadian Dollar', 'Singular form of the Canadian currency');
    }

    public function getSingularInvariant() : string
    {
        return 'Canadian Dollar';
    }

    public function getSymbol() : string
    {
        return '$';
    }

    public function getPlural() : string
    {
        return tex('Canadian Dollars', 'Plural form of the Canadian currency');
    }

    public function getPluralInvariant() : string
    {
        return 'Canadian Dollars';
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function isSymbolOnFront() : bool
    {
        return true;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '{symbol} -{amount}';
    }
}
