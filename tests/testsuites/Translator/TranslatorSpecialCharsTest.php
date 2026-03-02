<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Translator;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\Translator\LocalizationWriter;
use AppUtils\FileHelper;

final class TranslatorSpecialCharsTest extends TestCase
{
    private static string $storagePath = '';

    protected function setUp(): void
    {
        if (empty(self::$storagePath)) {
            $path = realpath(__DIR__ . '/../../storage');

            if (is_string($path)) {
                self::$storagePath = $path;
            } else {
                $this->fail('Could not find the tests storage folder.');
            }
        }
    }

    /**
     * Verifies that special characters survive a write/read round-trip
     * through the INI file format: double quotes, backslashes, newlines,
     * and semicolons.
     */
    public function test_roundTrip_specialCharacters(): void
    {
        $filePath = self::$storagePath . '/trywrite.ini';

        FileHelper::deleteFile($filePath);

        $text = "She said \"hello\" and\nnew line with back\\slash; semicolon\r\nand CR+LF";

        $writer = new LocalizationWriter(
            Localization::getAppLocale(),
            'client',
            $filePath
        );

        $writer->addHash('testhash', $text);
        $writer->writeFile();

        $data = parse_ini_file($filePath, false, INI_SCANNER_RAW);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('testhash', $data);

        // Unescape using the same logic as LocalizationTranslator
        $value = str_replace(
            ['\\n', '\\r', '\\"', '\\\\'],
            ["\n", "\r", '"', '\\'],
            $data['testhash']
        );

        $this->assertSame($text, $value);
    }

    /**
     * Regression test: the word "Yes" must survive a round-trip.
     * Without INI_SCANNER_RAW, PHP interprets "Yes" as boolean true ("1").
     */
    public function test_roundTrip_yesValue(): void
    {
        $filePath = self::$storagePath . '/trywrite.ini';

        FileHelper::deleteFile($filePath);

        $writer = new LocalizationWriter(
            Localization::getAppLocale(),
            'client',
            $filePath
        );

        $writer->addHash('yeshash', 'Yes');
        $writer->addHash('nohash', 'No');
        $writer->addHash('nonehash', 'None');
        $writer->writeFile();

        $data = parse_ini_file($filePath, false, INI_SCANNER_RAW);
        $this->assertIsArray($data);

        // Unescape
        $data = array_map(
            static fn(string $value): string => str_replace(
                ['\\n', '\\r', '\\"', '\\\\'],
                ["\n", "\r", '"', '\\'],
                $value
            ),
            $data
        );

        $this->assertSame('Yes', $data['yeshash']);
        $this->assertSame('No', $data['nohash']);
        $this->assertSame('None', $data['nonehash']);
    }
}
