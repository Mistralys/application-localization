<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser\Language;

use AppLocalize\Localization\Parser\BaseLanguage;
use AppLocalize\Localization\Parser\Token\JavaScriptToken;
use Peast\Peast;

class JavaScriptLanguage extends BaseLanguage
{
    public const LANGUAGE_ID = 'JavaScript';

    protected function getTokens() : array
    {
        $tokens = Peast::latest($this->content)->tokenize();

        $result = array();
        foreach($tokens as $token) {
            $result[] = array(
                $token->getType(),
                $token->getValue(),
                $token->getLocation()->getStart()->getLine()
            );
        }

        return $result;
    }

    public function getID(): string
    {
        return self::LANGUAGE_ID;
    }

    public function getTokenClass(): string
    {
        return JavaScriptToken::class;
    }
}
