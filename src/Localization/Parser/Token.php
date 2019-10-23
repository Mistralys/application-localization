<?php

namespace AppLocalize;

abstract class Localization_Parser_Token
{
   /**
    * @var array|string
    */
    protected $definition;
    
   /**
    * @var Localization_Parser_Token
    */
    protected $parentToken;
    
    protected $token;
    
    protected $value;
    
    protected $line = 0;
    
    public function __construct($definition, Localization_Parser_Token $parentToken=null)
    {
        $this->definition = $definition;
        $this->parentToken = $parentToken;
        
        $this->parseDefinition();
    }
    
    public function getValue()
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
    
    abstract public function getFunctionNames();
    
    abstract public function isEncapsedString();
    
    abstract public function isTranslationFunction();
    
    abstract public function isVariableOrFunction();
    
    public function getLine() : int
    {
        return $this->line;
    }
    
    abstract public function isArgumentSeparator();
    
    public function toArray()
    {
        return array(
            'token' => $this->getToken(),
            'value' => $this->getValue(),
            'line' => $this->getLine(),
            'isEncapsedString' => \AppUtils\ConvertHelper::bool2string($this->isEncapsedString())
         );
    }
}
