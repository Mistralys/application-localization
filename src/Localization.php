<?php
/**
 * @package Localization
 * @subpackage Core
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Currencies\CurrencyCollection;
use AppLocalize\Localization\Currencies\CurrencyInterface;
use AppLocalize\Localization\Editor\LocalizationEditor;
use AppLocalize\Localization\Event\CacheKeyChanged;
use AppLocalize\Localization\Event\ClientFolderChanged;
use AppLocalize\Localization\Event\LocaleChanged;
use AppLocalize\Localization\Event\LocalizationEventInterface;
use AppLocalize\Localization\Locale\en_GB;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Scanner\LocalizationScanner;
use AppLocalize\Localization\Source\BaseLocalizationSource;
use AppLocalize\Localization\Source\FolderLocalizationSource;
use AppLocalize\Localization\Translator\ClientFilesGenerator;
use AppLocalize\Localization\Translator\LocalizationTranslator;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\Repository\ClassRepositoryException;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;
use HTML_QuickForm2_Container;
use HTML_QuickForm2_Element_Select;

/**
 * Localization handling collection for both the
 * application itself and its user contents.
 *
 * @package Localization
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @phpstan-type ListenerDef array{callback:callable,args:array<int,mixed>,id:int}
 */
class Localization
{
    /**
     * The name of the default application locale, i.e., the
     * locale in which application textual content is written.
     *
     * @var string
     */
    public const BUILTIN_LOCALE_NAME = en_GB::LOCALE_NAME;

    public const NAMESPACE_APPLICATION = '__application';
    public const NAMESPACE_CONTENT = '__content';

    public const EVENT_LOCALE_CHANGED = 'LocaleChanged';
    public const EVENT_CLIENT_FOLDER_CHANGED = 'ClientFolderChanged';
    public const EVENT_CACHE_KEY_CHANGED = 'CacheKeyChanged';

    /**
    * Collection of all locales by namespace (application, content, custom...). 
    *
    * @var array<string,array<string,LocaleInterface>>
    * @see Localization::addLocale()
    */
    protected static array $locales = array();

    /**
     * @var boolean
     * @see Localization::init()
     */
    private static bool $initDone = false;

   /**
    * Path to the file in which the scanner results are stored.
    * @var string
    * @see Localization::configure()
    */
    protected static string $storageFile = '';
    
   /**
    * Path to the folder into which the client libraries are written.
    * @var string
    * @see Localization::setClientLibrariesFolder()
    */
    protected static string $clientFolder = '';
    
   /**
    * If this key changes, client libraries are refreshed.
    * @var string
    * @see Localization::setClientLibrariesCacheKey()
    */
    protected static string $clientCacheKey = '';
    
   /**
    * Whether the configuration has been made.
    * @var bool
    * @see Localization::configure()
    */
    protected static bool $configured = false;
    
   /**
    * Stores event listener instances.
    * @var array<string,ListenerDef[]>
    */
    protected static array $listeners = array();
    
   /**
    * @var integer
    * @see Localization::addEventListener()
    */
    protected static int $listenersCounter = 0;
    
   /**
    * @var LocalizationTranslator|NULL
    */
    protected static ?LocalizationTranslator $translator;
    
   /**
    * Initializes the localization layer. This is done
    * automatically and only once per request.
    * 
    * (Called at the end of this file)
    */
    public static function init() : void
    {
        if(self::$initDone) {
            return;
        }

        self::reset();
        
        $installFolder = __DIR__.'/../';
        
        // add the localization package's own sources,
        // so the bundled localized strings can
        // always be translated.
        self::addSourceFolder(
            'application-localization',
            'Application Localization Package',
            'Composer packages',
            $installFolder.'/localization',
            $installFolder.'/src'
        )
            ->excludeFiles(array('functions.php'))
            ->excludeFolder('css');
        
        self::$initDone = true;
    }

    private static ?string $cacheFolder = null;

    public static function getCacheFolder() : string
    {
        if(isset(self::$cacheFolder)) {
            return self::$cacheFolder;
        }

        // Is a cache folder present in the constant?
        if(defined('LOCALIZATION_CACHE_FOLDER')) {
            $value = constant('LOCALIZATION_CACHE_FOLDER');
            if(is_string($value)) {
                self::$cacheFolder = $value;
            }
        }

        if(!isset(self::$cacheFolder)) {
            self::$cacheFolder = __DIR__.'/../cache';
        }

        return self::$cacheFolder;
    }

    private static ?ClassRepositoryManager $classRepository = null;

    public static function getClassRepository() : ClassRepositoryManager
    {
        if(!isset(self::$classRepository)) {
            self::$classRepository = ClassRepositoryManager::create(self::getCacheFolder());
        }

        return self::$classRepository;
    }

    /**
     * Retrieves all available application locales, as an indexed
     * array with locale objects sorted by locale label.
     *
     * @return LocaleInterface[]
     * @see getAppLocale()
     */
    public static function getAppLocales() : array
    {
        return self::getLocalesByNS(self::NAMESPACE_APPLICATION);
    }
    
   /**
    * Retrieves all locales in the specified namespace.
    * 
    * @param string $namespace
    * @return LocaleInterface[]
    * @throws LocalizationException {@see LocalizationException::ERROR_NO_LOCALES_IN_NAMESPACE}
    */
    public static function getLocalesByNS(string $namespace) : array
    {
        if(isset(self::$locales[$namespace])) {
            return array_values(self::$locales[$namespace]);
        }
        
        throw new LocalizationException(
            'No locales available in namespace',
            sprintf(
                'The namespace [%s] does not exist.',
                $namespace
            ),
            LocalizationException::ERROR_NO_LOCALES_IN_NAMESPACE
        );
    }

    /**
     * Adds an application locale to use in the application.
     *
     * @param string $localeName
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function addAppLocale(string $localeName) : LocaleInterface
    {
        return self::addLocaleByNS($localeName, self::NAMESPACE_APPLICATION);
    }

    /**
     * Adds a content locale to use for content in the application.
     *
     * @param string $localeName
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function addContentLocale(string $localeName) : LocaleInterface
    {
        return self::addLocaleByNS($localeName, self::NAMESPACE_CONTENT);
    }

    /**
     * Adds a locale to the specified namespace.
     *
     * @param string $localeName
     * @param string $namespace
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function addLocaleByNS(string $localeName, string $namespace) : LocaleInterface
    {
        if(!isset(self::$locales[$namespace])) {
            self::$locales[$namespace] = array();
        }

        $localeName = LocalesCollection::getInstance()->filterName($localeName);
        
        if(!isset(self::$locales[$namespace][$localeName])) 
        {
            self::$locales[$namespace][$localeName] = self::createLocale($localeName);
            
            // sort the locales on add: less resource intensive
            // than doing it on getting locales.
            uasort(self::$locales[$namespace], static function(LocaleInterface $a, LocaleInterface $b) : int {
                return strnatcasecmp($a->getLabel(), $b->getLabel());
            });
        }
        
        return self::$locales[$namespace][$localeName];
    }

    /**
     * @param string $localeName
     * @return LocaleInterface
     *
     * @throws LocalizationException
     * @see LocalizationException::ERROR_LOCALE_NOT_FOUND
     */
    protected static function createLocale(string $localeName) : LocaleInterface
    {
        $collection = LocalesCollection::getInstance();
        if($collection->idExists($localeName)) {
            return $collection->getByID($localeName);
        }

        throw new LocalizationException(
            'Locale not supported.',
            sprintf(
                'The locale class [%s] does not exist. Known locales are: [%s].',
                $localeName,
                implode(', ', $collection->getIDs())
            ),
            LocalizationException::ERROR_LOCALE_NOT_FOUND
        );
    }

    private static ?CountryCollection $countries = null;

    /**
     * Retrieves the country collection instance to
     * access all available countries.
     *
     * @return CountryCollection
     */
    public static function createCountries() : CountryCollection
    {
        if(!isset(self::$countries)) {
            self::$countries = CountryCollection::getInstance();
        }

        return self::$countries;
    }

    private static ?CurrencyCollection $currencies = null;

    /**
     * Retrieves the currency collection instance to
     * access all available currencies.
     *
     * @return CurrencyCollection
     */
    public static function createCurrencies() : CurrencyCollection
    {
        if(!isset(self::$currencies)) {
            self::$currencies = CurrencyCollection::getInstance();
        }

        return self::$currencies;
    }

    /**
     * Retrieves the currency of the selected app locale.
     *
     * @return CurrencyInterface
     *
     * @throws LocalizationException
     * @see LocalizationException::ERROR_NO_LOCALE_SELECTED_IN_NS
     */
    public static function getAppCurrency() : CurrencyInterface
    {
        return self::getCurrencyNS(self::NAMESPACE_APPLICATION);
    }

    /**
     * Retrieves the currency of the selected content locale.
     *
     * @return CurrencyInterface
     *
     * @throws LocalizationException
     * @see LocalizationException::ERROR_NO_LOCALE_SELECTED_IN_NS
     */
    public static function getContentCurrency() : CurrencyInterface
    {
        return self::getCurrencyNS(self::NAMESPACE_CONTENT);
    }

    /**
     * Retrieves the currency of the selected locale in the specified namespace.
     *
     * @param string $namespace
     * @return CurrencyInterface
     *
     * @throws LocalizationException
     * @see LocalizationException::ERROR_NO_LOCALE_SELECTED_IN_NS
     */
    public static function getCurrencyNS(string $namespace) : CurrencyInterface
    {
        return self::getSelectedLocaleByNS($namespace)->getCurrency();
    }
    
    /**
     * Retrieves the selected application locale instance. 
     *
     * @return LocaleInterface
     */
    public static function getAppLocale() : LocaleInterface
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
    * @return string
    * @throws LocalizationException
    */
    public static function getLocaleNameByNS(string $namespace) : string
    {
        return self::getSelectedLocaleByNS($namespace)->getName();
    }
    
   /**
    * Retrieves the selected locale instance for the specified namespace.
    * 
    * @param string $namespace
    * @return LocaleInterface
    * @throws LocalizationException
    * @see LocalizationException::ERROR_NO_LOCALE_SELECTED_IN_NS
    */
    public static function getSelectedLocaleByNS(string $namespace) : LocaleInterface
    {
        self::requireNamespace($namespace);
        
        if(isset(self::$selected[$namespace])) {
            return self::$selected[$namespace];
        }
        
        throw new LocalizationException(
            'No selected locale in namespace.',
            sprintf(
                'Cannot retrieve selected locale: no locale has been selected in the namespace [%s].',
                $namespace
            ),
            LocalizationException::ERROR_NO_LOCALE_SELECTED_IN_NS
        );
    }
    
   /**
    * Stores the selected locale names by namespace.
    * @var array<string,LocaleInterface>
    */
    protected static array $selected = array();

   /**
    * Selects the active locale for the specified namespace.
    *
    * NOTE: Triggers the "LocaleChanged" event.
    * 
    * @param string $localeName
    * @param string $namespace
    * @return LocaleInterface
    * @throws LocalizationException
    *
    * @see LocaleChanged
    */
    public static function selectLocaleByNS(string $localeName, string $namespace) : LocaleInterface
    {
        self::requireNamespace($namespace);

        $localeName = LocalesCollection::getInstance()->filterName($localeName);

        $locale = self::addLocaleByNS($localeName, $namespace);
        $previous = null;
        
        if(isset(self::$selected[$namespace])) 
        {
            if(self::$selected[$namespace]->getName() === $localeName) {
                return $locale;
            }
            
            $previous = self::$selected[$namespace];
        }
        
        self::$translator = null;

        self::$selected[$namespace] = $locale;
        
        self::triggerEvent(
            self::EVENT_LOCALE_CHANGED,
            array(
                $namespace,
                $previous, 
                self::$selected[$namespace]
            ),
            LocaleChanged::class
        );
        
        return $locale;
    }

    /**
     * Triggers the specified event, with the provided arguments.
     *
     * @param string $name The event name.
     * @param array<int,mixed> $argsList
     * @param class-string<LocalizationEventInterface> $class The class name of the event to trigger.
     * @return LocalizationEventInterface
     */
    protected static function triggerEvent(string $name, array $argsList, string $class) : LocalizationEventInterface
    {
        $event = ClassHelper::requireObjectInstanceOf(
            LocalizationEventInterface::class,
            new $class($argsList)
        );
        
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
     * @param array<int,mixed> $args Additional arguments to add to the event
     * @return int The listener number.
     *
     * @see LocalizationException::ERROR_UNKNOWN_EVENT_NAME
     */
    public static function addEventListener(string $eventName, callable $callback, array $args=array()) : int
    {
        if(!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = array();
        }
        
        self::$listenersCounter++;
        
        self::$listeners[$eventName][] = array(
            'callback' => $callback,
            'args' => $args,
            'id' => self::$listenersCounter
        );
        
        return self::$listenersCounter;
    }

    /**
     * Clears the dynamic class loading cache of the localization package.
     *
     * @return void
     * @throws ClassRepositoryException
     */
    public static function clearClassCache() : void
    {
        ClassRepositoryManager::create(self::getCacheFolder())->clearCache();
    }

    /**
     * Adds an event listener for the <code>LocaleChanged</code> event,
     * which is triggered every time a locale is changed in any of the
     * available namespaces.
     *
     * The first parameter of the callback is always the event instance.
     *
     * @param callable $callback The listener function to call.
     * @param array<int,mixed> $args Optional indexed array with additional arguments to pass on to the callback function.
     * @return int
     * @see LocaleChanged
     */
    public static function onLocaleChanged(callable $callback, array $args=array()) : int
    {
        return self::addEventListener(self::EVENT_LOCALE_CHANGED, $callback, $args);
    }

    /**
     * @param callable $callback
     * @param array<int,mixed> $args
     * @return int
     */
    public static function onClientFolderChanged(callable $callback, array $args=array()) : int
    {
        return self::addEventListener(self::EVENT_CLIENT_FOLDER_CHANGED, $callback, $args);
    }

    /**
     * Add a listener tp the cache key change event, when the cache files
     * must be rewritten.
     *
     * @param callable $callback
     * @param array<int,mixed> $args
     * @return int
     */
    public static function onCacheKeyChanged(callable $callback, array $args=array()) : int
    {
        return self::addEventListener(self::EVENT_CACHE_KEY_CHANGED, $callback, $args);
    }

    /**
     * Selects the application locale to use.
     *
     * @param string $localeName
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function selectAppLocale(string $localeName) : LocaleInterface
    {
        return self::selectLocaleByNS($localeName, self::NAMESPACE_APPLICATION);
    }

   /**
    * Retrieves an application locale by its name. 
    * Note that the locale must have been added first.
    * 
    * @param string $localeName
    * @return LocaleInterface
    * @throws LocalizationException
    * @see Localization::appLocaleExists()
    */
    public static function getAppLocaleByName(string $localeName) : LocaleInterface
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
        $localeName = LocalesCollection::getInstance()->filterName($localeName);

        return isset(self::$locales[$namespace][$localeName]);
    }

    /**
     * Retrieves an indexed array with all available content locales,
     * sorted by locale label.
     *
     * @return LocaleInterface[]
     * @throws LocalizationException
     */
    public static function getContentLocales() : array
    {
        return self::getLocalesByNS(self::NAMESPACE_CONTENT);
    }

    /**
     * Retrieves the names of all content locales that have been added.
     * @return string[]
     * @throws LocalizationException
     */
    public static function getContentLocaleNames() : array
    {
        return self::getLocaleNamesByNS(self::NAMESPACE_CONTENT);
    }

    /**
     * Retrieves all locale names available in the specified namespace.
     * The names are sorted alphabetically.
     *
     * @param string $namespace
     * @return string[]
     * @throws LocalizationException
     */
    public static function getLocaleNamesByNS(string $namespace) : array
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
    public static function contentLocaleExists(string $localeName) : bool
    {
        return self::localeExistsInNS($localeName, self::NAMESPACE_CONTENT);
    }

    /**
     * Retrieves a specific content locale object by the locale name.
     * Note that you should check if it exists first to avoid triggering
     * an Exception if it does not.
     *
     * @param string $localeName
     * @return LocaleInterface
     * @throws LocalizationException
     * @see Localization::contentLocaleExists()
     */
    public static function getContentLocaleByName(string $localeName) : LocaleInterface
    {
        return self::getLocaleByNameNS($localeName, self::NAMESPACE_CONTENT);
    }
    
   /**
    * Retrieves a locale by its name in the specified namespace.
    * 
    * @param string $localeName
    * @param string $namespace
    * @return LocaleInterface
    *@throws LocalizationException {@see LocalizationException::ERROR_UNKNOWN_LOCALE_IN_NS}
    */
    public static function getLocaleByNameNS(string $localeName, string $namespace) : LocaleInterface
    {
        self::requireNamespace($namespace);

        $localeName = LocalesCollection::getInstance()->filterName($localeName);

        if(isset(self::$locales[$namespace][$localeName])) {
            return self::$locales[$namespace][$localeName];
        }
        
        throw new LocalizationException(
            'Unknown locale in namespace',
            sprintf(
                'The locale [%s] has not been added to the namespace [%s].',
                $localeName,
                $namespace
            ),
            LocalizationException::ERROR_UNKNOWN_LOCALE_IN_NS
        );
    }

    /**
     * Retrieves the currently selected content locale.
     *
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function getContentLocale() : LocaleInterface
    {
        return self::getSelectedLocaleByNS(self::NAMESPACE_CONTENT);
    }

    /**
     * @return string
     * @throws LocalizationException
     */
    public static function getContentLocaleName() : string
    {
        return self::getSelectedLocaleByNS(self::NAMESPACE_CONTENT)->getName();
    }

    /**
     * @param LocaleInterface $locale
     * @return bool
     */
    public static function isActiveAppLocale(LocaleInterface $locale) : bool
    {
        return $locale->getName() === self::getAppLocaleName();
    }

    /**
     * Checks whether the specified locale is the current content locale.
     * @param LocaleInterface $locale
     * @return boolean
     * @throws LocalizationException
     */
    public static function isActiveContentLocale(LocaleInterface $locale) : bool
    {
        return $locale->getName() === self::getContentLocaleName();
    }

    /**
     * Selects a specific content locale
     * @param string $localeName
     * @return LocaleInterface
     * @throws LocalizationException
     */
    public static function selectContentLocale(string $localeName) : LocaleInterface
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

    /**
     * @param LocaleInterface|null $locale
     * @return LocalizationTranslator
     */
    public static function getTranslator(?LocaleInterface $locale=null) : LocalizationTranslator
    {
        if($locale !== null)
        {
            $obj = new LocalizationTranslator();
            $obj->addSources(self::getSources());
            $obj->setTargetLocale($locale);
            return $obj;
        }
            
        if(!isset(self::$translator)) 
        {
            $obj = new LocalizationTranslator();
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
        
        return 0;
    }

    /**
     * @param string $namespace
     * @throws LocalizationException
     */
    protected static function requireNamespace(string $namespace) : void
    {
        if(isset(self::$locales[$namespace])) {
            return;
        }
        
        throw new LocalizationException(
            'Cannot count locales in unknown namespace',
            sprintf(
                'The namespace [%s] does not exist.',
                $namespace
            ),
            LocalizationException::ERROR_UNKNOWN_NAMESPACE
        );
    }
    
   /**
    * Injects a content locales selector element into the specified
    * HTML QuickForm2 container.
    * 
    * @param string $elementName
    * @param HTML_QuickForm2_Container $container
    * @param string $label
    * @return HTML_QuickForm2_Element_Select
    */
    public static function injectContentLocalesSelector(string $elementName, HTML_QuickForm2_Container $container, string $label='') : HTML_QuickForm2_Element_Select
    {
        return self::injectLocalesSelectorNS($elementName, self::NAMESPACE_CONTENT, $container, $label);
    }
    
   /**
    * Injects an app locale selector element into the specified
     * HTML QuickForm2 container.
     * 
    * @param string $elementName
    * @param HTML_QuickForm2_Container $container
    * @param string $label
    * @return HTML_QuickForm2_Element_Select
    */
    public static function injectAppLocalesSelector(string $elementName, HTML_QuickForm2_Container $container, string $label='') : HTML_QuickForm2_Element_Select
    {
        return self::injectLocalesSelectorNS($elementName, self::NAMESPACE_APPLICATION, $container, $label);
    }

    /**
     * Injects a locale selector element into the specified
     * HTML QuickForm2 container, for the specified locale
     * namespace.
     *
     * @param string $elementName
     * @param string $namespace
     * @param HTML_QuickForm2_Container $container
     * @param string $label
     * @return HTML_QuickForm2_Element_Select
     * @throws LocalizationException
     */
    public static function injectLocalesSelectorNS(string $elementName, string $namespace, HTML_QuickForm2_Container $container, string $label='') : HTML_QuickForm2_Element_Select
    {
        if(empty($label)) {
            $label = t('Language');
        }

        $select = $container->addSelect($elementName);
        $select->setLabel($label);

        $locales = self::getLocalesByNS($namespace);
        
        foreach($locales as $locale) {
            $select->addOption($locale->getLabel(), $locale->getName());
        }

        return $select;
    }

   /**
    * @var BaseLocalizationSource[]
    */
    protected static array $sources = array();
    
   /**
    * @var string[]
    */
    protected static array $excludeFolders = array();
    
   /**
    * @var string[]
    */
    protected static array $excludeFiles = array();
    
   /**
    * Retrieves all currently available sources.
    * 
    * @return BaseLocalizationSource[]
    */
    public static function getSources() : array
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
    
    public static function addSourceFolder(string $alias, string $label, string $group, string $storageFolder, string $path) : FolderLocalizationSource
    {
        $source = new FolderLocalizationSource($alias, $label, $group, $storageFolder, $path);
        self::$sources[] = $source;

        usort(self::$sources, static function(BaseLocalizationSource $a, BaseLocalizationSource $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
        
        return $source;
    }
    
   /**
    * Retrieves all sources grouped by their group name.
    * @return array<string,BaseLocalizationSource[]>
    */
    public static function getSourcesGrouped() : array
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
            if($source->getID() === $sourceID) {
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
            if($source->getAlias() === $sourceAlias) {
                return true;
            }
        }
        
        return false;
    }

   /**
    * Retrieves a localization source by its ID.
    * 
    * @param string $sourceID
    * @return BaseLocalizationSource
    *@throws LocalizationException
    */
    public static function getSourceByID(string $sourceID) : BaseLocalizationSource
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getID() === $sourceID) {
                return $source;
            }
        }
        
        throw new LocalizationException(
            'Unknown localization source',
            sprintf(
                'The source [%s] has not been added. Available sources are: [%s].',
                $sourceID,
                implode(', ', self::getSourceIDs())
            )
        );
    }
    
    /**
     * Retrieves a localization source by its ID.
     *
     * @param string $sourceAlias
     * @return BaseLocalizationSource
     *@throws LocalizationException
     */
    public static function getSourceByAlias(string $sourceAlias) : BaseLocalizationSource
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getAlias() === $sourceAlias) {
                return $source;
            }
        }
        
        throw new LocalizationException(
            'Unknown localization source',
            sprintf(
                'The source [%s] has not been added. Available sources are: [%s].',
                $sourceAlias,
                implode(', ', self::getSourceAliases())
            )
        );
    }

    /**
     * Creates the scanner instance used to find
     * all translatable strings in the application.
     *
     * @return LocalizationScanner
     * @throws LocalizationException
     */
    public static function createScanner() : LocalizationScanner
    {
        self::requireConfiguration();
        
        return new LocalizationScanner(self::$storageFile);
    }
    
    public static function log(string $message) : void
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
     * @throws FileHelper_Exception
     * @throws LocalizationException
     */
    public static function configure(string $storageFile, string $clientLibrariesFolder='') : void
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
    * Sets a key used to verify whether the client
    * libraries have to be refreshed. A common use is to set
    * this to the application's version number to guarantee
    * new texts are automatically used with each release.
    * 
    * NOTE: Otherwise files are refreshed only when saving 
    * them in the editor UI.
    *  
    * @param string $key
    */
    public static function setClientLibrariesCacheKey(string $key) : void
    {
        if($key !== self::$clientCacheKey) {
            self::$clientCacheKey = $key;
            self::triggerEvent(self::EVENT_CACHE_KEY_CHANGED, array($key), CacheKeyChanged::class);
        }
    }
    
    public static function getClientLibrariesCacheKey() : string
    {
        return self::$clientCacheKey;
    }
    
   /**
    * Sets the folder where client libraries are to be stored.
    * @param string $folder
    */
    public static function setClientLibrariesFolder(string $folder) : void
    {
        if($folder !== self::$clientFolder) {
            self::$clientFolder = $folder;
            self::triggerEvent(self::EVENT_CLIENT_FOLDER_CHANGED, array($folder), ClientFolderChanged::class);
        }
    }
    
   /**
    * Retrieves the path to the folder in which the client
    * libraries should be stored.
    * 
    * > NOTE: Can return an empty string when this is disabled.
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
     * @param bool $force Whether to force the update of the files, even if they are not changed.
     * @throws LocalizationException|FileHelper_Exception
     * @see ClientFilesGenerator
     */
    public static function writeClientFiles(bool $force=false) : void
    {
        self::createGenerator()->writeFiles($force);
    }

    private static ?ClientFilesGenerator $generator = null;

   /**
    * Creates a new instance of the client generator class
    * that is used to write the localization files into the
    * target folder on disk.
    * 
    * @return ClientFilesGenerator
    */
    public static function createGenerator() : ClientFilesGenerator
    {
        if(!isset(self::$generator)) {
            self::$generator = new ClientFilesGenerator();
        }

        return self::$generator;
    }

    /**
     * @return string
     * @throws LocalizationException
     */
    public static function getClientFolder() : string
    {
        self::requireConfiguration();
        
        return self::$clientFolder;
    }

    /**
     * @throws LocalizationException
     */
    protected static function requireConfiguration() : void
    {
        if(!self::$configured) 
        {
            throw new LocalizationException(
                'The localization configuration is incomplete.',
                'The configure method has not been called.',
                LocalizationException::ERROR_CONFIGURE_NOT_CALLED
            );
        }

        if(empty(self::$storageFile))
        {
            throw new LocalizationException(
                'No localization storage file set',
                'To use the scanner, the storage file has to be set using the setStorageFile method.',
                LocalizationException::ERROR_NO_STORAGE_FILE_SET
            );
        }
        
        if(empty(self::$sources)) 
        {
            throw new LocalizationException(
                'No source folders have been defined.',
                'At least one source folder has to be configured using the addSourceFolder method.',
                LocalizationException::ERROR_NO_SOURCES_ADDED
            );
        }
    }

    /**
     * Creates the editor instance that can be used to
     * display the localization UI to edit translatable
     * strings in the browser.
     *
     * @return LocalizationEditor
     * @throws LocalizationException
     */
    public static function createEditor() : LocalizationEditor
    {
        self::requireConfiguration();
        
        return new LocalizationEditor();
    }
    
   /**
    * Retrieves a list of all available source IDs.
    * @return string[]
    */
    public static function getSourceIDs() : array
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
    public static function getSourceAliases() : array
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
    public static function reset() : void
    {
        self::$locales = array();
        self::$selected = array();

        self::addAppLocale(self::BUILTIN_LOCALE_NAME);
        self::addContentLocale(self::BUILTIN_LOCALE_NAME);
        
        self::selectAppLocale(self::BUILTIN_LOCALE_NAME);
        self::selectContentLocale(self::BUILTIN_LOCALE_NAME);
    }
    
    /**
     * Retrieves a list of all supported locales.
     *
     * @return string[]
     */
    public static function getSupportedLocaleNames() : array
    {
        return LocalesCollection::getInstance()->getIDs();
    }
    
   /**
    * Checks whether the specified locale is supported.
    * 
    * @param string $localeName
    * @return bool
    */
    public static function isLocaleSupported(string $localeName) : bool
    {
        return LocalesCollection::getInstance()->idExists($localeName);
    }

    private static ?string $version = null;

    public static function getVersion() : string
    {
        if(isset(self::$version)) {
            return self::$version;
        }

        self::$version = self::getVersionFile()->getContents();

        return self::$version;
    }

    public static function getVersionFile() : FileInfo
    {
        return FileInfo::factory(__DIR__.'/../version.txt');
    }
}

Localization::init();
