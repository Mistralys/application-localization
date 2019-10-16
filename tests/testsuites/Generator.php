<?php

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;

final class GeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        Localization::reset();
    }

    public function test_writeFiles()
    {
        Localization::addAppLocale('fr_FR');
        Localization::addAppLocale('de_DE');
        
        $generator = Localization::createGenerator();

        $files = $generator->getFilesList();
        
        // two locales + 2 library files
        $this->assertEquals(4, count($files));
        
        foreach($files as $file)
        {
            \AppUtils\FileHelper::deleteFile($file);
        }
        
        $generator->writeFiles();
        
        foreach($files as $file)
        {
            $this->assertTrue(file_exists($file));
        }
    }
}