<?php
/**
 * @package Localization
 * @subpackage Translator
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Translator;

use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Parser\LocalizationParser;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Scanner\StringCollection;
use AppLocalize\Localization\Scanner\StringHash;
use AppLocalize\Localization\Source\BaseLocalizationSource;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\IniHelper;
use AppUtils\Interfaces\StringableInterface;
use Throwable;

/**
 * Application translation manager used to handle translating
 * application texts. Uses results from the {@link LocalizationParser}
 * class to determine which strings can be translated.
 *
 * Translations for each locale are stored in a separate file
 * and loaded as needed. The API allows adding new translations
 * and saving them to disk.
 *
 * @package Localization
 * @subpackage Translator
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class LocalizationTranslator
{
    public const ERROR_NO_STRINGS_AVAILABLE_FOR_LOCALE = 333101;
    public const ERROR_CANNOT_PARSE_LOCALE_FILE = 333103;
    
    private LocaleInterface $targetLocale;

    /**
     * Collection of strings per locale, associative array
     * with locale name => string pairs.
     *
     * @var array<string,string[]>
     */
    private array $strings = array();

    private ?string $targetLocaleName = null;

    /**
     * @var array<string,string>
     */
    protected array $reverseStrings = array();

    /**
    * @var BaseLocalizationSource[]
    */
    private array $sources = array();

    /**
     * Indexed array with locale names for which the strings
     * have been loaded, used to avoid loading them repeatedly.
     *
     * @var string[]
     */
    private array $loaded = array();

    public function addSource(BaseLocalizationSource $source) : void
    {
        $this->sources[] = $source;
    }

    /**
     * @param BaseLocalizationSource[] $sources
     */
    public function addSources(array $sources) : void
    {
        foreach($sources as $source) {
            $this->addSource($source);
        }
    }

    /**
     * Sets the locale to translate strings to. If this is
     *  different from the currently selected locale, this
     * triggers loading the locale's string file.
     *
     * @param LocaleInterface $locale
     * @throws LocalizationException
     */
    public function setTargetLocale(LocaleInterface $locale) : void
    {
        // don't do anything if it's the same locale
        if (isset($this->targetLocale) && $locale->getName() === $this->targetLocale->getName()) {
            return;
        }

        $this->targetLocale = $locale;
        $this->targetLocaleName = $locale->getName();
        $this->load($locale);
    }

    /**
     * Loads the strings for the specified locale if the
     * according storage file exists.
     *
     * @param LocaleInterface $locale
     * @return boolean
     * @throws LocalizationException
     */
    protected function load(LocaleInterface $locale) : bool
    {
        // initialize the storage array regardless of success
        $localeName = $locale->getName();
        if (in_array($localeName, $this->loaded, true)) {
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
            
            $data = parse_ini_file($file);
            
            if($data === false) 
            {
                throw new LocalizationException(
                    'Malformed localization file',
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
     * @param BaseLocalizationSource $source
     * @param StringCollection $collection
     */
    public function save(BaseLocalizationSource $source, StringCollection $collection) : void
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
    * @param LocaleInterface $locale
    * @return string[]
    * @throws LocalizationException
    */
    public function getStrings(LocaleInterface $locale) : array
    {
        $this->load($locale);
        
        $name = $locale->getName();
        
        if(isset($this->strings[$name])) {
            return $this->strings[$name];
        }
        
        throw new LocalizationException(
            'No strings available for '.$name,
            sprintf(
                'Tried getting strings for the locale [%s], but it has no strings. Available locales are: [%s].',
                $name,
                implode(', ', array_keys($this->strings))
            ),
            self::ERROR_NO_STRINGS_AVAILABLE_FOR_LOCALE
        );
    }

    /**
     * @param LocaleInterface $locale
     * @return bool
     * @throws LocalizationException
     */
    public function hasStrings(LocaleInterface $locale) : bool
    {
        $this->load($locale);
        
        return !empty($this->strings[$locale->getName()]);
    }

    /**
     * @param string $type
     * @param BaseLocalizationSource $source
     * @param StringHash[] $hashes
     * @param string $file
     * @param LocaleInterface $locale
     * @param boolean $editable
     */
    protected function renderStringsFile(string $type, BaseLocalizationSource $source, array $hashes, string $file, LocaleInterface $locale, bool $editable=true) : void
    {
        $writer = new LocalizationWriter($locale, $type, $file);

        if($editable)
        {
            $writer->makeEditable();
        }
        
        $sourceID = $source->getID();
        
        foreach ($hashes as $hash) 
        {
            if(!$hash->hasSourceID($sourceID)) {
                continue;
            }
            
            $text = $this->getHashTranslation($hash->getHash(), $locale);

            // skip any empty strings
            if($text === null || trim($text) === '') {
                continue;
            }
            
            $writer->addHash($hash->getHash(), $text);
        }
        
        $writer->writeFile();
    }

    /**
     * Retrieves the full path to the translation storage ini file.
     *
     * @param LocaleInterface $locale
     * @param BaseLocalizationSource $source
     * @return string
     */
    protected function resolveStorageFile(LocaleInterface $locale, BaseLocalizationSource $source) : string
    {
        return sprintf(
            '%1$s/%2$s-%3$s-server.ini',
            $source->getStorageFolder(),
            $locale->getName(),
            $source->getAlias()
        );
    }

    /**
     * Retrieves the full path to the translation storage ini file
     * for the clientside strings.
     *
     * @param LocaleInterface $locale
     * @param BaseLocalizationSource $source
     * @return string
     */
    protected function getClientStorageFile(LocaleInterface $locale, BaseLocalizationSource $source) : string
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
    public function setTranslation(string $hash, string $text) : void
    {
        $this->strings[$this->targetLocale->getName()][$hash] = $text;
    }

    /**
     * Clears a translation for the specified string hash
     * @param string $hash
     */
    public function clearTranslation(string $hash) : void
    {
        unset($this->strings[$this->targetLocale->getName()][$hash]);
    }

    /**
     * Translates a string. The first parameter has to be the string
     * to translate. Additional parameters are variables to insert
     * into the string.
     *
     * @param string $text
     * @param array<int,string|int|float|StringableInterface|NULL> $args
     * @return string
     * @throws LocalizationException
     */
    public function translate(string $text, array $args) : string
    {
        // to avoid re-creating the hash for the same texts over and over,
        // we keep track of the hashes we created, and re-use them.
        if(isset($this->reverseStrings[$text])) {
            $hash = $this->reverseStrings[$text];
        } else {
            $hash = md5($text);
            $this->reverseStrings[$text] = $hash;
        }

        // replace the text with the one we have on record, otherwise
        // leave the original unchanged.
        if (isset($this->strings[$this->targetLocaleName][$hash])) {
            $text = $this->strings[$this->targetLocaleName][$hash];
        }

        array_unshift($args, $text);

        try
        {
            return sprintf(...$args);
        }
        catch (Throwable $e)
        {
            array_shift($args);

            throw new LocalizationException(
                'Incorrectly translated string or erroneous localized string',
                sprintf(
                    'The string [%1$s] does not have the correct amount of placeholders. '.PHP_EOL.
                    'Values given: '.PHP_EOL.
                    '%2$s',
                    $text,
                    JSONConverter::var2json($args, JSON_PRETTY_PRINT)
                ),
                LocalizationException::ERROR_INCORRECTLY_TRANSLATED_STRING,
                $e
            );
        }
    }

    /**
     * Checks if the specified string hash exists.
     * @param string $hash
     * @return boolean
     */
    public function hashExists(string $hash) : bool
    {
        return array_key_exists($hash, $this->strings[$this->targetLocale->getName()]);
    }

    /**
     * Checks if a translation for the specified string hash exists.
     * @param string $text
     * @return boolean
     */
    public function translationExists(string $text) : bool
    {
        return array_key_exists(md5($text), $this->strings[$this->targetLocale->getName()]);
    }

    /**
     * Retrieves the locale into which texts are currently translated.
     * @return LocaleInterface
     */
    public function getTargetLocale() : LocaleInterface
    {
        return $this->targetLocale;
    }

    /**
     * Retrieves the translation for the specified string hash.
     *
     * @param string $hash
     * @param LocaleInterface|null $locale
     * @return string|NULL
     */
    public function getHashTranslation(string $hash, ?LocaleInterface $locale=null) : ?string
    {
        if(!$locale) {
            $locale = $this->targetLocale;
        }
        
        $localeName = $locale->getName();

        return $this->strings[$localeName][$hash] ?? null;
    }

    /**
     * Retrieves only the strings that are available clientside.
     *
     * @param LocaleInterface $locale
     * @return array<string,string>
     */
    public function getClientStrings(LocaleInterface $locale) : array
    {
        $result = array();
        
        foreach($this->sources as $source) 
        {
            $localeFile = $this->getClientStorageFile($locale, $source);
            if(!file_exists($localeFile)) {
                continue;
            }

            $strings = IniHelper::createFromFile($localeFile)->toArray();

            foreach($strings as $hash => $text)
            {
                $result[$hash] = (string)$text;
            }
        }
        
        return $result;
    }
}