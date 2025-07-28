<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Europe;

use AppLocalize\Localization\Country\CountryIE;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use function AppLocalize\t;

class EuropeDublinTimeZone extends BaseEuropeTimeZone
{
    public const ZONE_ID = 'Europe/Dublin';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Dublin');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Dublin';
    }

    public function getCountryCode(): string
    {
        return CountryIE::ISO_CODE;
    }
}
