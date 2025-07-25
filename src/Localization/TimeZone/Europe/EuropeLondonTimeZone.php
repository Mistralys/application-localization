<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

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
        return t('Paris');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Paris';
    }
}
