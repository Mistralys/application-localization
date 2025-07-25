<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\US;

use AppLocalize\Localization\TimeZones\BaseUSTimeZone;
use function AppLocalize\t;

class USEasternTimeZone extends BaseUSTimeZone
{
    public const ZONE_ID = 'US/Eastern';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Eastern');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Eastern';
    }
}
