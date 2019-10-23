<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Parser_Language_Javascript extends Localization_Parser_Language
{
    protected function getTokens() : array
    {
        return \JTokenizer\JTokenizer::getTokens($this->content);
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
