<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryGB;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeLondonTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/London';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('London');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'London';
    }

    public function getCountryCode(): string
    {
        return CountryGB::ISO_CODE;
    }
}
