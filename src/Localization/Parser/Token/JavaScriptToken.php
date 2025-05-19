<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser\Token;

use AppLocalize\Localization\Parser\BaseParsedToken;
use Peast\Syntax\Token;

class JavaScriptToken extends BaseParsedToken
{
    protected function parseDefinition() : void
    {
        // some entries are strings, like parentheses, semicolons and the like.
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
            $this->token = (string)$this->definition[0];
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
        return $this->getValue() === '(';
    }
    
    public function isClosingFuncParams() : bool
    {
        return $this->getValue() === ')';
    }
    
    public function isEncapsedString() : bool
    {
        return $this->token === Token::TYPE_STRING_LITERAL;
    }

    public function isTranslationFunction() : bool
    {
        return $this->isVariableOrFunction() && isset($this->nameLookup[$this->getValue()]);
    }
    
    public function isVariableOrFunction() : bool
    {
        return $this->getToken() === Token::TYPE_IDENTIFIER;
    }

    public function isExplanationFunction() : bool
    {
        return false;
    }

    public function isArgumentSeparator() : bool
    {
        return $this->getValue() === ',';
    }
}
