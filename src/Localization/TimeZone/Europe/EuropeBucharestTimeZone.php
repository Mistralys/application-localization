<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryRO;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeBucharestTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Bucharest';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Bucharest');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Bucharest';
    }

    public function getCountryCode(): string
    {
        return CountryRO::ISO_CODE;
    }
}
