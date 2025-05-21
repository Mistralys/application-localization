<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Scanner;

use AppLocalize\Localization;
use AppLocalize\Localization\Parser\Text;
use AppLocalize\Localization\Scanner\StringCollection;

/**
 * Container for a single string hash: collects all instances
 * of the same string hash across all places where the same
 * string has been found.
 * 
 * @package Localization
 * @subpackage Scanner
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @phpstan-import-type SerializedStringInfo from StringInfo
 * @phpstan-type SerializedStringHash array<int,SerializedStringInfo>
 */
class StringHash
{
    protected StringCollection $collection;
    protected string $hash;
    
   /**
    * @var StringInfo[]
    */
    protected array $strings = array();

    /**
     * @var array<string,bool>
     */
    protected array $sourceIDs = array();
    
    public function __construct(StringCollection $collection, string $hash)
    {
        $this->collection = $collection;
        $this->hash = $hash;
    }
    
    public function addString(StringInfo $string) : StringHash
    {
        $this->strings[] = $string;
        $this->sourceIDs[$string->getSourceID()] = true;
        
        return $this;
    }

    /**
     * @return SerializedStringHash
     */
    public function toArray() : array
    {
        $entries = array();
       
        foreach($this->strings as $string) {
            $entries[] = $string->toArray();
        }
        
        return $entries;
    }
    
   /**
    * Retrieves all individual string locations where this text was found.
    * @return StringInfo[]
    */
    public function getStrings() : array
    {
        return $this->strings;
    }
    
    public function countFiles() : int
    {
        $amount = 0;
        
        foreach($this->strings as $string) {
            if($string->isFile()) {
                $amount++;
            }
        }
        
        return $amount;
    }
    
    public function hasSourceID(string $id) : bool
    {
        return isset($this->sourceIDs[$id]);
    }
    
    public function hasLanguageType(string $type) : bool
    {
        foreach($this->strings as $string) {
            if($string->getLanguageType() === $type) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getText() : ?Text
    {
        if(isset($this->strings[0])) {
            return $this->strings[0]->getText();
        }
        
        return null;
    }
    
    public function getHash() : string
    {
        return $this->hash;
    }
    
    public function isTranslated() : bool
    {
        return Localization::getTranslator()->hashExists($this->getHash());
    }
    
    public function countStrings() : int
    {
        return count($this->strings);
    }
    
   /**
    * Retrieves the translated text, if any.
    * @return string
    */
    public function getTranslatedText() : string
    {
        return Localization::getTranslator()->getHashTranslation($this->getHash()) ?? '';
    }
    
   /**
    * Retrieves a list of all file names, with relative paths.
    * @return string[]
    */
    public function getFiles() : array
    {
        $files = array();
        
        foreach($this->strings as $string) 
        {
            if(!$string->isFile()) {
                continue;
            }
            
            $file = $string->getSourceFile();
            if(!in_array($file, $files, true)) {
                $files[] = $file;
            }
        }
        
        sort($files);
        
        return $files;
    }
    
   /**
    * Retrieves a list of all file names this string is used in.
    * @return string[]
    */
    public function getFileNames() : array
    {
        $files = $this->getFiles();
        $result = array();
        
        foreach($files as $path) {
            $result[] = basename($path);
        }
        
        // some files may have the same name, there's no
        // sense in using duplicates in this context.
        return array_unique($result);
    }
    
   /**
    * Retrieves a text made up of all strings that are relevant
    * for a full text search, imploded together. Used in the search
    * function to find matching strings.
    * 
    * @return string
    */
    public function getSearchString() : string
    {
        $parts = array($this->getTranslatedText(), $this->getTextAsString());
        
        $parts = array_merge($parts, $this->getFiles());
        
        return implode(' ', $parts);
    }

    public function getTextAsString() : string
    {
        $text = $this->getText();

        if($text !== null)
        {
            return $text->getText();
        }

        return '';
    }
}
