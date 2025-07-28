<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZone\Globals;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\TimeZones\BaseEuropeTimeZone;
use AppLocalize\Localization\TimeZones\BaseGlobalTimeZone;
use AppLocalize\Localization\TimeZones\TimeZoneCollection;
use function AppLocalize\t;

class GlobalCETTimeZone extends BaseGlobalTimeZone
{
    public const ZONE_ID = 'CET';

    public function getID(): string
    {
        return self::ZONE_ID;
    }

    public function getLocationLabel(): string
    {
        return t('Central European Time');
    }

    public function getLocationLabelInvariant(): string
    {
        return 'Central European Time';
    }

    protected function resolveCountries(): array
    {
        $result = array();

        foreach(TimeZoneCollection::getInstance()->getAll() as $timeZone) {
            if ($timeZone instanceof BaseEuropeTimeZone) {
                $result[] = $timeZone->getCountry();
            }
        }

        return $result;
    }
}
