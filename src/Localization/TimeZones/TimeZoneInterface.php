<?php
/**
 * @package Localization
 * @subpackage TimeZones
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppLocalize\Localization\Countries\CountryBasket;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for all time zones. This includes global time zones,
 * as well as country-specific time zones.
 *
 * > NOTE: Country-specific time zones implement the interface
 * > {@see CountryTimeZoneInterface}.
 *
 * @package Localization
 * @subpackage TimeZones
 * @see CountryTimeZoneInterface
 */
interface TimeZoneInterface extends StringPrimaryRecordInterface
{
    public function getLabel(): string;
    public function getLabelInvariant(): string;

    public function getZoneLabel() : string;
    public function getZoneLabelInvariant() : string;
    public function getLocationLabel() : string;
    public function getLocationLabelInvariant() : string;
}
