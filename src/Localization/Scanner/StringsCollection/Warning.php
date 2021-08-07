<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Scanner_StringsCollection_Warning
{
   /**
    * @var array
    */
    protected $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function getFile() : string
    {
        return strval($this->data['file']);
    }
    
    public function getLine() : int
    {
        return intval($this->data['line']);
    }
    
    public function getLanguageID() : string
    {
        return strval($this->data['languageID']);
    }
    
    public function getMessage() : string
    {
        return strval($this->data['message']);
    }
}
