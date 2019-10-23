<?php
/**
 * File containing the {@link Localization_Parser} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Parser
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * File-based parsing engine that extracts translateable
 * application strings from PHP and Javascript code.
 *
 * Uses the built-in PHP tokenizer and Tim Whitlock's
 * jtokenizer for parsing javascript files. 
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Localization_Parser
{
    const ERROR_INVALID_LANGUAGE_ID = 40601;
    
    const ERROR_UNSUPPORTED_FILE_EXTENSION = 40602;
    
   /**
    * @var Localization_Scanner
    */
    protected $scanner;
    
   /**
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;
    
    protected $languageMappings = array(
        'js' => 'Javascript',
        'php' => 'PHP'
    );
    
    public function __construct(Localization_Scanner $scanner)
    {
        $this->scanner = $scanner;
        $this->collection = $scanner->getCollection();
    }
    
   /**
    * Parses a source file. Must have a valid supported file extension.
    * 
    * @param string $path
    * @return Localization_Parser_Language
    * @throws Localization_Exception
    * 
    * @see Localization_Parser::ERROR_UNSUPPORTED_FILE_EXTENSION
    */
    public function parseFile(string $path) : Localization_Parser_Language
    {
        $this->requireValidFile($path);
         
        $ext = \AppUtils\FileHelper::getExtension($path);
        
        $language = $this->createLanguage($this->languageMappings[$ext]);
        
        $language->parseFile($path);
        
        return $language;
    }
    
   /**
    * Parses the string for the specified language.
    * 
    * @param string $languageID
    * @param string $code
    * @return Localization_Parser_Language
    * @throws Localization_Exception
    * 
    * @see Localization_Parser::ERROR_INVALID_LANGUAGE_ID
    */
    public function parseString(string $languageID, string $code) : Localization_Parser_Language
    {
        $this->requireValidLanguageID($languageID);
        
        $language = $this->createLanguage($languageID);
        
        $language->parseString($code);
        
        return $language;
    }
    
    protected function requireValidFile(string $path)
    {
        $ext = \AppUtils\FileHelper::getExtension($path);
        
        if($this->isExtensionSupported($ext)) {
            return;
        }
        
        throw new Localization_Exception(
            sprintf('Unsupported file extension [%s].', $ext),
            sprintf(
                'Tried parsing the file [%s].',
                $path
            ),
            self::ERROR_UNSUPPORTED_FILE_EXTENSION
        );
    }
    
    protected function requireValidLanguageID(string $languageID)
    {
        $values = $this->getLanguageIDs();
        
        if(in_array($languageID, $values)) {
            return;
        }

        throw new Localization_Exception(
            'Unknown language ID',
            sprintf(
                'The language ID [%s] is not a known ID. Valid IDs are: [%s].',
                $languageID,
                implode(', ', $values)
            ),
            self::ERROR_INVALID_LANGUAGE_ID
        );
    }
    
   /**
    * Retrieves a list of all language IDs that are supported.
    * @return string[] IDs list like "PHP", "Javascript"
    */
    public function getLanguageIDs() : array
    {
        return array_values($this->languageMappings);
    }
    
   /**
    * @var Localization_Parser_Language[]
    */
    protected $languageParsers = array();
    
   /**
    * Creates a parser for the specified language, e.g. "PHP".
    * NOTE: Existing parser instances are re-used.
    * 
    * @param string $languageID
    * @return Localization_Parser_Language
    * 
    * @see  Localization_Parser::ERROR_INVALID_LANGUAGE_ID
    */
    public function createLanguage(string $languageID)
    {
        $this->requireValidLanguageID($languageID);
        
        if(!isset($this->languageParsers[$languageID])) 
        {
            $class = '\AppLocalize\Localization_Parser_Language_'.$languageID;
        
            $this->languageParsers[$languageID] = new $class($this);
        }
        
        return $this->languageParsers[$languageID];
    }
    
   /**
    * Whether the specified file extension is supported.
    * 
    * @param string $ext
    * @return bool
    */
    public function isExtensionSupported(string $ext) : bool
    {
        $ext = strtolower($ext);
        
        return isset($this->languageMappings[$ext]);
    }

    /**
     * Checks if the target file is a file that can be
     * parsed, which means that it must have a supported 
     * file extension.
     *
     * @param string $file
     * @return boolean
     */
    public function isFileSupported($file)
    {
        return $this->isExtensionSupported(\AppUtils\FileHelper::getExtension($file));
    }
}