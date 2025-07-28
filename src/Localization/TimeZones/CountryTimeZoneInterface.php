<?php
/**
 * @package Localization
 * @subpackage TimeZones
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Locales\LocaleInterface;

/**
 * Interface for time zones that are associated with a specific country.
 * This interface extends the base TimeZoneInterface and adds methods
 * to retrieve the locale ID and the associated locale object.
 *
 * @package Localization
 * @subpackage TimeZones
 */
interface CountryTimeZoneInterface extends TimeZoneInterface
{
    public function getLocaleCode(): string;
    public function getLocale() : LocaleInterface;
    public function getCountryCode() : string;
    public function getCountry() : CountryInterface;
}