<?php

namespace AppLocalize;

class Localization_Scanner_StringInfo
{
   /**
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;
    
    protected $properties = array();
    
    protected $sourceID;
    
    protected $sourceType;
    
    protected $text;
    
    protected $line;
    
    protected $hash;
    
    public function __construct(Localization_Scanner_StringsCollection $collection, $sourceID, $sourceType, $text, $line)
    {
        $this->collection = $collection;
        $this->sourceID = $sourceID;
        $this->sourceType = $sourceType;
        $this->text = $text;
        $this->line = $line;
        $this->hash = md5($text);
    }
    
    public function getHash()
    {
        return $this->hash;
    }
    
    public function getSourceID()
    {
        return $this->sourceID;
    }
    
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    public function isFile()
    {
        return $this->sourceType == Localization_Scanner_StringsCollection::SOURCE_FILE;
    }
    
    public function isJavascript()
    {
        return $this->getProperty('languageType') == 'Javascript';
    }
    
    public function isPHP()
    {
        return $this->getProperty('languageType') == 'PHP';
    }
    
    public function getSourceFile()
    {
        return $this->getProperty('relativePath');
    }
    
    public function getLanguageType()
    {
        return $this->getProperty('languageType');
    }
    
    public function getProperty($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        return null;
    }
    
    public function getSourceType()
    {
        return $this->sourceType;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function getLine()
    {
        return $this->line;
    }
    
    public function toArray()
    {
        return array(
            'sourceType' => $this->getSourceType(),
            'sourceID' => $this->getSourceID(),            
            'hash' => $this->getHash(),
            'text' => $this->getText(),
            'line' => $this->getLine(),
            'properties' => $this->getProperties()
        );
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public static function fromArray($collection, $array)
    {
        $string = new Localization_Scanner_StringInfo(
            $collection, 
            $array['sourceID'],
            $array['sourceType'],
            $array['text'],
            $array['line']
        );
        
        foreach($array['properties'] as $name => $value) {
            $string->setProperty($name, $value);
        }
        
        return $string;
    }
}