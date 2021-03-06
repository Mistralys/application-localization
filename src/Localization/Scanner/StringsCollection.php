<?php

namespace AppLocalize;

class Localization_Scanner_StringsCollection
{
    const ERROR_UNKNOWN_STRING_HASH = 39201;
    
    const SOURCE_FILE = 'file';
    
    const STORAGE_FORMAT_VERSION = 1;
    
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
    
    public function addFromFile($sourceID, $relativePath, $languageType, $text, $line)
    {
        $string = $this->createString($sourceID, self::SOURCE_FILE, $text, $line);
        
        $string->setProperty('languageType', $languageType);
        $string->setProperty('relativePath', $relativePath);
        
        $this->add($string);
    }
    
    public function addWarning(Localization_Parser_Warning $warning)
    {
        $this->warnings[] = $warning->toArray();
    }
    
    protected function createString($sourceID, $sourceType, $text, $line)
    {
        $string = new Localization_Scanner_StringInfo($this, $sourceID, $sourceType, $text, $line);
        return $string;
    }
    
   /**
    * Adds a single translateable string.
    * 
    * @param Localization_Scanner_StringInfo $string
    * @return Localization_Scanner_StringsCollection
    */
    protected function add(Localization_Scanner_StringInfo $string)
    {
        $hash = $string->getHash();
        
        if(!isset($this->hashes[$hash])) {
            $this->hashes[$hash] = new Localization_Scanner_StringHash($this, $hash);
        }
        
        $this->hashes[$hash]->addString($string);
        return $this;
    }
    
   /**
    * Retrieves all available translateable strings, 
    * grouped by their hash to identify unique strings.
    * 
    * @return Localization_Scanner_StringHash[]
    */
    public function getHashes()
    {
        return array_values($this->hashes);
    }
    
    public function hashExists($hash)
    {
        return isset($this->hashes[$hash]);
    }
    
    public function getHash($hash)
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
    
    public function toArray()
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
    
    public function fromArray($array) : bool
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
    * search for translateable texts.
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
    * during the search for translateable texts, if any.
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
    * @return \AppLocalize\Localization_Scanner_StringHash[]
    */
    public function getHashesBySourceID(string $id) 
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
    * @return \AppLocalize\Localization_Scanner_StringHash[]
    */
    public function getHashesByLanguageID(string $languageID)
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