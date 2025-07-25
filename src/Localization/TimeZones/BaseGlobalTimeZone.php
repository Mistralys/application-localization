<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use function AppLocalize\t;

abstract class BaseGlobalTimeZone extends BaseTimeZone
{
    public function getZoneLabel(): string
    {
        return t('Global');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'Global';
    }
}
