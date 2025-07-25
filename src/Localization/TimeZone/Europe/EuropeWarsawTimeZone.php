<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeWarsawTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Warsaw';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Warsaw');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Warsaw';
    }
}
