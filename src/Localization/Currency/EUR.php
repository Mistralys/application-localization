<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization\Currencies\BaseCurrency;

class Localization_Currency_EUR extends BaseCurrency
{
    public const ISO_CODE = 'EUR';

    public function getSingular() : string
    {
        return t('Euro');
    }

    public function getSymbol() : string
    {
        return '€';
    }

    public function getPlural() : string
    {
        return t('Euros');
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
        if($country instanceof CountryFR) {
            return '- {amount} {symbol}';
        }

        return '-{amount} {symbol}';
    }

    public function getISO() : string
    {
        return self::ISO_CODE;
    }
}
