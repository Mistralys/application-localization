<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeRomeTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Rome';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Rome');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Rome';
    }
}
