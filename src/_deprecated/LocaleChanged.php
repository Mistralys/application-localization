<?php
/**
 * @package Localization
 * @subpackage Events
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Event\LocaleChanged;

/**
 * Event class: used for the "LocaleChanged" event. Provides
 * an easy-to-use API to work with the event details.
 *
 * @package Localization
 * @subpackage Events
 *
 * @see Localization::selectLocaleByNS()
 * @deprecated The new event class is {@see LocaleChanged}.
 */
class Localization_Event_LocaleChanged extends LocaleChanged
{

}
