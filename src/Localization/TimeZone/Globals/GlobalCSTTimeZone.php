<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Globals;

use AppLocalize\Localization\TimeZones\BaseGlobalTimeZone;
use function AppLocalize\t;

class GlobalCSTTimeZone extends BaseGlobalTimeZone
{
    public const ZONE_ID = 'CST';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Central Standard Time');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Central Standard Time';
    }
}
