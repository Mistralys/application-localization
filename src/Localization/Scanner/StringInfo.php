<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Scanner;

use AppLocalize\Localization\Parser\Language\JavaScriptLanguage;
use AppLocalize\Localization\Parser\Language\PHPLanguage;
use AppLocalize\Localization\Parser\Text;

/**
 * @phpstan-import-type SerializedText from Text
 * @phpstan-type SerializedStringInfo array{sourceType:string, sourceID:string, text:SerializedText, properties:array<string,string>}
 */
class StringInfo
{
    public const SERIALIZED_SOURCE_TYPE = 'sourceType';
    public const SERIALIZED_SOURCE_ID = 'sourceID';
    public const SERIALIZED_TEXT = 'text';
    public const SERIALIZED_PROPERTIES = 'properties';
    public const PROPERTY_LANGUAGE_TYPE = 'languageType';
    public const PROPERTY_RELATIVE_PATH = 'relativePath';

    protected StringCollection $collection;
    protected string $sourceID;
    protected string $sourceType;
    protected Text $text;

    /**
     * @var array<string,string>
     */
    protected array $properties = array();

    public function __construct(StringCollection $collection, string $sourceID, string $sourceType, Text $text)
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
     * @param string $value
     * @return $this
     */
    public function setProperty(string $name, string $value) : StringInfo
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    public function isFile() : bool
    {
        return $this->sourceType === StringCollection::SOURCE_FILE;
    }
    
    public function isJavascript() : bool
    {
        return $this->getProperty(self::PROPERTY_LANGUAGE_TYPE) === JavaScriptLanguage::LANGUAGE_ID;
    }
    
    public function isPHP() : bool
    {
        return $this->getProperty(self::PROPERTY_LANGUAGE_TYPE) === PHPLanguage::LANGUAGE_ID;
    }
    
    public function getSourceFile() : string
    {
        return (string)$this->getProperty(self::PROPERTY_RELATIVE_PATH);
    }
    
    public function getLanguageType() : string
    {
        return (string)$this->getProperty(self::PROPERTY_LANGUAGE_TYPE);
    }
    
    public function getProperty(string $name) : ?string
    {
        return $this->properties[$name] ?? null;
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
     * @return SerializedStringInfo
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

    /**
     * @return array<string,string>
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * @param StringCollection $collection
     * @param SerializedStringInfo $array
     * @return StringInfo
     */
    public static function fromArray(StringCollection $collection, array $array) : StringInfo
    {
        $string = new StringInfo(
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
