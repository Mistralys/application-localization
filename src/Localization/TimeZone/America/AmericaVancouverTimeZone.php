<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\America;

use AppLocalize\Localization\TimeZones\BaseAmericaTimeZone;
use function AppLocalize\t;

class AmericaVancouverTimeZone extends BaseAmericaTimeZone
{
    public const ZONE_ID = 'America/Vancouver';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Vancouver');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Vancouver';
    }
}
