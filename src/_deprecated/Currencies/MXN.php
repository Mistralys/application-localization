<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use AppLocalize\Localization\Currency\CurrencyMXN;

/**
 * @deprecated Use {@see CurrencyMXN}
 */
class Localization_Currency_MXN extends BaseCurrency
{
    public const ISO_CODE = 'MXN';

    public function getSingular() : string
    {
        return t('Mexican peso');
    }

    public function getSymbol() : string
    {
        return '$';
    }

    public function getPlural() : string
    {
        return t('Mexican peso');
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
        return true;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '{symbol} -{amount}';
    }
}
