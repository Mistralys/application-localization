<?php

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

abstract class BaseTimeZone implements TimeZoneInterface
{
    public function getLabel() : string
    {
        return $this->getZoneLabel().'/'.$this->getLocationLabel();
    }

    public function getLabelInvariant() : string
    {
        return $this->getZoneLabelInvariant().'/'.$this->getLocationLabelInvariant();
    }
}