<?php

declare(strict_types=1);

namespace AppLocalize;

use AppUtils\ConvertHelper;

abstract class Localization_Parser_Token
{
   /**
    * @var array|string
    */
    protected $definition;
    
   /**
    * @var Localization_Parser_Token|NULL
    */
    protected $parentToken;

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var string|NULL
     */
    protected $value = null;

    /**
     * @var int
     */
    protected $line = 0;

    /**
     * @var array<string,bool>
     */
    protected $nameLookup = array();

    /**
     * @param array|string $definition
     * @param Localization_Parser_Token|null $parentToken
     */
    public function __construct($definition, ?Localization_Parser_Token $parentToken=null)
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
