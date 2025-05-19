<?php

namespace AppLocalize;

use AppLocalize\Localization\Parser\Text;

class Localization_Scanner_StringInfo
{
    const SERIALIZED_SOURCE_TYPE = 'sourceType';
    const SERIALIZED_SOURCE_ID = 'sourceID';
    const SERIALIZED_TEXT = 'text';
    const SERIALIZED_PROPERTIES = 'properties';

    /**
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;

    /**
     * @var array<string,mixed>
     */
    protected $properties = array();

    /**
     * @var string
     */
    protected $sourceID;

    /**
     * @var string
     */
    protected $sourceType;

    /**
     * @var Text
     */
    protected $text;
    
    public function __construct(Localization_Scanner_StringsCollection $collection, string $sourceID, string $sourceType, Text $text)
    {
        $this->collection = $collection;
        $this->sourceID = $sourceID;
        $this->sourceType = $sourceType;
        $this->text = $text;
    }
    
    public function getHash() : string
    {
        return $this->text->getHash();
    }
    
    public function getSourceID() : string
    {
        return $this->sourceID;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setProperty(string $name, $value) : Localization_Scanner_StringInfo
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    public function isFile() : bool
    {
        return $this->sourceType == Localization_Scanner_StringsCollection::SOURCE_FILE;
    }
    
    public function isJavascript() : bool
    {
        return $this->getProperty('languageType') == 'Javascript';
    }
    
    public function isPHP() : bool
    {
        return $this->getProperty('languageType') == 'PHP';
    }
    
    public function getSourceFile() : string
    {
        return strval($this->getProperty('relativePath'));
    }
    
    public function getLanguageType() : string
    {
        return strval($this->getProperty('languageType'));
    }
    
    public function getProperty(string $name) : ?string
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        
        return null;
    }
    
    public function getSourceType() : string
    {
        return $this->sourceType;
    }
    
    public function getText() : Text
    {
        return $this->text;
    }
    
    public function getLine() : int
    {
        return $this->text->getLine();
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        return array(
            self::SERIALIZED_SOURCE_TYPE => $this->getSourceType(),
            self::SERIALIZED_SOURCE_ID => $this->getSourceID(),
            self::SERIALIZED_TEXT => $this->text->toArray(),
            self::SERIALIZED_PROPERTIES => $this->getProperties()
        );
    }
    
    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * @param Localization_Scanner_StringsCollection $collection
     * @param array<string,mixed> $array
     * @return Localization_Scanner_StringInfo
     */
    public static function fromArray(Localization_Scanner_StringsCollection $collection, array $array) : Localization_Scanner_StringInfo
    {
        $string = new Localization_Scanner_StringInfo(
            $collection, 
            $array[self::SERIALIZED_SOURCE_ID],
            $array[self::SERIALIZED_SOURCE_TYPE],
            Text::fromArray($array[self::SERIALIZED_TEXT])
        );
        
        foreach($array[self::SERIALIZED_PROPERTIES] as $name => $value) {
            $string->setProperty($name, $value);
        }
        
        return $string;
    }
}
