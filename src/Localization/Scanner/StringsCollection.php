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
        
        foreach($this->warnings as $warning)
        {
            $data['warnings'][] = $warning->toArray();
        }
        
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
    
    public function getWarnings() : array
    {
        return $this->warnings;
    }
    
    public function countHashes()
    {
        return count($this->hashes);
    }
    
    public function countFiles()
    {
        $amount = 0;
        foreach($this->hashes as $hash) {
            $amount = $amount + $hash->countFiles();
        }
        
        return $amount;
    }
    
    public function getHashesBySourceID($id)
    {
        $hashes = array();
        
        foreach($this->hashes as $hash) {
            if($hash->hasSourceID($id)) {
                $hashes[] = $hash;
            }
        }
        
        return $hashes;
    }
    
    public function getHashesByLanguageType($type)
    {
        $hashes = array();
        
        foreach($this->hashes as $hash) {
            if($hash->hasLanguageType($type)) {
                $hashes[] = $hash;
            }
        }
        
        return $hashes;
    }
}