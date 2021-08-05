<?php

declare(strict_types=1);

namespace AppLocalize;

use JTokenizer\JTokenizer;

class Localization_Parser_Language_Javascript extends Localization_Parser_Language
{
    protected function getTokens() : array
    {
        return JTokenizer::getTokens($this->content);
    }
}
