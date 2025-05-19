<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use AppLocalize\Localization\Currency\CurrencyGBP;

/**
 * @deprecated Use {@see CurrencyGBP}
 */
class Localization_Currency_GBP extends BaseCurrency
{
    public const ISO_CODE = 'GBP';

    public function getSingular() : string
    {
        return t('Pound');
    }

    public function getSymbol() : string
    {
        return '£';
    }

    public function getPlural() : string
    {
        return t('Pounds');
    }

    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function isSymbolOnFront() : bool
    {
        return true;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{symbol}{amount}';
    }
}
