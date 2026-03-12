<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Tools;

use AppLocalize\Localization;
use AppLocalize\Tools\TranslationImporter;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for {@see TranslationImporter}.
 *
 * Each test resets Localization and re-configures it with the real
 * localization/ directory before exercising the importer.
 *
 * Tests that need to manipulate OS files do so with backup/restore to
 * leave the localization/ directory in its original state.
 *
 * @package AppLocalize
 * @subpackage Tests
 */
class TranslationImporterTest extends TestCase
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

        // Configure the scanner with the real localization/storage.json.
        // Client-library writing is intentionally skipped (empty 2nd arg).
        Localization::configure($this->localizationDir . '/storage.json');
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * After import, the server INI must not contain any entry whose value is
     * an empty string — empty translations are silently skipped.
     */
    public function testEmptyTranslationIsSkipped(): void
    {
        ob_start();
        TranslationImporter::create()->import();
        ob_end_clean();

        $iniFile = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $this->assertFileExists($iniFile);

        $translations = parse_ini_file($iniFile);
        $this->assertIsArray($translations);

        foreach ($translations as $hash => $value) {
            $this->assertNotEquals(
                '',
                $value,
                "Server INI should never have an empty value (hash: $hash)"
            );
        }
    }

    /**
     * A hash that is present in the JSON but absent from the current
     * StringCollection must trigger a WARNING message and not cause an
     * exception.
     */
    public function testStaleHashProducesWarning(): void
    {
        $jsonFile = $this->localizationDir . '/de_DE-application-localization-translations.json';
        $backup = file_get_contents($jsonFile);
        $this->assertIsString($backup);

        $data = json_decode($backup, true);
        $this->assertIsArray($data);

        // Inject an entry with a fake/stale MD5 hash that cannot exist in
        // the current StringCollection.
        $staleHash = 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4';

        $data['strings'][] = [
            'hash' => $staleHash,
            'source_text' => 'Fake test string (stale)',
            'context' => '',
            'files' => [],
            'translation' => 'Gefälschte Übersetzung',
        ];

        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        try {
            ob_start();
            TranslationImporter::create()->import();
            $output = ob_get_clean();

            $this->assertStringContainsString(
                'WARNING: Stale hash [' . $staleHash . ']',
                (string) $output,
                'Expected stale hash warning in import output'
            );
        } finally {
            // Always restore the original JSON regardless of test outcome.
            file_put_contents($jsonFile, $backup);
        }
    }

    /**
     * The client INI must contain only strings that have the JavaScript
     * language type.  For the application-localization source (which has no
     * JS strings) the client INI must contain zero translation entries.
     */
    public function testClientIniContainsOnlyJsStrings(): void
    {
        ob_start();
        TranslationImporter::create()->import();
        ob_end_clean();

        $clientIni = $this->localizationDir . '/de_DE-application-localization-client.ini';
        $this->assertFileExists($clientIni);

        $content = file_get_contents($clientIni);
        $this->assertIsString($content);

        $translations = parse_ini_string($content);
        $this->assertIsArray($translations);

        $this->assertCount(
            0,
            $translations,
            'Client INI should contain no translation entries because application-localization has no JavaScript strings'
        );
    }

    /**
     * The server INI must contain at least one translated string — i.e. the
     * importer wrote all non-empty translations into the server file.
     */
    public function testServerIniContainsAllStrings(): void
    {
        ob_start();
        TranslationImporter::create()->import();
        ob_end_clean();

        $serverIni = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $this->assertFileExists($serverIni);

        $translations = parse_ini_file($serverIni);
        $this->assertIsArray($translations);
        $this->assertGreaterThan(0, count($translations), 'Server INI should contain translated strings');
    }

    /**
     * When the JSON export file for a locale/source pair is missing, the
     * importer must print a WARNING and continue without throwing an
     * exception.
     */
    public function testMissingJsonFileSkipsWithWarning(): void
    {
        $jsonFile = $this->localizationDir . '/de_DE-application-localization-translations.json';
        $backupFile = $jsonFile . '.test-backup';

        rename($jsonFile, $backupFile);

        try {
            ob_start();
            TranslationImporter::create()->import();
            $output = ob_get_clean();

            $this->assertStringContainsString(
                'WARNING: Export file not found',
                (string) $output,
                'Expected missing-file warning in import output'
            );
        } finally {
            rename($backupFile, $jsonFile);
        }
    }
}
