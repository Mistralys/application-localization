<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Currency\CurrencyCAD;
use AppLocalize\Localization\Countries\CountryInterface;

/**
 * @deprecated Use {@see CurrencyCAD}
 */
class Localization_Currency_CAD extends Localization_Currency_USD
{
    public const ISO_CODE = 'CAD';

    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '{symbol} -{amount}';
    }
}
