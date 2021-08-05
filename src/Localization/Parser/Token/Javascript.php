<?php

declare(strict_types=1);

namespace AppLocalize;

use JTokenizer\JTokenizer;

class Localization_Parser_Token_Javascript extends Localization_Parser_Token
{
    protected function parseDefinition() : void
    {
        // some entries are strings, like parenthesises, semicolons and the like.
        if(is_string($this->definition))
        {
            $this->token = $this->definition;
            $this->value = null;
            
            if(isset($this->parentToken)) {
                $this->line = $this->parentToken->getLine();
            }
        }
        else
        {
            $this->token = JTokenizer::getTokenName($this->definition[0]);
            $this->value = $this->definition[1];
            $this->line = $this->definition[2];
        }
    }
    
    public function getFunctionNames() : array
    {
        return array(
            't'
        );
    }
    
    public function isOpeningFuncParams() : bool
    {
        return $this->getToken() === '(';
    }
    
    public function isClosingFuncParams() : bool
    {
        return $this->getToken() === ')';
    }
    
    public function isEncapsedString() : bool
    {
        return $this->token === 'J_STRING_LITERAL';
    }

    public function isTranslationFunction() : bool
    {
        return $this->isVariableOrFunction() && isset($this->nameLookup[$this->getValue()]);
    }
    
    public function isVariableOrFunction() : bool
    {
        return $this->token === 'J_IDENTIFIER' || $this->token === 'J_FUNCTION';
    }

    public function isExplanationFunction() : bool
    {
        return false;
    }

    public function isArgumentSeparator() : bool
    {
        return $this->getToken() === ',';
    }
}
