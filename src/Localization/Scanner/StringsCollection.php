<?php

namespace AppLocalize;

use AppLocalize\Parser\Text;

class Localization_Scanner_StringsCollection
{
    const ERROR_UNKNOWN_STRING_HASH = 39201;
    
    const SOURCE_FILE = 'file';
    
    const STORAGE_FORMAT_VERSION = 2;
    
   /**
    * @var Localization_Scanner
    */
    protected $scanner;
    
   /**
    * @var Localization_Scanner_StringHash[]
    */
    protected $hashes = array();
    
   /**
    * @var array
    */
    protected $warnings = array();
    
    public function __construct(Localization_Scanner $scanner)
    {
        $this->scanner = $scanner;
    }
    
    public function addFromFile(string $sourceID, string $relativePath, string $languageType, Text $text) : void
    {
        $string = $this->createString($sourceID, self::SOURCE_FILE, $text);
        
        $string->setProperty('languageType', $languageType);
        $string->setProperty('relativePath', $relativePath);
        
        $this->add($string);
    }
    
    public function addWarning(Localization_Parser_Warning $warning) : void
    {
        $this->warnings[] = $warning->toArray();
    }
    
    protected function createString(string $sourceID, string $sourceType, Text $text) : Localization_Scanner_StringInfo
    {
        return new Localization_Scanner_StringInfo($this, $sourceID, $sourceType, $text);
    }
    
   /**
    * Adds a single translatable string.
    * 
    * @param Localization_Scanner_StringInfo $string
    * @return Localization_Scanner_StringsCollection
    */
    protected function add(Localization_Scanner_StringInfo $string) : Localization_Scanner_StringsCollection
    {
        $hash = $string->getHash();
        
        if(!isset($this->hashes[$hash])) {
            $this->hashes[$hash] = new Localization_Scanner_StringHash($this, $hash);
        }
        
        $this->hashes[$hash]->addString($string);
        return $this;
    }
    
   /**
    * Retrieves all available translatable strings,
    * grouped by their hash to identify unique strings.
    * 
    * @return Localization_Scanner_StringHash[]
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
     * @return Localization_Scanner_StringHash
     * @throws Localization_Exception
     */
    public function getHash(string $hash) : Localization_Scanner_StringHash
    {
        if(isset($this->hashes[$hash])) {
            return $this->hashes[$hash];
        }
        
        throw new Localization_Exception(
            'Unknown string hash',
            sprintf('Could not find string by hash [%s].', $hash),
            self::ERROR_UNKNOWN_STRING_HASH
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        $data = array(
            'formatVersion' => self::STORAGE_FORMAT_VERSION,
            'hashes' => array(),
            'warnings' => array()
        );
        
        foreach($this->hashes as $hash)
        {
            $data['hashes'] = array_merge($data['hashes'], $hash->toArray());
        }
        
        $data['warnings'] = $this->warnings;
        
        return $data;
    }

    /**
     * @param array<string,mixed> $array
     * @return bool
     */
    public function fromArray(array $array) : bool
    {
        if(!isset($array['formatVersion']) || $array['formatVersion'] != self::STORAGE_FORMAT_VERSION) {
            return false;
        }
        
        foreach($array['hashes'] as $entry) 
        {
            $string = Localization_Scanner_StringInfo::fromArray($this, $entry);
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
    * Retrieves the amount of warnings.
    * @return int
    */
    public function countWarnings() : int
    {
        return count($this->warnings);
    }
    
   /**
    * Retrieves all warning messages that were added
    * during the search for translatable texts, if any.
    * 
    * @return Localization_Scanner_StringsCollection_Warning[]
    */
    public function getWarnings() : array
    {
        $result = array();
        
        foreach($this->warnings as $def) {
            $result[] = new Localization_Scanner_StringsCollection_Warning($def);
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
    * @return Localization_Scanner_StringHash[]
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
    * @return Localization_Scanner_StringHash[]
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