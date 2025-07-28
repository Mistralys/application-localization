<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use function AppLocalize\t;

abstract class BaseAmericaTimeZone extends BaseCountryTimeZone
{
    public function getZoneLabel(): string
    {
        return t('America');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'America';
    }
}
