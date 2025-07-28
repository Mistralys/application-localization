<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryCH;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeZurichTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Zurich';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Zurich');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Zurich';
    }

    public function getCountryCode(): string
    {
        return CountryCH::ISO_CODE;
    }
}
