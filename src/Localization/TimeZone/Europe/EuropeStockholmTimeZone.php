<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountrySE;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeStockholmTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Stockholm';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Stockholm');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Stockholm';
    }

    public function getCountryCode(): string
    {
        return CountrySE::ISO_CODE;
    }
}
