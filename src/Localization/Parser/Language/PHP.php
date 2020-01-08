<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Parser_Language_PHP extends Localization_Parser_Language
{
    protected function getTokens() : array
    {
        return token_get_all($this->content);
    }
}
