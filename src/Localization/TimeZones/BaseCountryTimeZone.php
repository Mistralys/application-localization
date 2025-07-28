<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Locales\LocaleInterface;

abstract class BaseCountryTimeZone extends BaseTimeZone implements CountryTimeZoneInterface
{
    public function getLocale() : LocaleInterface
    {
        return $this->getCountry()->getMainLocale();
    }

    public function getLocaleCode(): string
    {
        return $this->getLocale()->getID();
    }

    private ?CountryInterface $country = null;

    public function getCountry() : CountryInterface
    {
        if(!isset($this->country)) {
            $this->country = CountryCollection::getInstance()->getByISO($this->getCountryCode());
        }

        return $this->country;
    }
}
