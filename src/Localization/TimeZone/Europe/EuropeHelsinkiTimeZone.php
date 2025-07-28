<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryFI;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeHelsinkiTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Helsinki';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Helsinki');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Helsinki';
    }

    public function getCountryCode(): string
    {
        return CountryFI::ISO_CODE;
    }
}
