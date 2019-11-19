<?php
/**
 * File containing the {@link Localization} class.
 * @package Application
 * @subpackage Localization
 * @see Localization
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * Localization handling collection for both the
 * application itself as well as its user contents.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Localization
{
    const ERROR_UNKNOWN_CONTENT_LOCALE = 39001;
    
    const ERROR_UNKNOWN_APPLICATION_LOCALE = 39002;
    
    const ERROR_NO_STORAGE_FILE_SET = 39003;
    
    const ERROR_CONFIGURE_NOT_CALLED = 39004;
    
    const ERROR_NO_SOURCES_ADDED = 39005;
    
    const ERROR_NO_LOCALE_SELECTED_IN_NS = 39006;
    
    const ERROR_NO_LOCALES_IN_NAMESPACE = 39007;
    
    const ERROR_UNKNOWN_NAMESPACE = 39008;
    
    const ERROR_UNKNOWN_LOCALE_IN_NS = 39009;
    
    const ERROR_UNKNOWN_EVENT_NAME = 39010;
    
    /**
     * The name of the default application locale, i.e. the
     * locale in which application textual content is written.
     *
     * @var string
     */
    const BUILTIN_LOCALE_NAME = 'en_UK';

    const NAMESPACE_APPLICATION = '__application';
    
    const NAMESPACE_CONTENT = '__content';
    
   /**
    * Collection of all locales by namespace (application, content, custom...). 
    *
    * @var array
    * @see Localization::addLocale()
    */
    protected static $locales = array();

    /**
     * @var boolean
     * @see Localization::init()
     */
    private static $initDone = false;

   /**
    * Path to the file in which the scanner results are stored.
    * @var string
    * @see Localization::configure()
    */
    protected static $storageFile = '';
    
   /**
    * Path to the folder into which the client libraries are written.
    * @var string
    * @see Localization::setClientLibrariesFolder()
    */
    protected static $clientFolder = '';
    
   /**
    * If this key changes, client libraries are refreshed.
    * @var string
    * @see Localization::setClientLibrariesCacheKey()
    */
    protected static $clientCacheKey = '';
    
   /**
    * Whether the configuration has been made.
    * @var bool
    * @see Localization::configure()
    */
    protected static $configured = false;
    
   /**
    * Stores event listener instances.
    * @var array
    */
    protected static $listeners = array();
    
   /**
    * @var integer
    * @see Localization::addEventListener()
    */
    protected static $listenersCounter = 0;
    
    /**
     * Initializes the localization layer. This is done
     * automatically, and only once per request.
     * 
     * (Called at the end of this file)
     */
    public static function init()
    {
        if(self::$initDone) {
            return;
        }

        self::reset();
        
        $installFolder = realpath(__DIR__.'/../');
        
        // add the localization package's own sources,
        // so the bundled localized strings can
        // always be translated.
        \AppLocalize\Localization::addSourceFolder(
            'application-localization',
            'Application Localization Package',
            'Composer packages',
            $installFolder.'/localization',
            $installFolder.'/src'
        )
        ->excludeFiles(array('jtokenizer'))
        ->excludeFolder('css');
        
        self::$initDone = true;
    }

    /**
     * Retrieves all available application locales, as an indexed
     * array with locale objects sorted by locale label.
     *
     * @return Localization_Locale[]
     * @see getAppLocale()
     */
    public static function getAppLocales()
    {
        return self::getLocalesByNS(self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Retrieves all locales in the specified namespace.
    * 
    * @param string $namespace
    * @return Localization_Locale[]
    */
    public static function getLocalesByNS(string $namespace)
    {
        if(isset(self::$locales[$namespace])) {
            return array_values(self::$locales[$namespace]);
        }
        
        throw new Localization_Exception(
            'No locales available in namespace',
            sprintf(
                'The namespace [%s] does not exist.',
                $namespace
            ),
            self::ERROR_NO_LOCALES_IN_NAMESPACE
        );
    }
    
   /**
    * Adds an application locale to use in the application.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function addAppLocale(string $localeName) : Localization_Locale
    {
        return self::addLocaleByNS($localeName, self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Adds a content locale to use for content in the application.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function addContentLocale(string $localeName) : Localization_Locale
    {
        return self::addLocaleByNS($localeName, self::NAMESPACE_CONTENT);
    }
    
   /**
    * Adds a locale to the specified namespace.
    * 
    * @param string $localeName
    * @param string $namespace
    * @return Localization_Locale
    */
    public static function addLocaleByNS(string $localeName, string $namespace) : Localization_Locale
    {
        if(!isset(self::$locales[$namespace])) {
            self::$locales[$namespace] = array();
        }
        
        if(!isset(self::$locales[$namespace][$localeName])) 
        {
            self::$locales[$namespace][$localeName] = self::createLocale($localeName);
            
            // sort the locales on add: less resource intensive
            // than doing it on getting locales.
            uasort(self::$locales[$namespace], function(Localization_Locale $a, Localization_Locale $b) {
                return strnatcasecmp($a->getLabel(), $b->getLabel());
            });
        }
        
        return self::$locales[$namespace][$localeName];
    }
    
    /**
     * @param string $localeName
     * @return Localization_Locale
     */
    protected static function createLocale(string $localeName)
    {
        return new Localization_Locale($localeName);
    }

    /**
     * Creates a new country object for the specified country, e.g. "uk".
     *
     * @param string $id
     * @return Localization_Country
     */
    public static function createCountry(string $id)
    {
        $className = '\AppLocalize\Localization_Country_' . strtoupper($id);
        return new $className();
    }
    
   /**
    * Retrieves the currency of the selected app locale.
    * 
    * @return Localization_Currency
    */
    public static function getAppCurrency() : Localization_Currency
    {
        return self::getCurrencyNS(self::NAMESPACE_APPLICATION);
    }

    /**
     * Retrieves the currency of the selected content locale.
     *
     * @return Localization_Currency
     */
    public static function getContentCurrency() : Localization_Currency
    {
        return self::getCurrencyNS(self::NAMESPACE_CONTENT);
    }
    
    /**
     * Retrieves the currency of the selected locale in the specified namespace.
     *
     * @param string $namespace
     * @return Localization_Currency
     */
    public static function getCurrencyNS(string $namespace) : Localization_Currency
    {
        return self::getSelectedLocaleByNS($namespace)->getCurrency();
    }
    
    /**
     * Retrieves the selected application locale instance. 
     *
     * @return Localization_Locale
     */
    public static function getAppLocale() : Localization_Locale
    {
        return self::getSelectedLocaleByNS(self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Retrieves the name of the selected application locale.
    * 
    * @return string
    */
    public static function getAppLocaleName() : string
    {
        return self::getLocaleNameByNS(self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Retrieves the names of the available application locales.
    * @return string[]
    */
    public static function getAppLocaleNames() : array
    {
        return self::getLocaleNamesByNS(self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Retrieves the selected locale name in the specified namespace.
    * 
    * @param string $namespace
    * @throws Localization_Exception
    * @return string
    */
    public static function getLocaleNameByNS(string $namespace) : string
    {
        return self::getSelectedKeyByNS('name', $namespace);
    }
    
   /**
    * Retrieves the selected locale instance for the specified namespace.
    * 
    * @param string $namespace
    * @return Localization_Locale
    * @throws Localization_Exception
    */
    public static function getSelectedLocaleByNS(string $namespace) : Localization_Locale
    {
        return self::getSelectedKeyByNS('locale', $namespace);
    }
    
    protected static function getSelectedKeyByNS(string $key, string $namespace)
    {
        self::requireNamespace($namespace);
        
        if(isset(self::$selected[$namespace])) {
            return self::$selected[$namespace][$key];
        }
        
        throw new Localization_Exception(
            'No selected locale in namespace.',
            sprintf(
                'Cannot retrieve selected key [%s]: no locale has been selected in the namespace [%s].',
                $key,
                $namespace
            ),
            self::ERROR_NO_LOCALE_SELECTED_IN_NS
        );
    }
    
   /**
    * Stores the selected locale names by namespace.
    * @var string[]
    */
    protected static $selected = array();

   /**
    * Selects the active locale for the specified namespace.
    * 
    * @param string $localeName
    * @param string $namespace
    * @return Localization_Locale
    * @throws Localization_Exception
    */
    public static function selectLocaleByNS(string $localeName, string $namespace) : Localization_Locale
    {
        self::requireNamespace($namespace);
        
        $locale = self::addLocaleByNS($localeName, $namespace);
        $previous = null;
        
        if(isset(self::$selected[$namespace])) 
        {
            if(self::$selected[$namespace]['name'] === $localeName) {
                return $locale;
            }
            
            $previous = self::$selected[$namespace];
        }
        
        self::$translator = null;
        
        self::$selected[$namespace] = array(
            'locale' => $locale,
            'name' => $localeName
        );
        
        self::triggerEvent(
            'LocaleChanged', 
            array(
                $namespace,
                $previous, 
                self::$selected[$namespace]
            )
        );
        
        return $locale;
    }
    
   /**
    * Triggers the specified event, with the provided arguments.
    * 
    * @param string $name The event name.
    * @param array $argsList
    * @see Localization_Event
    */
    protected static function triggerEvent(string $name, array $argsList) : Localization_Event
    {
        $class = '\AppLocalize\Localization_Event_'.$name;
        $event = new $class($argsList);
        
        if(!isset(self::$listeners[$name])) {
            return $event;
        }
        
        foreach(self::$listeners[$name] as $listener) 
        {
            $callArgs = $listener['args'];
            array_unshift($callArgs, $event);
            
            call_user_func_array($listener['callback'], $callArgs);
        }
        
        return $event;
    }
    
   /**
    * Adds a listener to the specified event name.
    * 
    * @param string $eventName
    * @param callable $callback
    * @param array $args Additional arguments to add to the event
    * @return int The listener number.
    */
    public static function addEventListener(string $eventName, $callback, array $args=array()) : int
    {
        if(!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = array();
        }
        
        $className = '\AppLocalize\Localization_Event_'.$eventName;
        
        if(!class_exists($className)) 
        {
            throw new Localization_Exception(
                sprintf('Unknown localization event [%s].', $eventName),
                sprintf('The required event class [%s] is not present.', $className),
                self::ERROR_UNKNOWN_EVENT_NAME
            );
        }
        
        self::$listenersCounter++;
        
        self::$listeners[$eventName][] = array(
            'class' => $className,
            'callback' => $callback,
            'args' => $args,
            'id' => self::$listenersCounter
        );
        
        return self::$listenersCounter;
    }
    
   /**
    * Adds an event listener for the <code>LocaleChanged</code> event,
    * which is triggered every time a locale is changed in any of the
    * available namespaces.
    * 
    * The first parameter of the callback is always the event instance.
    * 
    * @param callable $callback The listener function to call.
    * @param array $args Optional indexed array with additional arguments to pass on to the callback function.
    * @return int
    * @see Localization_Event_LocaleChanged
    */
    public static function onLocaleChanged($callback, array $args=array()) : int
    {
        return self::addEventListener('LocaleChanged', $callback, $args);
    }
    
   /**
    * Selects the application locale to use.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function selectAppLocale(string $localeName) : Localization_Locale
    {
        return self::selectLocaleByNS($localeName, self::NAMESPACE_APPLICATION);
    }

   /**
    * Retrieves an application locale by its name. 
    * Note that the locale must have been added first.
    * 
    * @param string $localeName
    * @throws Localization_Exception
    * @return Localization_Locale
    * @see Localization::appLocaleExists()
    */
    public static function getAppLocaleByName(string $localeName) : Localization_Locale
    {
        return self::getLocaleByNameNS($localeName, self::NAMESPACE_APPLICATION);
    }

    /**
     * Checks by the locale name if the specified locale is
     * available as a locale for the application.
     *
     * @param string $localeName
     * @return boolean
     */
    public static function appLocaleExists(string $localeName) : bool
    {
        return self::localeExistsInNS($localeName, self::NAMESPACE_APPLICATION);
    }
   
    public static function localeExistsInNS(string $localeName, string $namespace) : bool
    {
        return isset(self::$locales[$namespace]) && isset(self::$locales[$namespace][$localeName]);
    }

    /**
     * Retrieves an indexed array with all available content locales,
     * sorted by locale label.
     *
     * @return Localization_Locale[];
     */
    public static function getContentLocales()
    {
        return self::getLocalesByNS(self::NAMESPACE_CONTENT);
    }
    
   /**
    * Retrieves the names of all content locales that have been added.
    * @return string[]
    */
    public static function getContentLocaleNames()
    {
        return self::getLocaleNamesByNS(self::NAMESPACE_CONTENT);
    }
    
   /**
    * Retrieves all locale names available in the specified namespace.
    * The names are sorted alphabetically.
    * 
    * @param string $namespace
    * @return array|array
    */
    public static function getLocaleNamesByNS(string $namespace)
    {
        self::requireNamespace($namespace);
        
        $names = array_keys(self::$locales[$namespace]);
        
        sort($names);
        
        return $names;
     }
    
    /**
     * Checks by the locale name if the specified locale is
     * available as a locale for the user data.
     *
     * @param string $localeName
     * @return boolean
     */
    public static function contentLocaleExists($localeName)
    {
        return self::localeExistsInNS($localeName, self::NAMESPACE_CONTENT);
    }

    /**
     * Retrieves a specific content locale object by the locale name.
     * Note that you should check if it exists first to avoid triggering
     * an Exception if it does not.
     *
     * @param string $localeName
     * @throws Localization_Exception
     * @return Localization_Locale
     * @see Localization::contentLocaleExists()
     */
    public static function getContentLocaleByName($localeName) : Localization_Locale
    {
        return self::getLocaleByNameNS($localeName, self::NAMESPACE_CONTENT);
    }
    
   /**
    * Retrieves a locale by its name in the specified namespace.
    * 
    * @param string $localeName
    * @param string $namespace
    * @throws Localization_Exception
    * @return Localization_Locale
    */
    public static function getLocaleByNameNS(string $localeName, string $namespace) : Localization_Locale
    {
        self::requireNamespace($namespace);
        
        if(isset(self::$locales[$namespace]) && isset(self::$locales[$namespace][$localeName])) {
            return self::$locales[$namespace][$localeName];
        }
        
        throw new Localization_Exception(
            'Unknown locale in namespace',
            sprintf(
                'The locale [%s] has not been added to the namespace [%s].',
                $localeName,
                $namespace
            ),
            self::ERROR_UNKNOWN_LOCALE_IN_NS
        );
    }

    /**
     * Retrieves the currently selected content locale.
     *
     * @return Localization_Locale
     */
    public static function getContentLocale() : Localization_Locale
    {
        return self::getSelectedKeyByNS('locale', self::NAMESPACE_CONTENT);
    }

    public static function getContentLocaleName() : string
    {
        return self::getSelectedKeyByNS('name', self::NAMESPACE_CONTENT);
    }

    public static function isActiveAppLocale(Localization_Locale $locale)
    {
        return $locale->getName() === self::getAppLocaleName();
    }

    /**
     * Checks whether the specified locale is the current content locale.
     * @param Localization_Locale $locale
     * @return boolean
     */
    public static function isActiveContentLocale(Localization_Locale $locale)
    {
        return $locale->getName() === self::getContentLocaleName();
    }

    /**
     * Selects a specific content locale
     * @param string $localeName
     * @return Localization_Locale
     */
    public static function selectContentLocale(string $localeName) : Localization_Locale
    {
        return self::selectLocaleByNS($localeName, self::NAMESPACE_CONTENT);
    }
    
   /**
    * Checks whether the localization has been configured entirely.
    * @return bool
    */
    public static function isConfigured() : bool
    {
        return self::$configured;
    }

    protected static $translator;
    
    /**
     * @return Localization_Translator
     */
    public static function getTranslator(Localization_Locale $locale=null) : Localization_Translator
    {
        if($locale !== null)
        {
            $obj = new Localization_Translator();
            $obj->addSources(self::getSources());
            $obj->setTargetLocale($locale);
            return $obj;
        }
            
        if(!isset(self::$translator)) 
        {
            $obj = new Localization_Translator();
            $obj->addSources(self::getSources());
            $obj->setTargetLocale(self::getAppLocale());
            self::$translator = $obj;
        }

        return self::$translator;
    }

    public static function countContentLocales() : int
    {
        return self::countLocalesByNS(self::NAMESPACE_CONTENT);
    }

    public static function countAppLocales() : int
    {
        return self::countLocalesByNS(self::NAMESPACE_APPLICATION);
    }
    
    public static function countLocalesByNS(string $namespace) : int
    {
        self::requireNamespace($namespace);
        
        if(isset(self::$locales[$namespace])) {
            return count(self::$locales[$namespace]);
        }
    }
    
    protected static function requireNamespace(string $namespace)
    {
        if(isset(self::$locales[$namespace])) {
            return;
        }
        
        throw new Localization_Exception(
            'Cannot count locales in unknown namespace',
            sprintf(
                'The namespace [%s] does not exist.',
                $namespace
            ),
            self::ERROR_UNKNOWN_NAMESPACE
        );
    }
    
   /**
    * Injects a content locales selector element into the specified
    * HTML QuickForm2 container.
    * 
    * @param string $elementName
    * @param \HTML_QuickForm2_Container $container
    * @param string $label
    * @return \HTML_QuickForm2_Element_Select
    */
    public static function injectContentLocalesSelector(string $elementName, \HTML_QuickForm2_Container $container, string $label='') : \HTML_QuickForm2_Element_Select
    {
        return self::injectLocalesSelectorNS($elementName, self::NAMESPACE_CONTENT, $container, $label);
    }
    
   /**
    * Injects an app locales selector element into the specified
     * HTML QuickForm2 container.
     * 
    * @param string $elementName
    * @param \HTML_QuickForm2_Container $container
    * @param string|NULL $label
    * @return \HTML_QuickForm2_Element_Select
    */
    public static function injectAppLocalesSelector(string $elementName, \HTML_QuickForm2_Container $container, string $label='') : \HTML_QuickForm2_Element_Select
    {
        return self::injectLocalesSelectorNS($elementName, self::NAMESPACE_APPLICATION, $container, $label);
    }

    /**
     * Injects a locales selector element into the specified
     * HTML QuickForm2 container, for the specified locales
     * namespace.
     *
     * @param string $elementName
     * @param string $namespace
     * @param \HTML_QuickForm2_Container $container
     * @return \HTML_QuickForm2_Element_Select
     */
    public static function injectLocalesSelectorNS(string $elementName, string $namespace, \HTML_QuickForm2_Container $container, string $label='') : \HTML_QuickForm2_Element_Select
    {
        if(empty($label)) {
            $label = t('Language');
        }

        /* @var $select \HTML_QuickForm2_Element_Select */
        $select = $container->addSelect($elementName);
        $select->setLabel($label);

        $locales = self::getLocalesByNS($namespace);
        
        foreach($locales as $locale) {
            $select->addOption($locale->getLabel(), $locale->getName());
        }

        return $select;
    }

   /**
    * @var Localization_Source[]
    */
    protected static $sources = array();
    
   /**
    * @var string[]
    */
    protected static $excludeFolders = array();
    
   /**
    * @var string[]
    */
    protected static $excludeFiles = array();
    
   /**
    * Retrieves all currently available sources.
    * 
    * @return \AppLocalize\Localization_Source[]
    */
    public static function getSources()
    {
        return self::$sources;
    }
    
    public static function addExcludeFolder(string $folderName) : void
    { 
        if(!in_array($folderName, self::$excludeFolders)) {
            self::$excludeFolders[] = $folderName;
        }
    }
    
    public static function addExcludeFile(string $fileName) : void
    {
        if(!in_array($fileName, self::$excludeFiles)) {
            self::$excludeFiles[] = $fileName;
        }
    }
    
    public static function addSourceFolder($alias, $label, $group, $storageFolder, $path) : Localization_Source_Folder
    {
        $source = new Localization_Source_Folder($alias, $label, $group, $storageFolder, $path);
        self::$sources[] = $source;

        usort(self::$sources, function(Localization_Source $a, Localization_Source $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
        
        return $source;
    }
    
   /**
    * Retrieves all sources grouped by their group name.
    * @return Localization_Source[]
    */
    public static function getSourcesGrouped()
    {
        $sources = self::getSources();
        
        $grouped = array();
        
        foreach($sources as $source) 
        {
            $group = $source->getGroup();
            
            if(!isset($grouped[$group])) {
                $grouped[$group] = array();
            }
            
            $grouped[$group][] = $source;
        }
        
        return $grouped;
    }
    
   /**
    * Checks whether a specific source exists by its ID.
    * @param string $sourceID
    * @return boolean
    */
    public static function sourceExists(string $sourceID) : bool
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getID() == $sourceID) {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * Checks whether a specific source exists by its alias.
    * @param string $sourceAlias
    * @return boolean
    */
    public static function sourceAliasExists(string $sourceAlias) : bool
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getAlias() == $sourceAlias) {
                return true;
            }
        }
        
        return false;
    }

   /**
    * Retrieves a localization source by its ID.
    * 
    * @param string $sourceID
    * @throws Localization_Exception
    * @return Localization_Source
    */
    public static function getSourceByID(string $sourceID) : Localization_Source
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getID() == $sourceID) {
                return $source;
            }
        }
        
        throw new Localization_Exception(
            'Unknown localization source',
            sprintf(
                'The source [%s] has not been added. Available soources are: [%s].',
                $sourceID,
                implode(', ', self::getSourceIDs())
            )
        );
    }
    
    /**
     * Retrieves a localization source by its ID.
     *
     * @param string $sourceAlias
     * @throws Localization_Exception
     * @return Localization_Source
     */
    public static function getSourceByAlias(string $sourceAlias) : Localization_Source
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getAlias() == $sourceAlias) {
                return $source;
            }
        }
        
        throw new Localization_Exception(
            'Unknown localization source',
            sprintf(
                'The source [%s] has not been added. Available soources are: [%s].',
                $sourceAlias,
                implode(', ', self::getSourceAliases())
            )
        );
    }
    
   /**
    * Creates the scanner instance that is used to find
    * all translateable strings in the application.
    * 
    * @param string $storageFile Path to the file in which to store string information.
    * @return Localization_Scanner
    */
    public static function createScanner() : Localization_Scanner
    {
        self::requireConfiguration();
        
        return new Localization_Scanner(self::$storageFile);
    }
    
    public static function log($message)
    {
        // FIXME: TODO: Add this
    }
    
   /**
    * Configures the localization for the application:
    * sets the location of the required files and folders.
    * Also updated the client library files as needed.
    * 
    * @param string $storageFile Where to store the file analysis storage file.
    * @param string $clientLibrariesFolder Where to put the client libraries and translation files. Will be created if it does not exist. Optional: if not set, client libraries will not be created.
    */
    public static function configure(string $storageFile, string $clientLibrariesFolder='')
    {
        self::$configured = true;
        
        self::$storageFile = $storageFile;
        self::$clientFolder = $clientLibrariesFolder;

        // only write the client libraries to disk if the folder
        // has been specified.
        if(!empty($clientLibrariesFolder)) 
        {
            self::writeClientFiles();
        }
    }
    
   /**
    * Sets a key that is used to verify whether the client
    * libraries have to be refreshed. A common use is to set
    * this to the application's version number to guarantee
    * new texts are automatically used with each release.
    * 
    * NOTE: Otherwise files are refreshed only when saving 
    * them in the editor UI.
    *  
    * @param string $key
    */
    public static function setClientLibrariesCacheKey(string $key)
    {
        self::$clientCacheKey = $key;
    }
    
    public static function getClientLibrariesCacheKey() : string
    {
        return self::$clientCacheKey;
    }
    
   /**
    * Sets the folder where client libraries are to be stored.
    * @param string $folder
    */
    public static function setClientLibrariesFolder(string $folder)
    {
        self::$clientFolder = $folder;
    }
    
   /**
    * Retrieves the path to the folder in which the client
    * libraries should be stored.
    * 
    * NOTE: Can return an empty string, when this is disabled.
    * 
    * @return string
    */
    public static function getClientLibrariesFolder() : string
    {
        return self::$clientFolder;
    }
    
   /**
    * Writes / updates the client library files on disk,
    * at the location specified in the {@link Localization::configure()}
    * method.
    * 
    * @see Localization_ClientGenerator
    * @param bool $force Whether to refresh the files, even if they exist.
    */
    public static function writeClientFiles(bool $force=false) : void
    {
        self::createGenerator()->writeFiles($force);
    }
    
   /**
    * Creates a new instance of the client generator class
    * that is used to write the localization files into the
    * target folder on disk.
    * 
    * @return Localization_ClientGenerator
    */
    public static function createGenerator() : Localization_ClientGenerator
    {
        return new Localization_ClientGenerator();
    }
    
    public static function getClientFolder()
    {
        self::requireConfiguration();
        
        return self::$clientFolder;
    }
    
    protected static function requireConfiguration()
    {
        if(!self::$configured) 
        {
            throw new Localization_Exception(
                'The localization configuration is incomplete.',
                'The configure method has not been called.',
                self::ERROR_CONFIGURE_NOT_CALLED
            );
        }

        if(empty(self::$storageFile))
        {
            throw new Localization_Exception(
                'No localization storage file set',
                'To use the scanner, the storage file has to be set using the setStorageFile method.',
                self::ERROR_NO_STORAGE_FILE_SET
            );
        }
        
        if(empty(self::$sources)) 
        {
            throw new Localization_Exception(
                'No source folders have been defined.',
                'At least one source folder has to be configured using the addSourceFolder method.',
                self::ERROR_NO_SOURCES_ADDED
            );
        }
    }
    
   /**
    * Creates the editor instance that can be used to 
    * display the localization UI to edit translateable
    * strings in the browser.
    * 
    * @return \AppLocalize\Localization_Editor
    */
    public static function createEditor()
    {
        self::requireConfiguration();
        
        return new Localization_Editor();
    }
    
   /**
    * Retrieves a list of all available source IDs.
    * @return string[]
    */
    public static function getSourceIDs()
    {
        $ids = array();
        
        foreach(self::$sources as $source) {
            $ids[] = $source->getID();
        }
        
        return $ids;
    }
    
    /**
     * Retrieves a list of all available source aliases.
     * @return string[]
     */
    public static function getSourceAliases()
    {
        $aliases = array();
        
        foreach(self::$sources as $source) {
            $aliases[] = $source->getAlias();
        }
        
        return $aliases;
    }
    
   /**
    * Resets all locales to the built-in locale.
    */
    public static function reset()
    {
        self::$locales = array();
        self::$selected = array();
        self::$listeners = array();
        self::$listenersCounter = 0;
        
        self::addAppLocale(self::BUILTIN_LOCALE_NAME);
        self::addContentLocale(self::BUILTIN_LOCALE_NAME);
        
        self::selectAppLocale(self::BUILTIN_LOCALE_NAME);
        self::selectContentLocale(self::BUILTIN_LOCALE_NAME);
    }
    
    /**
     * Indxed array with locale names supported by the application
     * @var array
     */
    protected static $supportedLocales = array(
        'de_DE',
        'en_US',
        'en_CA',
        'en_UK',
        'es_ES',
        'fr_FR',
        'pl_PL',
        'it_IT',
        'de_AT',
        'de_CH'
    );
    
   /**
    * Retrieves a list of all supported locales.
    * 
    * @return array
    */
    public static function getSupportedLocaleNames() : array
    {
        return self::$supportedLocales;
    }
    
   /**
    * Checks whether the specified locale is supported.
    * 
    * @param string $localeName
    * @return bool
    */
    public static function isLocaleSupported(string $localeName) : bool
    {
        return in_array($localeName, self::$supportedLocales);
    }
}

Localization::init();