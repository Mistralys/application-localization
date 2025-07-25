<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryBasket;
use AppLocalize\Localization\Countries\CountryCollection;

abstract class BaseTimeZone implements TimeZoneInterface
{
    public function getLabel() : string
    {
        return $this->getZoneLabel().'/'.$this->getLocationLabel();
    }

    public function getLabelInvariant() : string
    {
        return $this->getZoneLabelInvariant().'/'.$this->getLocationLabelInvariant();
    }

    private ?CountryBasket $countries = null;

    public function getCountries() : CountryBasket
    {
        if(isset($this->countries)) {
            return $this->countries;
        }

        $this->countries = CountryBasket::create();

        $zoneID = $this->getID();
        foreach(CountryCollection::getInstance()->getAll() as $country) {
            if($country->getTimeZoneID() === $zoneID) {
                $this->countries->addItem($country);
            }
        }

        return $this->countries;
    }
}