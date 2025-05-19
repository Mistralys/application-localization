<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use AppLocalize\Localization\Currency\CurrencyCHF;

/**
 * @deprecated Use {@see CurrencyCHF}
 */
class Localization_Currency_CHF extends BaseCurrency
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

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{amount} {symbol}';
    }
}
