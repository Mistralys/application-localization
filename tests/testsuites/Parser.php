<?php

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization_Parser_Language_Javascript;
use AppLocalize\Localization_Exception;

final class ParserTest extends TestCase
{
    /**
     * Check that an exception is thrown when trying
     * to parse a file with an extension that is not
     * supported.
     */
    public function test_unsupportedFiles()
    {
        $parser = Localization::createScanner()->getParser();
        
        $this->expectException(Localization_Exception::class);
        
        $parser->parseFile('/path/to/unknown/file.txt');
    }
    
    /**
     * Check that the file not found exception is triggered
     * if the file to parse does not exist.
     */
    public function test_fileNotFound()
    {
        $parser = Localization::createScanner()->getParser();
        
        $this->expectException(Localization_Exception::class);
        
        $lang = $parser->parseFile('/path/to/unknown/file.js');
    }
}