<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Globals;

use AppLocalize\Localization\TimeZones\BaseGlobalTimeZone;
use function AppLocalize\t;

class GlobalUTCTimeZone extends BaseGlobalTimeZone
{
    public const ZONE_ID = 'UTC';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Coordinated Universal Time');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Coordinated Universal Time';
    }
}
