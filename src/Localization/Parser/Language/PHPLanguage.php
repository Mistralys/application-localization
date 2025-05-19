<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser\Language;

use AppLocalize\Localization\Parser\BaseLanguage;
use AppLocalize\Localization\Parser\Token\PHPToken;

class PHPLanguage extends BaseLanguage
{
    public const LANGUAGE_ID = 'PHP';

    protected function getTokens() : array
    {
        return token_get_all($this->content);
    }

    public function getID(): string
    {
        return self::LANGUAGE_ID;
    }

    public function getTokenClass(): string
    {
        return PHPToken::class;
    }
}
