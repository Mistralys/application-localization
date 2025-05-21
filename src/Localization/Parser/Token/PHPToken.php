<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser\Token;

use AppLocalize\Localization\Parser\BaseParsedToken;

class PHPToken extends BaseParsedToken
{
    /**
     * @var array<string,bool>
     */
    private static array $explanationFunctions = array(
        'tex' => true,
        'ptex' => true,
        'ptexs' => true
    );

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
            $this->token = token_name((int)$this->definition[0]);
            $this->value = $this->definition[1];
            $this->line = $this->definition[2];
        }
    }

    /**
     * @return string[]
     */
    public function getFunctionNames() : array
    {
        return array(
            't',
            'pt',
            'pts',
            'tex',
            'ptex',
            'ptexs'
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
    
    public function isString() : bool
    {
        return $this->token === 'T_STRING';
    }
    
    public function isEncapsedString() : bool
    {
        return $this->token === 'T_CONSTANT_ENCAPSED_STRING';
    }
    
    public function isTranslationFunction() : bool
    {
        return $this->isString() && isset($this->nameLookup[$this->getValue()]);
    }
    
    public function isVariableOrFunction() : bool
    {
        return $this->token === 'T_VARIABLE' || $this->token === 'T_FUNCTION';
    }

    public function isExplanationFunction() : bool
    {
        return isset(self::$explanationFunctions[$this->getValue()]);
    }
    
    public function isArgumentSeparator() : bool
    {
        return $this->getToken() === ',';
    }
}
