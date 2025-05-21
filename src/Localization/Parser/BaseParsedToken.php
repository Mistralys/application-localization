<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser;

use AppUtils\ConvertHelper;

/**
 * @phpstan-import-type RawParsedToken from BaseLanguage
 */
abstract class BaseParsedToken
{
   /**
    * @var RawParsedToken
    */
    protected $definition;
    
    protected ?BaseParsedToken $parentToken = null;
    protected string $token = '';
    protected ?string $value = null;
    protected int $line = 0;

    /**
     * @var array<string,bool>
     */
    protected array $nameLookup = array();

    /**
     * @param RawParsedToken $definition
     * @param BaseParsedToken|null $parentToken
     */
    public function __construct($definition, ?BaseParsedToken $parentToken=null)
    {
        $this->definition = $definition;
        $this->parentToken = $parentToken;

        $names = $this->getFunctionNames();
        foreach($names as $name) {
            $this->nameLookup[$name] = true;
        }

        $this->parseDefinition();
    }
    
    public function getValue() : ?string
    {
        return $this->value;
    }
    
    public function getToken() : string
    {
        return $this->token;
    }
    
    abstract protected function parseDefinition() : void;
    
    abstract public function isOpeningFuncParams() : bool;
    
    abstract public function isClosingFuncParams() : bool;

    /**
     * @return string[]
     */
    abstract public function getFunctionNames() : array;
    
    abstract public function isEncapsedString() : bool;
    
    abstract public function isTranslationFunction() : bool;
    
    abstract public function isVariableOrFunction() : bool;

    abstract public function isExplanationFunction() : bool;

    public function getLine() : int
    {
        return $this->line;
    }
    
    abstract public function isArgumentSeparator() : bool;

    /**
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        return array(
            'token' => $this->getToken(),
            'value' => $this->getValue(),
            'line' => $this->getLine(),
            'isEncapsedString' => ConvertHelper::bool2string($this->isEncapsedString())
         );
    }
}
