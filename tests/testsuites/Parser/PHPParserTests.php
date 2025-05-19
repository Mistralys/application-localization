<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Parser;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\Parser\Language\PHPLanguage;

final class ParserPHPTest extends TestCase
{
    /**
     * @var string
     */
    protected static string $phpFile = '';

    protected function setUp(): void
    {
        if (!empty(self::$phpFile)) {
            return;
        }

        $phpFile = realpath(__DIR__ . '/../../assets/Parser/translations.php');

        if ($phpFile !== false) {
            self::$phpFile = $phpFile;
            return;
        }

        $this->fail('Cannot run parser tests: the parser PHP test file does not exist.');
    }

    /**
     * Check that the expected translatable strings are found
     * in the test file.
     */
    public function test_findTexts(): void
    {
        $parser = Localization::createScanner()->getParser();

        $lang = $parser->parseFile(self::$phpFile);

        $this->assertInstanceOf(PHPLanguage::class, $lang);

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
        foreach ($texts as $text) {
            $actual[] = $text->getText();
        }

        $actual = array_unique($actual);

        $this->assertCount(
            $expectedAmount,
            $actual,
            'The amount of texts found does not match.' . PHP_EOL .
            'Expected:' . PHP_EOL .
            print_r($expected, true) . PHP_EOL .
            'Actual:' . PHP_EOL .
            print_r($actual, true)
        );

        foreach ($actual as $text) {
            $this->assertContains($text, $expected);
        }
    }

    /**
     * Check that the expected warnings are triggered when
     * parsing the test file.
     */
    public function test_triggerWarnings(): void
    {
        $parser = Localization::createScanner()->getParser();

        $lang = $parser->parseFile(self::$phpFile);

        $this->assertTrue($lang->hasWarnings());

        $warnings = $lang->getWarnings();

        $this->assertCount(1, $warnings, 'The amount of warnings generated should match.' . PHP_EOL . $lang->getWarningsAsString());

        foreach ($warnings as $warning) {
            $this->assertEquals(self::$phpFile, $warning->getFile());
        }
    }

    /**
     * Check that translatable texts are found as expected
     * when parsing a JavaScript string instead of a file.
     */
    public function test_fromString(): void
    {
        $parser = Localization::createScanner()->getParser();

        $lang = $parser->parseString(PHPLanguage::LANGUAGE_ID, "<?php \$text = t('Some text here');");

        $this->assertFalse($lang->hasSourceFile());

        $expected = array(
            'Some text here'
        );

        $actual = $lang->getTexts();

        $this->assertSameSize($expected, $actual, 'The amount of translatable texts must match.');

        foreach ($actual as $text) {
            $this->assertContains($text->getText(), $expected, 'The translated text does not match the expected texts.');
        }
    }
}
