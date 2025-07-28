<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppUtils\Baskets\GenericStringPrimaryBasket;

/**
 * Country basket utility class: A collection of countries with
 * methods to freely add and remove them.
 *
 * @package Localization
 * @subpackage Countries
 *
 * @method CountryInterface[] getAll()
 * @method CountryInterface getByID(string $id)
 * @method static CountryBasket create(...$initialRecords)
 */
class CountryBasket extends GenericStringPrimaryBasket
{
    public function getAllowedItemClasses(): array
    {
        return array(
            CountryInterface::class
        );
    }
}
