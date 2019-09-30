<?php
/**
 * File containing the {@link Localization_Parser} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Parser
 */

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
    * Parses a source file.
    * 
    * @param string $path
    * @return null|Localization_Parser_Language
    */
    public function parseFile($path)
    {
        $ext = strtolower(\AppUtils\FileHelper::getExtension($path));
        if(!isset($this->languageMappings[$ext])) {
            return null;
        }
        
        $language = $this->createLanguage($this->languageMappings[$ext]);
        
        $language->parse(file_get_contents($path));
        
        return $language;
    }
        
   /**
    * @var Localization_Parser_Language[]
    */
    protected $parsers = array();
    
   /**
    * Creates a parser for the specified language, e.g. "PHP".
    * @param string $language
    * @return Localization_Parser_Language
    */
    protected function createLanguage($language)
    {
        if(!isset($this->parsers[$language])) 
        {
            $class = '\AppLocalize\Localization_Parser_Language_'.$language;
        
            $this->parsers[$language] = new $class($this);
        }
        
        return $this->parsers[$language];
    }
    
    public function isExtensionSupported($ext)
    {
        $ext = strtolower($ext);
        
        return isset($this->languageMappings[$ext]);
    }

    /**
     * Checks if the target file is a file that can be
     * parsed, which means that is must not be in the
     * exclude list and have a supported file extension.
     *
     * @param string $file
     * @return boolean
     */
    public function isFileSupported($file)
    {
        return $this->isExtensionSupported(\AppUtils\FileHelper::getExtension($file));
    }
}