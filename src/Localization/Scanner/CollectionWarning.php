<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Scanner;

use AppLocalize\Localization\Parser\ParserWarning;

/**
 * @phpstan-import-type SerializedWarning from ParserWarning
 */
class CollectionWarning
{
   /**
    * @var SerializedWarning
    */
    protected array $data;

    /**
     * @param SerializedWarning $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function getFile() : string
    {
        return (string)$this->data['file'];
    }
    
    public function getLine() : int
    {
        return (int)$this->data['line'];
    }
    
    public function getLanguageID() : string
    {
        return (string)$this->data['languageID'];
    }
    
    public function getMessage() : string
    {
        return (string)$this->data['message'];
    }
}
