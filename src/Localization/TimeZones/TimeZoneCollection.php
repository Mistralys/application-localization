<?php
/**
 * @package Localization
 * @subpackage TimeZones
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppLocalize\Localization\TimeZones\Baskets\CountryTimeZoneBasket;
use AppLocalize\Localization\TimeZones\Baskets\GlobalTimeZoneBasket;
use AppLocalize\Localization\TimeZones\Baskets\TimeZoneBasket;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Collection of time zones.
 *
 * @package Localization
 * @subpackage TimeZones
 *
 * @method TimeZoneInterface[] getAll()
 * @method TimeZoneInterface getByID(string $id)
 * @method TimeZoneInterface getDefault()
 */
class TimeZoneCollection extends BaseClassLoaderCollection
{
    private static ?TimeZoneCollection $instance = null;

    public static function getInstance(): TimeZoneCollection
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            TimeZoneInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return TimeZoneInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../TimeZone');
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }

    /**
     * Attempts to find a time zone by its locale code.
     *
     * @param string|LocaleInterface $locale Locale code or locale instance.
     * @return CountryTimeZoneInterface|NULL
     */
    public function findByLocale($locale) : ?CountryTimeZoneInterface
    {
        if($locale instanceof LocaleInterface) {
            $localeCode = $locale->getID();
        } else {
            $localeCode = $locale;
        }

        $localeCode = LocalesCollection::getInstance()->filterName($localeCode);

        foreach($this->getCountryTimeZones()->getAll() as $timeZone) {
            if($timeZone->getLocaleCode() === $localeCode) {
                return $timeZone;
            }
        }

        return null;
    }

    /**
     * @param string|CountryInterface $country Country or country ISO code.
     * @return CountryTimeZoneInterface|null
     */
    public function findByCountry($country) : ?CountryTimeZoneInterface
    {
        if ($country instanceof CountryInterface) {
            $countryCode = $country->getCode();
        } else {
            $countryCode = $country;
        }

        $countryCode = CountryCollection::getInstance()->filterCode($countryCode);

        foreach ($this->getCountryTimeZones()->getAll() as $timeZone) {
            if ($timeZone->getCountryCode() === $countryCode) {
                return $timeZone;
            }
        }

        return null;
    }

    /**
     * Gets all time zones in a basket to access them easily.
     * @return CountryTimeZoneBasket
     */
    public function getCountryTimeZones() : CountryTimeZoneBasket
    {
        $basket = CountryTimeZoneBasket::create();

        foreach ($this->getAll() as $timeZone) {
            if ($timeZone instanceof CountryTimeZoneInterface) {
                $basket->addItem($timeZone);
            }
        }

        return $basket;
    }

    /**
     * Gets all global time zones in a basket to access them easily.
     * @return GlobalTimeZoneBasket
     */
    public function getGlobalTimeZones() : GlobalTimeZoneBasket
    {
        $basket = GlobalTimeZoneBasket::create();

        foreach ($this->getAll() as $timeZone) {
            if ($timeZone instanceof GlobalTimeZoneInterface) {
                $basket->addItem($timeZone);
            }
        }

        return $basket;
    }
}
