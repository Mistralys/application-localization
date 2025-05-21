<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser;

use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use function AppLocalize\t;

/**
 * @phpstan-type RawParsedToken array{0:int|string, 1:string, 2:int}|string
 */
abstract class BaseLanguage
{
    public const ERROR_SOURCE_FILE_NOT_FOUND = 40501;
    public const ERROR_FAILED_READING_SOURCE_FILE = 40502;

    protected bool $debug = false;
    protected LocalizationParser $parser;

   /**
    * The function names that are included in the search.
    * @var string[]
    */
    protected array $functionNames = array();
    
   /**
    * The token definitions.
    * @var array<int,RawParsedToken>
    */
    protected array $tokens = array();
    
   /**
    * The total number of tokens found in the content.
    * @var integer
    */
    protected int $totalTokens = 0;
    
   /**
    * All texts that have been collected.
    * @var Text[]
    */
    protected array $texts = array();
    
   /**
    * @var string
    */
    protected string $content = '';

   /**
    * @var ParserWarning[]
    */
    protected array $warnings = array();
    
   /**
    * The source file that was parsed (if any)
    * @var string
    */
    protected string $sourceFile = '';

    /**
     * @var string[]
     */
    private static array $allowedContextTags = array(
        'br',
        'p',
        'strong',
        'em',
        'b',
        'i',
        'a',
        'code',
        'pre'
    );

    public function __construct(LocalizationParser $parser)
    {
        $this->parser = $parser;
        $this->functionNames = $this->getFunctionNames();
    }

    /**
     * @return array<int,RawParsedToken>
     */
    abstract protected function getTokens() : array;
    
   /**
    * Retrieves the ID of the language.
    * @return string E.g. "PHP", "Javascript"
    */
    abstract public function getID() : string;

    /**
     * @return class-string<BaseParsedToken>
     */
    abstract public function getTokenClass() : string;

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
    * @throws LocalizationException
    */
    public function parseFile(string $path) : void
    {
        if(!file_exists($path)) 
        {
            throw new LocalizationException(
                sprintf('Source code file [%s] not found', basename($path)),
                sprintf(
                    'Tried looking for the file in path [%s].',
                    $path
                ),
                self::ERROR_SOURCE_FILE_NOT_FOUND
            );
        }
        
        $this->sourceFile = $path;

        try
        {
            $this->content = FileHelper::readContents($path);
        }
        catch (FileHelper_Exception $e)
        {
            throw new LocalizationException(
                sprintf('Source code file [%s] could not be read', basename($path)),
                sprintf(
                    'Tried opening the file located at [%s].',
                    $path
                ),
                self::ERROR_FAILED_READING_SOURCE_FILE,
                $e
            );
        }
        
        $this->parse();
    }
    
   /**
    * Parses a source code string.
    * @param string $content
    */
    public function parseString(string $content) : void
    {
        $this->content = $content;
        $this->sourceFile = '';
        
        $this->parse();
    }
    
    protected function parse() : void
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

    /**
     * @return Text[]
     */
    public function getTexts() : array
    {
        return $this->texts;
    }

    /**
     * Retrieves a list of all tag names that may be used
     * in the translation context strings.
     *
     * @return string[]
     */
    public static function getAllowedContextTags() : array
    {
        return self::$allowedContextTags;
    }

    protected function addResult(string $text, int $line=0, string $explanation='') : void
    {
        $this->log(sprintf('Line [%1$s] | Found string [%2$s]', $line, $text));

        $explanation = strip_tags($explanation, '<'.implode('><', self::$allowedContextTags).'>');


        $this->texts[] = new Text($text, $line, $explanation);
    }

   /**
    * Retrieves a list of all the function names that are
    * used as translation functions in the language.
    * @return string[]
    */
    public function getFunctionNames() : array
    {
        return $this->createToken('dummy')->getFunctionNames();
    }

    protected function log(string $message) : void
    {
        Localization::log(sprintf('%1$s parser | %2$s', $this->getID(), $message));
    }

   /**
    * Adds a warning message when a text cannot be parsed correctly for some reason.
    * 
    * @param BaseParsedToken $token
    * @param string $message
    * @return ParserWarning
    */
    protected function addWarning(BaseParsedToken $token, string $message) : ParserWarning
    {
        $warning = new ParserWarning($this, $token, $message);
        
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
    * Retrieves all warnings generated during parsing,
    * if any.
    * 
    * @return ParserWarning[]
    */
    public function getWarnings() : array
    {
        return $this->warnings;
    }

    public function getWarningsAsString() : string
    {
        $result = '';
        foreach($this->warnings as $warning)
        {
            $result .= $warning->toString().PHP_EOL;
        }

        return $result;
    }

   /**
    * Creates a token instance: this retrieves information on
    * the language token being parsed.
    * 
    * @param RawParsedToken $definition The token definition.
    * @param BaseParsedToken|NULL $parentToken
    * @return BaseParsedToken
    */
    protected function createToken($definition, ?BaseParsedToken $parentToken=null) : BaseParsedToken
    {
        $class = $this->getTokenClass();
        return new $class($definition, $parentToken);
    }

   /**
    * Parses a translation function token.
    * 
    * @param int $number
    * @param BaseParsedToken $token
    */
    protected function parseToken(int $number, BaseParsedToken $token) : void
    {
        $textParts = array();
        $max = $number + 200;
        $open = false;
        $explanation = '';
        
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
            if($open && $subToken->isArgumentSeparator())
            {
                if($token->isExplanationFunction()) {
                    $leftover = array_slice($this->tokens, $i+1);
                    $explanation = $this->parseExplanation($token, $leftover);
                }
                break;
            }
            
            if($open && $subToken->isEncapsedString())
            {
                $textParts[] = $this->trimText(strval($subToken->getValue()));
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
        
        $this->addResult($text, $token->getLine(), $explanation);
    }

    /**
     * @param BaseParsedToken $token
     * @param array<int,RawParsedToken> $tokens
     * @return string
     */
    private function parseExplanation(BaseParsedToken $token, array $tokens) : string
    {
        $textParts = array();
        $max = 200;

        for($i = 0; $i < $max; $i++)
        {
            if(!isset($tokens[$i])) {
                break;
            }

            $subToken = $this->createToken($tokens[$i], $token);

            if($subToken->isClosingFuncParams()) {
                break;
            }

            // additional parameters in the translation function, we don't want to capture these now.
            if($subToken->isArgumentSeparator())
            {
                break;
            }

            if($subToken->isEncapsedString())
            {
                $textParts[] = $this->trimText((string)$subToken->getValue());
                continue;
            }

            if($subToken->isVariableOrFunction()) {
                $textParts = null;
                $this->addWarning($subToken, t('Variables or functions are not supported in translation functions.'));
                break;
            }
        }

        if(empty($textParts)) {
            return '';
        }

        return implode('', $textParts);
    }

    protected function debug(string $text) : void
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
    public function trimText(string $text) : string
    {
        return stripslashes(trim($text, "'\""));
    }
}