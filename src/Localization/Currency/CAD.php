<?php

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryInterface;

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
