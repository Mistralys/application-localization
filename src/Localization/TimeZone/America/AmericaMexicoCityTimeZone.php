<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\America;

use AppLocalize\Localization\Country\CountryMX;
use AppLocalize\Localization\TimeZones\BaseAmericaTimeZone;
use function AppLocalize\t;

class AmericaMexicoCityTimeZone extends BaseAmericaTimeZone
{
    public const ZONE_ID = 'America/Mexico_City';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Mexico City');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Mexico City';
    }

    public function getCountryCode(): string
    {
        return CountryMX::ISO_CODE;
    }
}
