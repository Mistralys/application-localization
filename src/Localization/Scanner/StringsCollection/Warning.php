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
    
    public function getFile()
    {
        return $this->data['file'];
    }
    
    public function getLine()
    {
        return $this->data['line'];
    }
    
    public function getLanguageID()
    {
        return $this->data['languageID'];
    }
    
    public function getMessage()
    {
        return $this->data['message'];
    }
}
