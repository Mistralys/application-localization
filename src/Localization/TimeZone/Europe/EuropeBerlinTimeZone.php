<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeBerlinTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Berlin';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Berlin');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Berlin';
    }

    public function getCountryCode(): string
    {
        return CountryDE::ISO_CODE;
    }
}
