<?php
/**
 * @package Localization
 * @subpackage Events
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Event;

use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Locales\BaseLocale;

/**
 * Event class: used for the "LocaleChanged" event. Provides
 * an easy-to-use API to work with the event details.
 * 
 * @package Localization
 * @subpackage Events
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Localization::selectLocaleByNS()
 */
class LocaleChanged extends BaseLocalizationEvent
{
    public const ERROR_NO_CURRENT_LOCALE_SPECIFIED = 91401;

    /**
    * The locale that was used before the change, if any.
    * @return BaseLocale|NULL
    */
    public function getPrevious() : ?BaseLocale
    {
        $arg = $this->getArgument(1);
        if($arg instanceof BaseLocale) {
            return $arg;
        }
        
        return null;
    }

    /**
     * The locale that is used now after the change.
     *
     * @return BaseLocale
     * @throws LocalizationException
     */
    public function getCurrent() : BaseLocale
    {
        $arg = $this->getArgument(2);

        if($arg instanceof BaseLocale) {
            return $arg;
        }

        throw new LocalizationException(
            'No current locale available in event',
            'The current locale parameter was not a locale instance.',
            self::ERROR_NO_CURRENT_LOCALE_SPECIFIED
        );
    }
    
   /**
    * The namespace in which the locale change occurred.
    * @return string
    */
    public function getNamespace() : string
    {
        return strval($this->getArgument(0));
    }

   /**
    * Whether the change occurred for an application locale.
    * @return bool
    */
    public function isAppLocale() : bool
    {
        return $this->getNamespace() === Localization::NAMESPACE_APPLICATION;
    }
    
   /**
    * Whether the change occurred for a content locale.
    * @return bool
    */
    public function isContentLocale() : bool
    {
        return $this->getNamespace() === Localization::NAMESPACE_CONTENT;
    }
}
