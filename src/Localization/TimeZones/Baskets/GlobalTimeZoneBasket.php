<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones\Baskets;

use AppLocalize\Localization\TimeZones\GlobalTimeZoneInterface;
use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Basket-like collection used to store a freeform selection of global time zones.
 *
 * @package Localization
 * @subpackage Locales
 *
 * @method GlobalTimeZoneInterface[] getAll()
 * @method GlobalTimeZoneInterface getByID(string $id)
 * @method static GlobalTimeZoneBasket create(...$initialRecords)
 */
class GlobalTimeZoneBasket extends GenericStringPrimaryBasket
{
    public function getAllowedItemClasses(): array
    {
        return array(
            GlobalTimeZoneInterface::class
        );
    }
}
