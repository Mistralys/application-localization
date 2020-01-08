<?php

declare(strict_types=1);

namespace AppLocalize;

abstract class Localization_Parser_Language
{
    const ERROR_SOURCE_FILE_NOT_FOUND = 40501;
    
    const ERROR_FAILED_READING_SOURCE_FILE = 40502;
    
    protected $debug = false;
    
    /**
     * @var Localization_Parser
     */
    protected $parser;

   /**
    * The function names that are included in the search.
    * @var array
    */
    protected $functionNames = array();
    
   /**
    * The tokens definitions.
    * @var array
    */
    protected $tokens = array();
    
   /**
    * The total amount of tokens found in the content.
    * @var integer
    */
    protected $totalTokens = 0;
    
   /**
    * All texts that have been collected.
    * @var array
    */
    protected $texts = array();
    
   /**
    * @var string
    */
    protected $content = '';

   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var array
    */
    protected $warnings = array();
    
   /**
    * The source file that was parsed (if any)
    * @var string
    */
    protected $sourceFile = '';
    
    public function __construct(Localization_Parser $parser)
    {
        $this->parser = $parser;
        $this->functionNames = $this->getFunctionNames();
    }
    
    abstract protected function getTokens() : array;
    
   /**
    * Retrieves the ID of the language.
    * @return string E.g. "PHP", "Javascript"
    */
    public function getID() : string
    {
        if(!isset($this->id)) {
            $this->id = str_replace('AppLocalize\Localization_Parser_Language_', '', get_class($this));
        }
        
        return $this->id;
    }
    
    public function hasSourceFile() : bool
    {
        return !empty($this->sourceFile);
    }
    
    public function getSourceFile() : string
    {
        return $this->sourceFile;
    }
    
   /**
    * Parses the code from a file.
    * 
    * @param string $path
    * @throws Localization_Exception
    */
    public function parseFile(string $path) : void
    {
        if(!file_exists($path)) 
        {
            throw new Localization_Exception(
                sprintf('Source code file [%s] not found', basename($path)),
                sprintf(
                    'Tried looking for the file in path [%s].',
                    $path
                ),
                self::ERROR_SOURCE_FILE_NOT_FOUND
            );
        }
        
        $this->sourceFile = $path;
        $this->content = file_get_contents($path);
        
        if($this->content !== false) {
            $this->parse();
            return;
        }
        
        throw new Localization_Exception(
            sprintf('Source code file [%s] could not be read', basename($path)),
            sprintf(
                'Tried opening the file located at [%s].',
                $path
            ),
            self::ERROR_FAILED_READING_SOURCE_FILE
        );
    }
    
   /**
    * Parses a source code string.
    * @param string $content
    */
    public function parseString($content) : void
    {
        $this->content = $content;
        $this->sourceFile = '';
        
        $this->parse();
    }
    
    protected function parse()
    {
        $this->texts = array();
        $this->warnings = array();
        $this->tokens = $this->getTokens();
        $this->totalTokens = count($this->tokens);
        
        for($i = 0; $i < $this->totalTokens; $i++)
        {
            $token = $this->createToken($this->tokens[$i]);
            
            if($token->isTranslationFunction()) {
                $this->parseToken($i+1, $token);
            }
        }
    }
    
    public function getTexts()
    {
        return $this->texts;
    }
    
    protected function addResult($text, $line=null)
    {
        $this->log(sprintf('Line [%1$s] | Found string [%2$s]', $line, $text));
        
        $this->texts[] = array(
            'text' => $text, 
            'line' => $line
        );
    }

   /**
    * Retrieves a list of all the function names that are
    * used as translation functions in the language.
    * @return array
    */
    public function getFunctionNames() : array
    {
        return $this->createToken('dummy')->getFunctionNames();
    }

    protected function log($message)
    {
        Localization::log(sprintf('%1$s parser | %2$s', $this->getID(), $message));
    }

   /**
    * Adds a warning message when a text cannot be parsed correctly for some reason.
    * 
    * @param Localization_Parser_Token $token
    * @param string $message
    * @return Localization_Parser_Warning
    */
    protected function addWarning(Localization_Parser_Token $token, string $message) : Localization_Parser_Warning
    {
        $warning = new Localization_Parser_Warning($this, $token, $message);
        
        $this->warnings[] = $warning;
        
        return $warning;
    }
    
   /**
    * Whether any warnings were generated during parsing.
    * @return bool
    */
    public function hasWarnings() : bool
    {
        return !empty($this->warnings);
    }
    
   /**
    * Retrieves all warnings that were generated during parsing,
    * if any.
    * 
    * @return Localization_Parser_Warning[]
    */
    public function getWarnings()
    {
        return $this->warnings;
    }
    
   /**
    * Creates a token instance: this retrieves information on
    * the language token being parsed.
    * 
    * @param array|string $definition The token definition.
    * @param Localization_Parser_Token $parentToken
    * @return Localization_Parser_Token
    */
    protected function createToken($definition, Localization_Parser_Token $parentToken=null) : Localization_Parser_Token
    {
        $class = '\AppLocalize\Localization_Parser_Token_'.$this->getID();
        
        return new $class($definition, $parentToken);
    }

   /**
    * Parses a translation function token.
    * 
    * @param int $number
    * @param Localization_Parser_Token $token
    */
    protected function parseToken(int $number, Localization_Parser_Token $token)
    {
        $textParts = array();
        $max = $number + 200;
        $open = false;
        
        for($i = $number; $i < $max; $i++)
        {
            if(!isset($this->tokens[$i])) {
                break;
            }
            
            $subToken = $this->createToken($this->tokens[$i], $token);
            
            if(!$open && $subToken->isOpeningFuncParams())
            {
                $open = true;
                continue;
            }
            
            if($open && $subToken->isClosingFuncParams()) {
                break;
            }
            
            // additional parameters in the translation function, we don't want to capture these now.
            if($open && $subToken->isArgumentSeparator()) {
                break;
            }
            
            if($open && $subToken->isEncapsedString())
            {
                $textParts[] = $this->trimText($subToken->getValue());
                continue;
            }
            
            if($open && $subToken->isVariableOrFunction()) {
                $textParts = null;
                $this->addWarning($subToken, t('Variables or functions are not supported in translation functions.'));
                break;
            }
        }
        
        if(empty($textParts)) {
            return;
        }
        
        $text = implode('', $textParts);
        
        $this->addResult($text, $token->getLine());
    }
    
    protected function debug($text)
    {
        if($this->debug) {
            echo $text;
        }
    }

    /**
     * Used to trim the text from the code. Also strips slashes
     * from the text, as it comes raw from the code.
     *
     * @param string $text
     * @return string
     */
    public function trimText($text)
    {
        return stripslashes(trim($text, "'\""));
    }
}