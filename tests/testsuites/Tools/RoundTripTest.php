<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Tools;

use AppLocalize\Localization;
use AppLocalize\Tools\TranslationExporter;
use AppLocalize\Tools\TranslationImporter;
use PHPUnit\Framework\TestCase;

/**
 * Round-trip integration tests.
 *
 * Runs a full export → import cycle against the real localization/ directory
 * and verifies that the resulting INI files are consistent with the JSON
 * export files (i.e. no data is lost or corrupted in transit).
 *
 * @package AppLocalize
 * @subpackage Tests
 */
class RoundTripTest extends TestCase
{
    private string $localizationDir;

    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        $root = realpath(dirname(__DIR__, 3));
        $this->assertIsString($root);

        $this->localizationDir = $root . '/localization';

        Localization::reset();
        Localization::addAppLocale('de_DE');
        Localization::addAppLocale('fr_FR');
        Localization::configure($this->localizationDir . '/storage.json');
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * After a full export → import cycle the server INI must contain exactly
     * the same translations that were recorded before the cycle began.
     */
    public function testRoundTripPreservesTranslations(): void
    {
        $serverIniFile = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $originalIni = parse_ini_file($serverIniFile);
        $this->assertIsArray($originalIni);

        ob_start();
        TranslationExporter::create()->export();
        TranslationImporter::create()->import();
        ob_end_clean();

        $newIni = parse_ini_file($serverIniFile);
        $this->assertIsArray($newIni);

        $this->assertCount(
            count($originalIni),
            $newIni,
            'Round-trip should produce the same number of INI entries'
        );

        foreach ($originalIni as $hash => $value) {
            $this->assertArrayHasKey($hash, $newIni, "Hash $hash should be present after round-trip");
            $this->assertSame(
                $value,
                $newIni[$hash],
                "Translation for hash $hash should be unchanged after round-trip"
            );
        }
    }

    /**
     * After a full export → import cycle, the number of INI entries must
     * equal the number of non-empty translation entries in the JSON file.
     */
    public function testRoundTripTranslationCountMatchesJsonNonEmpty(): void
    {
        ob_start();
        TranslationExporter::create()->export();
        TranslationImporter::create()->import();
        ob_end_clean();

        $jsonFile = $this->localizationDir . '/de_DE-application-localization-translations.json';
        $this->assertFileExists($jsonFile);

        $content = file_get_contents($jsonFile);
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

        $nonEmptyCount = count(array_filter(
            $data['strings'],
            static fn (array $entry): bool => $entry['translation'] !== ''
        ));

        $serverIni = parse_ini_file($this->localizationDir . '/de_DE-application-localization-server.ini');
        $this->assertIsArray($serverIni);

        $this->assertSame(
            $nonEmptyCount,
            count($serverIni),
            'INI entry count should equal the number of non-empty JSON translations after round-trip'
        );
    }
}
