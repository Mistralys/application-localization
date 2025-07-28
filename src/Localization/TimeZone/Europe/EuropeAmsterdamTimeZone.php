<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryNL;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeAmsterdamTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Amsterdam';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Amsterdam');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Amsterdam';
    }

    public function getCountryCode(): string
    {
        return CountryNL::ISO_CODE;
    }
}
