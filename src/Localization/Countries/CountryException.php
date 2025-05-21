<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization\LocalizationException;

class CountryException extends LocalizationException
{
    public const ERROR_CANNOT_PARSE_CURRENCY_NUMBER = 177701;
}
