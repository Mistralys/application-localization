<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization_Country_AT;
use AppLocalize\Localization_Country_CA;
use AppLocalize\Localization_Country_DE;
use AppLocalize\Localization_Country_ES;
use AppLocalize\Localization_Country_FR;
use AppLocalize\Localization_Country_IT;
use AppLocalize\Localization_Country_MX;
use AppLocalize\Localization_Country_PL;
use AppLocalize\Localization_Country_UK;
use AppLocalize\Localization_Country_US;
use AppUtils\ClassHelper;

/**
 * Canned list of known countries for easy access.
 *
 * Use {@see CountryCollection::choose()} to access this.
 *
 * @package Localization
 * @subpackage Countries
 */
class CannedCountries
{
    private CountryCollection $collection;

    public function __construct()
    {
        $this->collection = CountryCollection::getInstance();
    }

    public function de() : Localization_Country_DE
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_DE::class,
            $this->collection->getByID(Localization_Country_DE::ISO_CODE)
        );
    }

    public function us() : Localization_Country_US
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_US::class,
            $this->collection->getByID(Localization_Country_US::ISO_CODE)
        );
    }

    public function ca() : Localization_Country_CA
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_CA::class,
            $this->collection->getByID(Localization_Country_CA::ISO_CODE)
        );
    }

    public function es() : Localization_Country_ES
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_ES::class,
            $this->collection->getByID(Localization_Country_ES::ISO_CODE)
        );
    }

    public function fr() : Localization_Country_FR
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_FR::class,
            $this->collection->getByID(Localization_Country_FR::ISO_CODE)
        );
    }

    public function it() : Localization_Country_IT
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_IT::class,
            $this->collection->getByID(Localization_Country_IT::ISO_CODE)
        );
    }

    public function uk() : Localization_Country_UK
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_UK::class,
            $this->collection->getByID(Localization_Country_UK::ISO_CODE)
        );
    }

    public function mx() : Localization_Country_MX
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_MX::class,
            $this->collection->getByID(Localization_Country_MX::ISO_CODE)
        );
    }

    public function at() : Localization_Country_AT
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_AT::class,
            $this->collection->getByID(Localization_Country_AT::ISO_CODE)
        );
    }

    public function pl() : Localization_Country_PL
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Country_PL::class,
            $this->collection->getByID(Localization_Country_PL::ISO_CODE)
        );
    }
}
