<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryBasket;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface TimeZoneInterface extends StringPrimaryRecordInterface
{
    public function getLabel(): string;
    public function getLabelInvariant(): string;

    public function getZoneLabel() : string;
    public function getZoneLabelInvariant() : string;
    public function getLocationLabel() : string;
    public function getLocationLabelInvariant() : string;

    /**
     * Gets all the known countries that use this timezone.
     * @return CountryBasket
     */
    public function getCountries() : CountryBasket;
}
