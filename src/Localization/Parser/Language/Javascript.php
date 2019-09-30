<?php

namespace AppLocalize;

class Localization_Parser_Language_Javascript extends Localization_Parser_Language
{
    public function getFunctionNames()
    {
        return array('t');
    }

    protected $totalTokens = 0;

    protected $debug = false;

    protected $tokens;
    
    protected function _parse()
    {
        $this->tokens = j_token_get_all($this->content);

        $this->totalTokens = count($this->tokens);
        for ($i = 0; $i < $this->totalTokens; $i++) {
            $this->parseToken($i, $this->tokens[$i]);
        }
    }

    protected function parseToken($number, $def)
    {
        if (!is_array($def)) {
            return;
        }

        $token = j_token_name($def[0]);
        $value = $def[1];
        $line = $def[2];

        if ($token != 'J_IDENTIFIER' || !in_array($value, $this->functionNames)) {
            return;
        }

        // we have found a function we seek - now we have
        // to iterate over the next tokens to find the text
        $max = $number + 20;
        $text = null;
        for ($i = $number; $i < $max; $i++) 
        {
            if(!isset($this->tokens[$i])) {
                continue;
            }
            
            $subdef = $this->tokens[$i];
            $subtoken = j_token_name($subdef[0]);
            $subvalue = $subdef[1];
            if ($subtoken == 'J_STRING_LITERAL') {
                $text = $this->trimText($subvalue);
                break;
            }
        }

        $this->debug('Found text [' . htmlspecialchars($text) . ']');

        if ($text) {
            $this->addResult($text, $line);
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
        return stripslashes(trim($text, "'"));
    }
    
    protected function debug($text)
    {
        if ($this->debug) {
            echo $text;
        }
    }
}