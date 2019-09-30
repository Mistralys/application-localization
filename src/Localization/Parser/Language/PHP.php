<?php

namespace AppLocalize;

class Localization_Parser_Language_PHP extends Localization_Parser_Language
{
    public function getFunctionNames()
    {
        return array('t', 'pt');
    }

    protected function _parse()
    {
        $this->tokens = token_get_all($this->content);

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

        $token = token_name($def[0]);
        if ($token != 'T_STRING') {
            return;
        }

        $value = $def[1];
        if (!in_array($value, $this->functionNames)) {
            return;
        }

        $text = null;
        $max = $number + 20;
        $total = count($this->tokens);
        if($max > $total) {
            $max = $total;
        }
        
        for ($i = $number; $i < $max; $i++) {
            $subdef = $this->tokens[$i];
            if (!is_array($subdef)) {
                continue;
            }

            $subtoken = token_name($subdef[0]);
            $subvalue = $subdef[1];
            if ($subtoken == 'T_CONSTANT_ENCAPSED_STRING') {
                $text = $this->trimText($subvalue);
                break;
            }
        }

        if ($text == '__translator') {
            return;
        }

        if ($text) {
            $line = $def[2];
            $this->addResult($text, $line);
        }

        return;
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
}