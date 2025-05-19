<?php
/**
 * @package Localization
 * @subpackage Events
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Event;

/**
 * Interface for localization event instances.
 * A base implementation is provided by {@see BaseLocalizationEvent}.
 *
 * @package Localization
 * @subpackage Events
 */
interface LocalizationEventInterface
{
    /**
     * @param int $index
     * @return mixed|NULL
     */
    public function getArgument(int $index);
}
