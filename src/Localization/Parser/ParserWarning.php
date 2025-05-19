<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser;

use function AppLocalize\t;
use function AppLocalize\tex;

class ParserWarning
{
    protected BaseLanguage $language;
    protected BaseParsedToken $token;
    protected string $message;
    
    public function __construct(BaseLanguage $language, BaseParsedToken $token, string $message)
    {
        $this->language = $language;
        $this->token = $token;
        $this->message = $message;
    }
    
    public function getLanguage() : BaseLanguage
    {
        return $this->language;
    }
    
    public function getToken() : BaseParsedToken
    {
        return $this->token;
    }
    
    public function getFile() : string
    {
        return $this->language->getSourceFile();
    }
    
    public function getLine() : int
    {
        return $this->token->getLine();
    }
    
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        return array(
            'languageID' => $this->language->getID(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'message' => $this->getMessage()
        );
    }

    public function toString() : string
    {
        return tex(
            '%1$s Translation warning: %2$s. In file %3$s on line %4$d.',
            'Placeholders: #1 = Language ID (e.g. "PHP") #2 = Message #3 = File #4 = Line',
            $this->language->getID(),
            rtrim($this->getMessage(), '.'),
            basename($this->getFile()),
            $this->getLine()
        );
    }
}
