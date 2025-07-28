<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Basket-like collection used to store a freeform selection of locales.
 *
 * @package Localization
 * @subpackage Locales
 *
 * @method LocaleInterface[] getAll()
 * @method LocaleInterface getByID(string $id)
 * @method static LocaleBasket create(...$initialRecords)
 */
class LocaleBasket extends GenericStringPrimaryBasket
{
    public function getAllowedItemClasses(): array
    {
        return array(
            LocaleInterface::class
        );
    }
}