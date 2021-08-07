<?php
/**
 * File containing the {@link Localization_Event_LocaleChanged} class.
 * @package Localization
 * @subpackage Events
 * @see Localization_Event_LocaleChanged
 */

declare(strict_types=1);

namespace AppLocalize;

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
class Localization_Event_LocaleChanged extends Localization_Event
{
    const ERROR_NO_CURRENT_LOCALE_SPECIFIED = 91401;

    /**
    * The locale that was used before the change, if any.
    * @return Localization_Locale|NULL
    */
    public function getPrevious() : ?Localization_Locale
    {
        $arg = $this->getArgument(1);
        if($arg instanceof Localization_Locale) {
            return $arg;
        }
        
        return null;
    }

    /**
     * The locale that is used now after the change.
     *
     * @return Localization_Locale
     * @throws Localization_Exception
     */
    public function getCurrent() : Localization_Locale
    {
        $arg = $this->getArgument(2);

        if($arg instanceof Localization_Locale) {
            return $arg;
        }

        throw new Localization_Exception(
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
