<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryES;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeMadridTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Madrid';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Madrid');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Madrid';
    }

    public function getCountryCode(): string
    {
        return CountryES::ISO_CODE;
    }
}
