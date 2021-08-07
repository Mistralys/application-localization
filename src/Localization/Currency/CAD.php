<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Currency_CAD extends Localization_Currency_USD
{
    public function getISO() : string
    {
        return 'CAD';
    }
}