<?php
/**
 * @package Localization
 * @subpackage TimeZones
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryBasket;
use AppLocalize\Localization\Locales\LocaleBasket;

/**
 * Interface for global time zones that are not specific to
 * a country, like the UTC time zone.
 *
 * @package Localization
 * @subpackage TimeZones
 */
interface GlobalTimeZoneInterface extends TimeZoneInterface
{
    /**
     * Gets all the known countries that use this timezone.
     * @return CountryBasket
     */
    public function getCountries() : CountryBasket;

    /**
     * Gets all the known locales that use this timezone.
     * @return LocaleBasket
     */
    public function getLocales() : LocaleBasket;
}