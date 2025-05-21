<?php
/**
 * @package Localization
 * @subpackage Parser
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Parser;

use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Parser\Language\JavaScriptLanguage;
use AppLocalize\Localization\Parser\Language\PHPLanguage;
use AppLocalize\Localization\Scanner\LocalizationScanner;
use AppLocalize\Localization_Scanner_StringsCollection;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper;
use function AppUtils\parseVariable;

/**
 * File-based parsing engine that extracts translatable
 * application strings from PHP and JavaScript code.
 *
 * Uses the built-in PHP tokenizer and Tim Whitlock's
 * jtokenizer for parsing JavaScript files.
 *
 * @package Localization
 * @subpackage Parser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LocalizationParser
{
    public const ERROR_INVALID_LANGUAGE_ID = 40601;
    public const ERROR_UNSUPPORTED_FILE_EXTENSION = 40602;
    public const ERROR_INVALID_LANGUAGE_CLASS = 40603;
    
    protected LocalizationScanner $scanner;
    protected Localization_Scanner_StringsCollection $collection;

    /**
     * @var array<string,class-string<BaseLanguage>>
     */
    protected array $extensionMappings = array(
        'js' => JavaScriptLanguage::class,
        'php' => PHPLanguage::class
    );

    /**
     * @var array<string,class-string<BaseLanguage>>
     */
    protected array $idMappings = array(
        JavaScriptLanguage::LANGUAGE_ID => JavaScriptLanguage::class,
        PHPLanguage::LANGUAGE_ID => PHPLanguage::class
    );

    public function __construct(LocalizationScanner $scanner)
    {
        $this->scanner = $scanner;
        $this->collection = $scanner->getCollection();
    }
    
   /**
    * Parses a source file. Must have a valid supported file extension.
    * 
    * @param string $path
    * @return BaseLanguage
    * @throws LocalizationException
    * 
    * @see LocalizationParser::ERROR_UNSUPPORTED_FILE_EXTENSION
    */
    public function parseFile(string $path) : BaseLanguage
    {
        $this->requireValidFile($path);
         
        $ext = FileHelper::getExtension($path);
        
        $language = $this->createLanguageInstance($this->extensionMappings[$ext]);
        
        $language->parseFile($path);
        
        return $language;
    }
    
   /**
    * Parses the string for the specified language.
    * 
    * @param string $languageID
    * @param string $code
    * @return BaseLanguage
    * @throws LocalizationException
    * 
    * @see LocalizationParser::ERROR_INVALID_LANGUAGE_ID
    */
    public function parseString(string $languageID, string $code) : BaseLanguage
    {
        $this->requireValidLanguageID($languageID);
        
        $language = $this->createLanguage($languageID);
        
        $language->parseString($code);
        
        return $language;
    }

    /**
     * @param string $path
     * @throws LocalizationException
     * @see LocalizationParser::ERROR_UNSUPPORTED_FILE_EXTENSION
     */
    protected function requireValidFile(string $path) : void
    {
        $ext = FileHelper::getExtension($path);
        
        if($this->isExtensionSupported($ext)) {
            return;
        }
        
        throw new LocalizationException(
            sprintf('Unsupported file extension [%s].', $ext),
            sprintf(
                'Tried parsing the file [%s].',
                $path
            ),
            self::ERROR_UNSUPPORTED_FILE_EXTENSION
        );
    }

    /**
     * @param string $languageID
     * @return class-string<BaseLanguage>
     * @throws LocalizationException {@see LocalizationParser::ERROR_INVALID_LANGUAGE_ID}
     */
    protected function requireValidLanguageID(string $languageID) : string
    {
        $languageID = strtolower($languageID);

        if(isset($this->extensionMappings[$languageID])) {
            return $this->extensionMappings[$languageID];
        }

        foreach($this->idMappings as $validID => $class) {
            if(strtolower($validID) === $languageID) {
                return $class;
            }
        }

        throw new LocalizationException(
            'Unknown language ID',
            sprintf(
                'The language ID [%s] is not a known ID. Valid IDs are: [%s].',
                $languageID,
                implode(', ', $this->getLanguageIDs())
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
        return array_keys($this->extensionMappings);
    }
    
   /**
    * @var array<string,BaseLanguage>
    */
    protected array $languageParsers = array();

    /**
     * Creates a parser for the specified language, e.g. "PHP".
     *
     * > NOTE: Existing parser instances are re-used.
     *
     * @param string $languageID
     * @return BaseLanguage
     *
     * @throws LocalizationException
     * @see LocalizationParser::ERROR_INVALID_LANGUAGE_ID
     * @see LocalizationParser::ERROR_INVALID_LANGUAGE_CLASS
     */
    public function createLanguage(string $languageID) : BaseLanguage
    {
        $class = $this->requireValidLanguageID($languageID);
        
        if(!isset($this->languageParsers[$languageID])) 
        {
            $this->languageParsers[$languageID] = $this->createLanguageInstance($class);
        }
        
        return $this->languageParsers[$languageID];
    }

    /**
     * @param class-string<BaseLanguage>|string $class
     * @return BaseLanguage
     * @throws BaseClassHelperException
     */
    private function createLanguageInstance(string $class) : BaseLanguage
    {
        return ClassHelper::requireObjectInstanceOf(
            BaseLanguage::class,
            new $class($this)
        );
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
        
        return isset($this->extensionMappings[$ext]);
    }

    /**
     * Checks if the target file is a file that can be
     * parsed, which means that it must have a supported 
     * file extension.
     *
     * @param string $file
     * @return boolean
     */
    public function isFileSupported(string $file) : bool
    {
        return $this->isExtensionSupported(FileHelper::getExtension($file));
    }
}
