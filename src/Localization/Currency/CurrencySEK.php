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
 * Currency: Swedish Krona (SEK)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencySEK extends BaseCurrency
{
    public const ISO_CODE = 'SEK';

    public function getSingular() : string
    {
        return tex('Krona', 'Singular form of the Swedish currency');
    }

    public function getSymbol() : string
    {
        return 'kr';
    }

    public function getPlural() : string
    {
        return tex('Kronor', 'Plural form of the Swedish currency');
    }

    public function isSymbolOnFront() : bool
    {
        return false;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{amount} {symbol}';
    }

    public function getISO() : string
    {
        return self::ISO_CODE;
    }
}
