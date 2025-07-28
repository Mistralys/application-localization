<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryBE;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeBrusselsTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Brussels';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Brussels');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Brussels';
    }

    public function getCountryCode(): string
    {
        return CountryBE::ISO_CODE;
    }
}
