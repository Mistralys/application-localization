<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use function AppLocalize\t;

abstract class BaseAsiaTimeZone extends BaseTimeZone
{
    public function getZoneLabel(): string
    {
        return t('Asia');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'Asia';
    }
}
