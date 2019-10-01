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
 * @link http://www.mistralys.com
 */
class Localization
{
    const ERROR_UNKNOWN_CONTENT_LOCALE = 39001;
    
    const ERROR_UNKNOWN_APPLICATION_LOCALE = 39002;
    
    const ERROR_NO_STORAGE_FILE_SET = 39003;
    
    /**
     * The name of the default application locale, i.e. the
     * locale in which application textual content is written.
     *
     * @var string
     */
    const BUILTIN_LOCALE_NAME = 'en_UK';

    /**
     * Collection of all content locales, as an associative
     * array with locale name => locale object value pairs.
     *
     * @var array
     * @see getContentLocales()
     * @see getContentLocale()
     * @see getContentLocale()
     * @see Localization_Locale
     */
    private static $contentLocales = array();

    /**
     * Collection of all content locales, as an associative
     * array with locale name => locale object value pairs.
     *
     * @var array
     * @see getAppLocales()
     * @see getAppLocale()
     * @see Localization_Locale
     */
    private static $applicationLocales = array();

    /**
     * @var boolean
     */
    private static $initDone = false;

    /**
     * Initializes the localization layer. This is done
     * automatically, and only once per request.
     */
    public static function init()
    {
        if(self::$initDone) {
            return;
        }

        self::addAppLocale(self::BUILTIN_LOCALE_NAME);
        self::addContentLocale(self::BUILTIN_LOCALE_NAME);
        
        self::selectAppLocale(self::BUILTIN_LOCALE_NAME);
        self::selectContentLocale(self::BUILTIN_LOCALE_NAME);
        
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
        $locales = array_values(self::$applicationLocales);

        usort($locales, function(Localization_Locale $a, Localization_Locale $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
        
        return $locales;
    }
    
   /**
    * Adds an application locale to use in the application.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function addAppLocale(string $localeName) : Localization_Locale
    {
        if(!isset(self::$applicationLocales[$localeName])) {
            self::$applicationLocales[$localeName] = self::createLocale($localeName);
        }
        
        return self::$applicationLocales[$localeName];
    }
    
   /**
    * Adds a content locale to use for content in the application.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function addContentLocale(string $localeName) : Localization_Locale
    {
        if(!isset(self::$contentLocales[$localeName])) {
            self::$contentLocales[$localeName] = self::createLocale($localeName);
        }
        
        return self::$contentLocales[$localeName];
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
    * @var Localization_Locale
    */
    protected static $applicationLocale;
    
   /**
    * @var string
    */
    protected static $applicationLocaleName;

    /**
     * Returns the current application locale. This is the builtin
     * locale by default, or the locale as set in the user settings.
     *
     * @return Localization_Locale
     */
    public static function getAppLocale() : Localization_Locale
    {
        return self::$applicationLocale;
    }

   /**
    * Selects the application locale to use.
    * 
    * @param string $localeName
    * @return Localization_Locale
    */
    public static function selectAppLocale(string $localeName) : Localization_Locale
    {
        self::$applicationLocale = self::addAppLocale($localeName);
        self::$applicationLocaleName = $localeName;
        
        return self::$applicationLocale;
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
        if(isset(self::$applicationLocales[$localeName])) {
            return self::$applicationLocales[$localeName];
        }

        throw new Localization_Exception(
            'Unknown application locale',
            sprintf(
                'Tried getting locale [%1$s], but that does not exist. Available locales are: [%2$s].',
                $localeName,
                implode(', ', array_keys(self::$applicationLocales))
            ),
            self::ERROR_UNKNOWN_APPLICATION_LOCALE
        );
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
        return isset(self::$applicationLocales[$localeName]);
    }

    /**
     * Retrieves an indexed array with all available content locales,
     * sorted by locale label.
     *
     * @return Localization_Locale[];
     */
    public static function getContentLocales()
    {
        $locales = array_values(self::$contentLocales);
        
        usort($locales, function(Localization_Locale $a, Localization_Locale $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
        
        return $locales;
    }
    
   /**
    * Retrieves the names of all content locales that have been added.
    * @return string[]
    */
    public static function getContentLocaleNames()
    {
        return array_keys(self::$contentLocales);
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
        return isset(self::$contentLocales[$localeName]);
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
        if(isset(self::$contentLocales[$localeName])) {
            return self::$contentLocales[$localeName];
        }
        
        throw new Localization_Exception(
            'Unknown locale',
            sprintf(
                'Cannot get locale [%s], it does not exist. Valid locale names are: [%s].',
                $localeName,
                implode(', ', array_keys(self::$contentLocales))
            ),
            self::ERROR_UNKNOWN_CONTENT_LOCALE
        );
    }

    private static $contentLocale;
    
    private static $contentLocaleName;

    /**
     * Retrieves the currently selected content locale.
     *
     * @return Localization_Locale
     */
    public static function getContentLocale() : Localization_Locale
    {
        return self::$contentLocale;
    }

    public static function getContentLocaleName() : string
    {
        return self::$currentContentLocaleName = self::getContentLocale()->getName();
    }

    public static function isActiveAppLocale(Localization_Locale $locale)
    {
        return $locale->getName() === self::$applicationLocaleName;
    }

    /**
     * Checks whether the specified locale is the current content locale.
     * @param Localization_Locale $locale
     * @return boolean
     */
    public static function isActiveContentLocale(Localization_Locale $locale)
    {
        return $locale->getName() === self::$contentLocaleName;
    }

    /**
     * Selects a specific content locale
     * @param string $localeName
     * @return Localization_Locale
     */
    public static function selectContentLocale(string $localeName) : Localization_Locale
    {
        self::$contentLocale = self::addContentLocale($localeName);
        self::$contentLocaleName = $localeName;
        
        return self::$contentLocale;
    }

    protected static $translator;
    
    /**
     * @return Localization_Translator
     */
    public static function getTranslator() : Localization_Translator
    {
        if(!isset(self::$translator)) 
        {
            $obj = new Localization_Translator();
            $obj->addSources(self::getSources());
            $obj->setTargetLocale(self::getAppLocale());
            self::$translator = $obj;
        }

        return self::$translator;
    }

    public static function countContentLocales()
    {
        return count(self::$contentLocales);
    }

    public static function countLocales()
    {
        return count(self::$applicationLocales);
    }

    /**
     * Injects an application locales selector element to the specified
     * form container.
     *
     * @param string $name
     * @param \HTML_QuickForm2_Container $container
     * @return \HTML_QuickForm2_Element_Select
     */
    public static function injectLocalesSelector($name, \HTML_QuickForm2_Container $container, $label = null)
    {
        if (is_null($label)) {
            $label = t('Language');
        }

        /* @var $select \HTML_QuickForm2_Element_Select */
        $select = $container->addElement('select', $name);
        $select->setLabel($label);
        $select->setId('f-' . $name);
        $select->addClass('input-xlarge');

        foreach (self::$applicationLocales as $locale) {
            $select->addOption($locale->getLabel(), $locale->getName());
        }

        return $select;
    }

   /**
    * Retrieves the current application currency instance.
    * @return Localization_Currency
    */
    public static function getCurrentCurrency()
    {
        return self::getAppLocale()->getCurrency();
    }

   /**
    * @var Localization_Source[]
    */
    protected static $sources = array();
    
    protected static $excludeFolders = array();
    
    protected static $excludeFiles = array();
    
    public static function getSources()
    {
        return self::$sources;
    }
    
    public static function addExcludeFolder(string $folderName)
    { 
        if(!in_array($folderName, self::$excludeFolders)) {
            self::$excludeFolders[] = $folderName;
        }
    }
    
    public static function addExcludeFile(string $fileName)
    {
        if(!in_array($fileName, self::$excludeFiles)) {
            self::$excludeFiles[] = $fileName;
        }
    }
    
    public static function addSourceFolder($alias, $label, $group, $storageFolder, $path)
    {
        $source = new Localization_Source_Folder($alias, $label, $group, $storageFolder, $path);
        self::$sources[] = $source;
        
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
    public static function sourceExists($sourceID)
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getID() == $sourceID) {
                return true;
            }
        }
        
        return false;
    }

    public static function getSourceByID($sourceID)
    {
        $sources = self::getSources();
        foreach($sources as $source) {
            if($source->getID() == $sourceID) {
                return $source;
            }
        }
        
        return null;
    }
    
    protected static $storageFile;
    
   /**
    * Creates the scanner instance that is used to find
    * all translateable strings in the application.
    * 
    * @param string $storageFile Path to the file in which to store string information.
    * @return Localization_Scanner
    */
    public static function createScanner()
    {
        if(!isset(self::$storageFile)) 
        {
            throw new Localization_Exception(
                'No localization storage file set',
                'To use the scanner, the storage file has to be set using the setStorageFile method.',
                self::ERROR_NO_STORAGE_FILE_SET
            );
        }
        
        return new Localization_Scanner(self::$storageFile);
    }
    
    public static function log($message)
    {
        // FIXME: TODO: Add this
    }
    
    public static function setStorageFile(string $storageFile)
    {
        self::$storageFile = $storageFile;
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
}

Localization::init();