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
 * Currency: Zloty (PLN)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyPLN extends BaseCurrency
{
    public const ISO_CODE = 'PLN';

    public function getSingular() : string
    {
        return t('złoty');
    }

    public function getSingularInvariant() : string
    {
        return 'złoty';
    }

    public function getSymbol() : string
    {
        return 'zł';
    }

    public function getPlural() : string
    {
        return t('złotys');
    }

    public function getPluralInvariant() : string
    {
        return 'złotys';
    }
    
    public function getISO() : string
    {
        return self::ISO_CODE;
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
}
