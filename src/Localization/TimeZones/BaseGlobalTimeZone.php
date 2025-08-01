<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryBasket;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Locales\LocaleBasket;
use function AppLocalize\t;

abstract class BaseGlobalTimeZone extends BaseTimeZone implements GlobalTimeZoneInterface
{
    public function getZoneLabel(): string
    {
        return t('Global');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'Global';
    }

    private ?CountryBasket $countries = null;

    public function getCountries() : CountryBasket
    {
        if(isset($this->countries)) {
            return $this->countries;
        }

        $this->countries = CountryBasket::create($this->resolveCountries());

        return $this->countries;
    }

    protected function resolveCountries() : array
    {
        return CountryCollection::getInstance()->getAll();
    }

    public function getLocales(): LocaleBasket
    {
        $basket = LocaleBasket::create();

        foreach($this->getCountries()->getAll() as $country) {
            $basket->addItem($country->getMainLocale());
        }

        return $basket;
    }
}
