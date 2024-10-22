<?php
/**
 * @package Localization
 * @subpackage Events
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Event;

use AppLocalize\Localization;
use AppLocalize\Localization_Event;

/**
 * Event class: used for the {@see Localization::EVENT_CLIENT_FOLDER_CHANGED} event.
 *
 * @package Localization
 * @subpackage Events
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClientFolderChanged extends Localization_Event
{
}
