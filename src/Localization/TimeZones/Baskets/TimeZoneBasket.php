<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones\Baskets;

use AppLocalize\Localization\TimeZones\TimeZoneInterface;
use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Basket-like collection used to store a freeform selection of time zones.
 *
 * @package Localization
 * @subpackage Locales
 *
 * @method TimeZoneInterface[] getAll()
 * @method TimeZoneInterface getByID(string $id)
 * @method static TimeZoneBasket create(...$initialRecords)
 */
class TimeZoneBasket extends GenericStringPrimaryBasket
{
    public function getAllowedItemClasses(): array
    {
        return array(
            TimeZoneInterface::class
        );
    }
}