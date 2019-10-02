<?php

namespace AppLocalize;

/**
 * Container for a single string hash: collects all instances
 * of the same string hash accross all places where the same
 * string has been found.
 * 
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Localization_Scanner_StringHash
{
   /**
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;
    
   /**
    * @var string
    */
    protected $hash;
    
   /**
    * @var Localization_Scanner_StringInfo[]
    */
    protected $strings = array();
    
    protected $sourceIDs = array();
    
    public function __construct(Localization_Scanner_StringsCollection $collection, $hash)
    {
        $this->collection = $collection;
        $this->hash = $hash;
    }
    
    public function addString(Localization_Scanner_StringInfo $string)
    {
        $this->strings[] = $string;
        $this->sourceIDs[$string->getSourceID()] = true;
    }
    
    public function toArray()
    {
        $entries = array();
       
        foreach($this->strings as $string) {
            $entries[] = $string->toArray();
        }
        
        return $entries;
    }
    
    public function getStrings()
    {
        return $this->strings;
    }
    
    public function countFiles()
    {
        $amount = 0;
        
        foreach($this->strings as $string) {
            if($string->isFile()) {
                $amount++;
            }
        }
        
        return $amount;
    }
    
    public function hasSourceID($id)
    {
        return isset($this->sourceIDs[$id]);
    }
    
    public function hasLanguageType($type)
    {
        foreach($this->strings as $string) {
            if($string->getLanguageType() == $type) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getText()
    {
        if(isset($this->strings[0])) {
            return $this->strings[0]->getText();
        }
        
        return '';
    }
    
    public function getHash()
    {
        return $this->hash;
    }
    
    public function isTranslated()
    {
        $translator = Localization::getTranslator();
        return $translator->hashExists($this->getHash());
    }
    
    public function countStrings()
    {
        return count($this->strings);
    }
    
    public function getTranslatedText()
    {
        $translator = Localization::getTranslator();
        return $translator->getHashTranslation($this->getHash());
    }
    
   /**
    * Retrieves a list of all file names, with relative paths.
    * @return string[]
    */
    public function getFiles()
    {
        $files = array();
        
        foreach($this->strings as $string) 
        {
            if(!$string->isFile()) {
                continue;
            }
            
            $file = $string->getSourceFile();
            if(!in_array($file, $files)) {
                $files[] = $file;
            }
        }
        
        return $files;
    }
    
    public function getSearchString()
    {
        $parts = array($this->getTranslatedText(), $this->getText());
        
        $parts = array_merge($parts, $this->getFiles());
        
        return implode(' ', $parts);
    }
}
