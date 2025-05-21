<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Scanner;

use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Parser\ParserWarning;
use AppLocalize\Localization\Parser\Text;
use AppLocalize\Localization\Scanner\CollectionWarning;

/**
 * @phpstan-import-type SerializedWarning from ParserWarning
 * @phpstan-import-type SerializedStringHash from StringHash
 * @phpstan-type SerializedStringCollection array{formatVersion:int,hashes:array<int,SerializedStringHash>,warnings:array<int,SerializedWarning>}
 */
class StringCollection
{
    public const ERROR_UNKNOWN_STRING_HASH = 39201;
    
    public const SOURCE_FILE = 'file';
    
    public const STORAGE_FORMAT_VERSION = 2;
    
    protected LocalizationScanner $scanner;
    
   /**
    * @var StringHash[]
    */
    protected array $hashes = array();
    
   /**
    * @var array<int,SerializedWarning>
    */
    protected array $warnings = array();
    
    public function __construct(LocalizationScanner $scanner)
    {
        $this->scanner = $scanner;
    }
    
    public function addFromFile(string $sourceID, string $relativePath, string $languageType, Text $text) : void
    {
        $string = $this->createString($sourceID, self::SOURCE_FILE, $text);
        
        $string->setProperty(StringInfo::PROPERTY_LANGUAGE_TYPE, $languageType);
        $string->setProperty(StringInfo::PROPERTY_RELATIVE_PATH, $relativePath);
        
        $this->add($string);
    }
    
    public function addWarning(ParserWarning $warning) : void
    {
        $this->warnings[] = $warning->toArray();
    }
    
    protected function createString(string $sourceID, string $sourceType, Text $text) : StringInfo
    {
        return new StringInfo($this, $sourceID, $sourceType, $text);
    }
    
   /**
    * Adds a single translatable string.
    * 
    * @param StringInfo $string
    * @return StringCollection
    */
    protected function add(StringInfo $string) : StringCollection
    {
        $hash = $string->getHash();
        
        if(!isset($this->hashes[$hash])) {
            $this->hashes[$hash] = new StringHash($this, $hash);
        }
        
        $this->hashes[$hash]->addString($string);
        return $this;
    }
    
   /**
    * Retrieves all available translatable strings,
    * grouped by their hash to identify unique strings.
    * 
    * @return StringHash[]
    */
    public function getHashes() : array
    {
        return array_values($this->hashes);
    }
    
    public function hashExists(string $hash) : bool
    {
        return isset($this->hashes[$hash]);
    }

    /**
     * @param string $hash
     * @return StringHash
     * @throws LocalizationException
     */
    public function getHash(string $hash) : StringHash
    {
        if(isset($this->hashes[$hash])) {
            return $this->hashes[$hash];
        }
        
        throw new LocalizationException(
            'Unknown string hash',
            sprintf('Could not find string by hash [%s].', $hash),
            self::ERROR_UNKNOWN_STRING_HASH
        );
    }

    /**
     * @return SerializedStringCollection
     */
    public function toArray() : array
    {
        $data = array(
            'formatVersion' => self::STORAGE_FORMAT_VERSION,
            'hashes' => array(),
            'warnings' => $this->warnings
        );
        
        foreach($this->hashes as $hash)
        {
            $data['hashes'][] = $hash->toArray();
        }
        
        return $data;
    }

    /**
     * @param array<int|string,mixed> $array
     * @return bool
     */
    public function fromArray(array $array) : bool
    {
        if(!isset($array['formatVersion']) || $array['formatVersion'] !== self::STORAGE_FORMAT_VERSION) {
            return false;
        }
        
        foreach($array['hashes'] as $entry) 
        {
            $string = StringInfo::fromArray($this, $entry);
            $this->add($string);
        }
        
        $this->warnings = $array['warnings'];
        
        return true;
    }
    
   /**
    * Whether the parser reported warnings during the
    * search for translatable texts.
    * 
    * @return bool
    */
    public function hasWarnings() : bool
    {
        return !empty($this->warnings);
    }
    
   /**
    * Retrieves the number of warnings.
    * @return int
    */
    public function countWarnings() : int
    {
        return count($this->warnings);
    }
    
   /**
    * Retrieves all warning messages added during
    * the search for translatable texts, if any.
    * 
    * @return CollectionWarning[]
    */
    public function getWarnings() : array
    {
        $result = array();
        
        foreach($this->warnings as $def) {
            $result[] = new CollectionWarning($def);
        }
        
        return $result;
    }
    
    public function countHashes() : int
    {
        return count($this->hashes);
    }
    
    public function countFiles() : int
    {
        $amount = 0;
        foreach($this->hashes as $hash) {
            $amount = $amount + $hash->countFiles();
        }
        
        return $amount;
    }
    
   /**
    * Retrieves all string hashed for the specified source.
    * 
    * @param string $id
    * @return StringHash[]
    */
    public function getHashesBySourceID(string $id) : array
    {
        $hashes = array();
        
        foreach($this->hashes as $hash) {
            if($hash->hasSourceID($id)) {
                $hashes[] = $hash;
            }
        }
        
        return $hashes;
    }
    
   /**
    * Retrieves all hashes for the specified language ID.
    * 
    * @param string $languageID The language ID, e.g. "PHP"
    * @return StringHash[]
    */
    public function getHashesByLanguageID(string $languageID) : array
    {
        $hashes = array();
        
        foreach($this->hashes as $hash) {
            if($hash->hasLanguageType($languageID)) {
                $hashes[] = $hash;
            }
        }
        
        return $hashes;
    }
}