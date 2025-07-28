<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryAT;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeViennaTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Vienna';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Vienna');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Vienna';
    }

    public function getCountryCode(): string
    {
        return CountryAT::ISO_CODE;
    }
}
