<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Country\CountryUS;
use function AppLocalize\t;

abstract class BaseUSTimeZone extends BaseAmericaTimeZone
{
    public function getZoneLabel(): string
    {
        return t('United States');
    }

    public function getZoneLabelInvariant(): string
    {
        return 'United States';
    }

    public function getCountryCode(): string
    {
        return CountryUS::ISO_CODE;
    }
}
