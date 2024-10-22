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
use AppLocalize\Localization_Country_RO;
use AppLocalize\Localization_Country_UK;
use AppLocalize\Localization_Country_US;
use AppLocalize\Localization_Country_ZZ;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\Collections\CollectionException;

/**
 * Country collection that gives access to all available
 * countries and their data.
 *
 * @package Localization
 * @subpackage Countries
 *
 * @method BaseCountry getByID(string $id)
 * @method BaseCountry getDefault()
 * @method BaseCountry[] getAll()
 */
class CountryCollection extends BaseStringPrimaryCollection
{
    private static ?CountryCollection $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): CountryCollection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $iso Two-letter ISO code of the country, e.g. `de`. Case-insensitive.
     * @return CountryInterface
     * @throws CollectionException
     */
    public function getByISO(string $iso) : CountryInterface
    {
        return $this->getByID(strtolower($iso));
    }

    /**
     * Checks whether the target ISO code is known.
     * @param string $iso Two-letter ISO code of the country, e.g. `de`. Case-insensitive.
     * @return bool
     */
    public function isoExists(string $iso) : bool
    {
        return $this->idExists(strtolower($iso));
    }

    public function getDefaultID(): string
    {
        return Localization_Country_US::ISO_CODE;
    }

    protected function registerItems(): void
    {
        $this->registerItem(new Localization_Country_AT());
        $this->registerItem(new Localization_Country_CA());
        $this->registerItem(new Localization_Country_DE());
        $this->registerItem(new Localization_Country_ES());
        $this->registerItem(new Localization_Country_FR());
        $this->registerItem(new Localization_Country_IT());
        $this->registerItem(new Localization_Country_MX());
        $this->registerItem(new Localization_Country_PL());
        $this->registerItem(new Localization_Country_RO());
        $this->registerItem(new Localization_Country_UK());
        $this->registerItem(new Localization_Country_US());
        $this->registerItem(new Localization_Country_ZZ());
    }

    private ?CannedCountries $canned = null;

    public function choose() : CannedCountries
    {
        if(!isset($this->canned)) {
            $this->canned = new CannedCountries();
        }

        return $this->canned;
    }
}
