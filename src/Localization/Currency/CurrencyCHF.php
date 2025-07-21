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
 * Currency: Swiss Franc (CHF)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyCHF extends BaseCurrency
{
    public const ISO_CODE = 'CHF';

    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function getSingular() : string
    {
        return t('Swiss Franc');
    }

    public function getSingularInvariant() : string
    {
        return 'Swiss Franc';
    }

    public function isSymbolOnFront(): bool
    {
        return false;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function getSymbol() : string
    {
        return 'F';
    }

    public function getPlural() : string
    {
        return t('Swiss Francs');
    }

    public function getPluralInvariant() : string
    {
        return 'Swiss Francs';
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{amount} {symbol}';
    }
}
