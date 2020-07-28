<?php

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization_Writer;
use AppUtils\FileHelper;

final class WriterTest extends TestCase
{
   /**
    * @var string
    */
    private static $storagePath = '';
    
    protected function setUp() : void
    {
        if(empty(self::$storagePath))
        {
            $path = realpath(__DIR__.'/../storage');
            
            if(is_string($path))
            {
                self::$storagePath = $path;
            }
            else 
            {
                throw new Exception('Could not find the tests storage folder.');
            }
        }
    }
    
    public function test_escape_quotes() : void
    {
        $filePath = self::$storagePath.'/trywrite.ini';
        
        FileHelper::deleteFile($filePath);
        
        $writer = new Localization_Writer(
            Localization::getAppLocale(), 
            'client', 
            $filePath
        );
        
        $writer->addHash('hash', 'Some "text with" quotes.');
        
        $writer->writeFile();
        
        // if the file parses without returning false, it works.
        $this->assertIsArray(parse_ini_file($filePath));
    }
}
