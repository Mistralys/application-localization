<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Parser_Warning
{
    protected $language;
    
    protected $token;
    
    protected $message;
    
    public function __construct(Localization_Parser_Language $language, Localization_Parser_Token $token, string $message)
    {
        $this->language = $language;
        $this->token = $token;
        $this->message = $message;
    }
    
    public function getLanguage() : Localization_Parser_Language
    {
        return $this->language;
    }
    
    public function getToken() : Localization_Parser_Token
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
    
    public function toArray()
    {
        return array(
            'languageID' => $this->language->getID(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'message' => $this->getMessage()
        );
    }
}
