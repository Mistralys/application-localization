<?php
/**
 * @package Localization
 * @subpackage Events
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Event;

use AppLocalize\Localization;

/**
 * Event class: used for the {@see Localization::EVENT_CACHE_KEY_CHANGED} event.
 *
 * @package Localization
 * @subpackage Events
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CacheKeyChanged extends BaseLocalizationEvent
{
}
