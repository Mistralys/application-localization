<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use AppLocalize\Localization\Currency\CurrencyPLN;

/**
 * @deprecated Use {@see CurrencyPLN}
 */
class Localization_Currency_PLN extends BaseCurrency
{
    public const ISO_CODE = 'PLN';

    public function getSingular() : string
    {
        return t('złoty');
    }

    public function getSymbol() : string
    {
        return 'zł';
    }

    public function getPlural() : string
    {
        return t('złotys');
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
