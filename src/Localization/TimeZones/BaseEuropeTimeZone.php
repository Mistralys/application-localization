<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use function AppLocalize\t;

abstract class BaseEuropeTimeZone extends BaseCountryTimeZone
{
    public function getZoneLabel(): string
    {
        return t('Europe');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'Europe';
    }
}
