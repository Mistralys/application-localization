<?php
/**
 * File containing the {@link Localization_Translator} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Translator
 */

namespace AppLocalize;

/**
 * Application translation manager used to handle translating
 * application texts. Uses results from the {@link Localization_Parser}
 * class to determine which strings can be translated.
 *
 * Translations for each locale are stored in a separate file
 * and loaded as needed. The API allows adding new translations
 * and saving them to disk.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class Localization_Translator
{
    const ERROR_NO_STRINGS_AVAILABLE_FOR_LOCALE = 333101;
    
    const ERROR_CANNOT_SAVE_LOCALE_FILE = 333102;
    
    const ERROR_CANNOT_PARSE_LOCALE_FILE = 333103;
    
    /**
     * @var Localization_Locale
     */
    private $targetLocale;

    /**
     * Collection of strings per locale, associative array
     * with locale name => string pairs.
     *
     * @var array
     */
    private $strings = array();
    
    private $targetLocaleName = null;
    
   /**
    * @var Localization_Source[]
    */
    private $sources = array();

    public function addSource(Localization_Source $source)
    {
        $this->sources[] = $source;
    }
    
    public function addSources($sources)
    {
        foreach($sources as $source) {
            $this->addSource($source);
        }
    }

    /**
     * Sets the locale to translate strings to. If this is
     * not the same as the currently selected locale, this
     * triggers loading the locale's strings file.
     *
     * @param Localization_Locale $locale
     */
    public function setTargetLocale(Localization_Locale $locale)
    {
        // don't do anything if it's the same locale
        if (isset($this->targetLocale) && $locale->getName() == $this->targetLocale->getName()) {
            return;
        }

        $this->targetLocale = $locale;
        $this->targetLocaleName = $locale->getName();
        $this->load($locale);
    }

    /**
     * Indexed array with locale names for which the strings
     * have been loaded, used to avoid loading them repatedly.
     *
     * @var array
     */
    private $loaded = array();

    /**
     * Loads the strings for the specified locale if the
     * according storage file exists.
     *
     * @param Localization_Locale $locale
     * @return boolean
     */
    protected function load(Localization_Locale $locale)
    {
        // initialize the storage array regardless of success
        $localeName = $locale->getName();
        if (in_array($localeName, $this->loaded)) {
            return true;
        }

        if (!isset($this->strings[$localeName])) {
            $this->strings[$localeName] = array();
        }

        foreach($this->sources as $source)
        {
            $file = $this->resolveStorageFile($locale, $source);
            if (!file_exists($file)) {
                continue;
            }
            
            $data = parse_ini_file($file, false);
            
            if($data === false) 
            {
                throw new Localization_Exception(
                    'Malformatted localization file',
                    sprintf(
                        'The localization ini file %1$s cannot be parsed.',
                        $file
                    ),
                    self::ERROR_CANNOT_PARSE_LOCALE_FILE
                );
            }
    
            $this->strings[$localeName] = array_merge(
                $this->strings[$localeName],
                $data
            );
        }
        
        $this->loaded[] = $localeName;
        
        return true;
    }

    /**
     * Saves the current string collections for the target
     * locale to disk. The format is the regular PHP .ini
     * format with string hash => text pairs without sections.
     *
     * These files may be edited manually as well if needed,
     * but new strings can only be added via the UI because
     * the hashes have to be created.
     *
     * @param Localization_Source $source
     * @param Localization_Scanner_StringsCollection $collection
     */
    public function save(Localization_Source $source, Localization_Scanner_StringsCollection $collection) : void
    {
        // the serverside strings file gets all available hashes,
        // which are filtered by source.
        $this->renderStringsFile(
            'Serverside',
            $source,
            $collection->getHashes(),
            $this->resolveStorageFile($this->targetLocale, $source),
            $this->targetLocale
        );
        
        // the clientside strings file only gets the JS hashes.
        $this->renderStringsFile(
            'Clientside',
            $source,
            $collection->getHashesByLanguageID('Javascript'),
            $this->getClientStorageFile($this->targetLocale, $source),
            $this->targetLocale,
            false
        );
    }
    
   /**
    * Retrieves all available strings for the specified locale,
    * as hash => text value pairs.
    * 
    * @param Localization_Locale $locale
    * @throws Localization_Exception
    * @return string[]
    */
    public function getStrings(Localization_Locale $locale)
    {
        $this->load($locale);
        
        $name = $locale->getName();
        
        if(isset($this->strings[$name])) {
            return $this->strings[$name];
        }
        
        throw new Localization_Exception(
            'No strings available for '.$name,
            sprintf(
                'Tried getting strings for the locale [%s], but it has no strings. Available locales are: [%s].',
                $name,
                implode(', ', array_keys($this->strings))
            ),
            self::ERROR_NO_STRINGS_AVAILABLE_FOR_LOCALE
        );
    }
    
    public function hasStrings(Localization_Locale $locale) : bool
    {
        $this->load($locale);
        
        return !empty($this->strings[$locale->getName()]);
    }
    
   /**
    * @param string $type
    * @param Localization_Scanner_StringHash[] $hashes
    * @param string $file
    * @param Localization_Locale $locale
    * @param boolean $editable
    * @throws Localization_Exception
    */
    protected function renderStringsFile($type, Localization_Source $source, $hashes, $file, Localization_Locale $locale, $editable=true)
    {
        $sourceID = $source->getID();
        
        $title = strtoupper($type).' TRANSLATION FILE FOR ' . strtoupper($locale->getLabel());
        
        $content  = '; -------------------------------------------------------' . PHP_EOL;
        $content .= '; '. $title . PHP_EOL;
        $content .= '; -------------------------------------------------------' . PHP_EOL;
        $content .= '; ' . PHP_EOL;
        
        if($editable) {
            $content .= '; You may edit text directly in this file under the following conditions:' . PHP_EOL;
            $content .= '; ' . PHP_EOL;
            $content .= '; 1) Do not to modify the keys (left hand side of the = sign)' . PHP_EOL;
            $content .= '; 2) Save the file as UTF-8 without BOM' . PHP_EOL;
        } else {
            $content .= '; Do NOT edit this file directly! It depends on the main translation file' . PHP_EOL;
            $content .= '; and any changes will be lost. Edit the main file instead.' . PHP_EOL;
        }
        
        $content .= PHP_EOL;
        
        $saveList = array();
        foreach ($hashes as $hash) 
        {
            if(!$hash->hasSourceID($sourceID)) {
                continue;
            }
            
            $text = $this->getHashTranslation($hash->getHash(), $locale);

            // skip any empty strings
            if($text === null || trim($text) == '') {
                continue;
            }
            
            $saveList[] = array(
                'hash' => $hash->getHash(),
                'text' => $text
            );
        }
        
        usort($saveList, array($this, 'callback_sortStrings'));

        foreach($saveList as $entry) {
            $content .= $entry['hash'] . '= "' . $entry['text'] . '"' . PHP_EOL;
        }

        if(!file_put_contents($file, $content)) {
            throw new Localization_Exception(
                'Cannot save localization file',
                sprintf(
                    'Could not save the %s translation to file [%s].',
                    $locale->getName(),
                    $file
                ),
                self::ERROR_CANNOT_SAVE_LOCALE_FILE
            );
        }
    }
    
   /**
    * Sort the strings to ensure they always appear in the same order:
    * first by text, and same strings by their hashes. This is important 
    * for strings that have the same translation to avoid them changing 
    * order between sorts.
    * 
    * @param array $a
    * @param array $b
    * @return number
    */
    public function callback_sortStrings($a, $b)
    {
        $result = strnatcasecmp($a['text'], $b['text']);
        if($result==0) {
            return strnatcmp($a['hash'], $b['hash']);
        }
        
        return $result;
    }
    
    /**
     * Retrieves the full path to the strings storage ini file.
     * 
     * @param Localization_Locale $locale
     * @return string
     */
    protected function resolveStorageFile(Localization_Locale $locale, Localization_Source $source)
    {
        return sprintf(
            '%1$s/%2$s-%3$s-server.ini',
            $source->getStorageFolder(),
            $locale->getName(),
            $source->getAlias()
        );
    }
    
   /**
    * Retrieves the full path to the strings storage ini file 
    * for the clientside strings.
    * 
    * @param Localization_Locale $locale
    * @return string
    */
    protected function getClientStorageFile(Localization_Locale $locale, Localization_Source $source)
    {
        return sprintf(
            '%1$s/%2$s-%3$s-client.ini',
            $source->getStorageFolder(),
            $locale->getName(),
            $source->getAlias()
        );
    }

    /**
     * Sets the translation for a specific string hash
     * @param string $hash
     * @param string $text
     */
    public function setTranslation($hash, $text)
    {
        $this->strings[$this->targetLocale->getName()][$hash] = $text;
    }

    /**
     * Clears a translation for the specified string hash
     * @param string $hash
     */
    public function clearTranslation($hash)
    {
        unset($this->strings[$this->targetLocale->getName()][$hash]);
    }
    
    protected $reverseStrings = array();

    /**
     * Translates a string. The first parameter has to be the string
     * to translate, additional parameters are variables to insert
     * into the string.
     *
     * @return string|null
     */
    public function translate()
    {
        $args = func_get_args();
        $text = $args[0];
        
        // to avoid re-creating the hash for the same texts over and over,
        // we keep track of the hashes we created, and re-use them.
        if(isset($this->reverseStrings[$text])) {
            $hash = $this->reverseStrings[$text];
        } else {
            $hash = md5($text);
            $this->reverseStrings[$text] = $hash;
        }

        // replace the text with the one we have on record, otherwise
        // simply leave the original unchanged.
        if (isset($this->strings[$this->targetLocaleName][$hash])) {
            $args[0] = $this->strings[$this->targetLocaleName][$hash];
        }

        $result = call_user_func_array('sprintf', $args);
        if ($result === false) {
            throw new Localization_Exception(
                'Incorrectly translated string or erroneous localized string',
                sprintf(
                    'The string %1$s seems to have too many or too few arguments.',
                    $text
                )
            );
        }

        return $result;
    }

    /**
     * Checks if the specified string hash exists.
     * @param string $hash
     * @return boolean
     */
    public function hashExists($hash)
    {
        return array_key_exists($hash, $this->strings[$this->targetLocale->getName()]);
    }

    /**
     * Checks if a translation for the specified string hash exists.
     * @param string $text
     * @return boolean
     */
    public function translationExists($text)
    {
        return array_key_exists(md5($text), $this->strings[$this->targetLocale->getName()]);
    }

    /**
     * Retrieves the locale into which texts are currently translated.
     * @return Localization_Locale
     */
    public function getTargetLocale()
    {
        return $this->targetLocale;
    }

    /**
     * Retrieves the translation for the specified string hash.
     * @param string $hash
     * @return string|NULL
     */
    public function getHashTranslation($hash, Localization_Locale $locale=null)
    {
        if(!$locale) {
            $locale = $this->targetLocale;
        }
        
        $localeName = $locale->getName();
        
        if(isset($this->strings[$localeName]) && isset($this->strings[$localeName][$hash])) {
            return $this->strings[$localeName][$hash];
        }

        return null;
    }

   /**
    * Retrieves only the strings that are available clientside.
    * 
    * @param Localization_Locale $locale
    * @return array
    */
    public function getClientStrings(Localization_Locale $locale)
    {
        $result = array();
        
        foreach($this->sources as $source) 
        {
            $localeFile = self::getClientStorageFile($locale, $source);
            if(!file_exists($localeFile)) {
                continue;
            }
            
            $strings = parse_ini_file($localeFile);
            
            $result = array_merge($result, $strings);
        }
        
        return $result;
    }
}