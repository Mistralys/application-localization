<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization\Country\CountryAT;
use AppLocalize\Localization\Country\CountryBE;
use AppLocalize\Localization\Country\CountryCA;
use AppLocalize\Localization\Country\CountryFI;
use AppLocalize\Localization\Country\CountrySG;
use AppLocalize\Localization\Country\CountryCH;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryES;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization\Country\CountryIT;
use AppLocalize\Localization\Country\CountryMX;
use AppLocalize\Localization\Country\CountryPL;
use AppLocalize\Localization\Country\CountryUK;
use AppLocalize\Localization\Country\CountryUS;
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

    public function de() : CountryDE
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryDE::class,
            $this->collection->getByID(CountryDE::ISO_CODE)
        );
    }

    public function us() : CountryUS
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryUS::class,
            $this->collection->getByID(CountryUS::ISO_CODE)
        );
    }

    public function ca() : CountryCA
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryCA::class,
            $this->collection->getByID(CountryCA::ISO_CODE)
        );
    }

    public function es() : CountryES
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryES::class,
            $this->collection->getByID(CountryES::ISO_CODE)
        );
    }

    public function fr() : CountryFR
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryFR::class,
            $this->collection->getByID(CountryFR::ISO_CODE)
        );
    }

    public function it() : CountryIT
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryIT::class,
            $this->collection->getByID(CountryIT::ISO_CODE)
        );
    }

    public function uk() : CountryUK
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryUK::class,
            $this->collection->getByID(CountryUK::ISO_CODE)
        );
    }

    public function mx() : CountryMX
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryMX::class,
            $this->collection->getByID(CountryMX::ISO_CODE)
        );
    }

    public function at() : CountryAT
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryAT::class,
            $this->collection->getByID(CountryAT::ISO_CODE)
        );
    }

    public function pl() : CountryPL
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryPL::class,
            $this->collection->getByID(CountryPL::ISO_CODE)
        );
    }

    public function fi() : CountryFI
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryFI::class,
            $this->collection->getByID(CountryFI::ISO_CODE)
        );
    }

    public function be() : CountryBE
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryBE::class,
            $this->collection->getByID(CountryBE::ISO_CODE)
        );
    }

    public function ch() : CountryCH
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryCH::class,
            $this->collection->getByID(CountryCH::ISO_CODE)
        );
    }

    public function sg() : CountrySG
    {
        return ClassHelper::requireObjectInstanceOf(
            CountrySG::class,
            $this->collection->getByID(CountrySG::ISO_CODE)
        );
    }
}
