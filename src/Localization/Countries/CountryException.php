<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization\LocalizationException;

/**
 * Exception and exception codes for the country localization module.
 *
 * @package Localization
 * @subpackage Countries
 */
class CountryException extends LocalizationException
{
    public const ERROR_CANNOT_PARSE_CURRENCY_NUMBER = 177701;
    public const ERROR_INVALID_BASKET_COUNTRY_SELECTION = 177702;
}
