<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones\Baskets;

use AppLocalize\Localization\TimeZones\CountryTimeZoneInterface;
use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Basket-like collection used to store a freeform selection of country-specific time zones.
 *
 * @package Localization
 * @subpackage Locales
 *
 * @method CountryTimeZoneInterface[] getAll()
 * @method CountryTimeZoneInterface getByID(string $id)
 * @method static CountryTimeZoneBasket create(...$initialRecords)
 */
class CountryTimeZoneBasket extends GenericStringPrimaryBasket
{
    public function getAllowedItemClasses(): array
    {
        return array(
            CountryTimeZoneInterface::class
        );
    }
}