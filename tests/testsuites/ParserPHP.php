<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization_Parser_Language_PHP;
use AppLocalize\Localization_Exception;

final class ParserPHPTest extends TestCase
{
    protected $phpFile;
    
    protected function setUp() : void
    {
        if(isset($this->phpFile)) {
            return;
        }
        
        $this->phpFile = realpath(__DIR__.'/../assets/Parser/translations.php');
        
        if($this->phpFile === false) {
            throw new Exception(
                'Cannot run parser tests: the parser PHP test file does not exist.'
            );
        }
    }
    
    /**
     * Check that the expected translatable strings are found
     * in the test file.
     */
    public function test_findTexts() : void
    {
        $parser = Localization::createScanner()->getParser();
        
        $lang = $parser->parseFile($this->phpFile);
        
        $this->assertInstanceOf(Localization_Parser_Language_PHP::class, $lang);
        
        $expected = array(
            'Global context',
            'With double quotes',
            'Within function',
            'Multiline text over several concatenated lines',
            'Within class method.',
            'This is %1$sbold%2$s text.',
            'A %1$s text within a translated text.',
            'translated text',
            'Within a closure'
        );
        
        $expectedAmount = count($expected);
        
        $texts = $lang->getTexts();

        $actual = array();
        foreach ($texts as $text)
        {
            $actual[] = $text->getText();
        }

        $actual = array_unique($actual);

        $this->assertEquals(
            $expectedAmount,
            count($actual),
            'The amount of texts found does not match.'.PHP_EOL.
            'Expected:'.PHP_EOL.
            print_r($expected, true).PHP_EOL.
            'Actual:'.PHP_EOL.
            print_r($actual, true)
        );
        
        foreach($actual as $text)
        {
            $this->assertContains($text, $expected);
        }
    }
    
    /**
     * Check that the expected warnings are triggered when
     * parsing the test file.
     */
    public function test_triggerWarnings() : void
    {
        $parser = Localization::createScanner()->getParser();
        
        $lang = $parser->parseFile($this->phpFile);
        
        $this->assertTrue($lang->hasWarnings());
        
        $warnings = $lang->getWarnings();
        
        $this->assertEquals(2, count($warnings), 'The amount of warnings generated should match.');
        
        foreach($warnings as $warning)
        {
            $this->assertEquals($this->phpFile, $warning->getFile());
        }
    }
    
    /**
     * Check that translatable texts are found as expected
     * when parsing a javascript string instead of a file.
     */
    public function test_fromString() : void
    {
        $parser = Localization::createScanner()->getParser();
        
        $lang = $parser->parseString('PHP', "<?php \$text = t('Some text here');");
        
        $this->assertFalse($lang->hasSourceFile());
        
        $expected = array(
            'Some text here'
        );
        
        $actual = $lang->getTexts();
        
        $this->assertEquals(count($expected), count($actual), 'The amount of translatable texts must match.');
        
        foreach($actual as $text)
        {
            $this->assertContains($text->getText(), $expected, 'The translated text does not match the expected texts.');
        }
    }
}
