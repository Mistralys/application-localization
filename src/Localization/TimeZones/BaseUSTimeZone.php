<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use function AppLocalize\t;

abstract class BaseUSTimeZone extends BaseTimeZone
{
    public function getZoneLabel(): string
    {
        return t('United States');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'United States';
    }
}
