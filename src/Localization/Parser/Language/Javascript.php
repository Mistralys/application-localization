<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Parser_Language_Javascript extends Localization_Parser_Language
{
    protected function getTokens() : array
    {
        return \JTokenizer\JTokenizer::getTokens($this->content);
    }
}
