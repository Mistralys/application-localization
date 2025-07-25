<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\TimeZones\BaseAsiaTimeZone;
use function AppLocalize\t;

class AsiaSingaporeTimeZone extends BaseAsiaTimeZone
{
    public const ZONE_ID = 'Asia/Singapore';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Singapore');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Singapore';
    }
}
