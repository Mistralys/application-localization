<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Tools;

use AppLocalize\Localization;
use AppLocalize\Tools\TranslationExporter;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for {@see TranslationExporter}.
 *
 * setUp configures Localization against the real localization/ directory and
 * runs a fresh export before each test so all assertions see up-to-date JSON.
 *
 * @package AppLocalize
 * @subpackage Tests
 */
class TranslationExporterTest extends TestCase
{
    private string $localizationDir;
    private string $deJsonFile;
    private string $frJsonFile;

    /**
     * Backup of the original de_DE server INI content, restored in tearDown.
     */
    private string $deServerIniBackup = '';

    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        $root = realpath(dirname(__DIR__, 3));
        $this->assertIsString($root);

        $this->localizationDir = $root . '/localization';

        // Temporarily strip one translation entry from the de_DE server INI so
        // that the subsequent export always contains at least one untranslated
        // hash, regardless of whether composer import-translations was run
        // before this test.
        $deServerIni = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $backup = file_get_contents($deServerIni);
        $this->assertIsString($backup);
        $this->deServerIniBackup = $backup;

        $entries = parse_ini_file($deServerIni);
        if (is_array($entries)) {
            // Find the last key with a non-empty value and remove it.
            $keys = array_keys(array_filter($entries, static fn($v) => $v !== ''));
            if (!empty($keys)) {
                $keyToRemove = end($keys);
                unset($entries[$keyToRemove]);
            }
            // Write the modified INI back.
            $lines = [];
            foreach ($entries as $key => $value) {
                $lines[] = $key . ' = "' . $value . '"';
            }
            file_put_contents($deServerIni, implode("\n", $lines) . "\n");
        }

        Localization::reset();
        Localization::addAppLocale('de_DE');
        Localization::addAppLocale('fr_FR');

        // Configure the scanner with the real localization/storage.json.
        // Client-library writing is intentionally skipped (empty 2nd arg).
        Localization::configure($this->localizationDir . '/storage.json');

        // Run a fresh export — writes to real localization/ (idempotent).
        // Output is suppressed during setup; test methods assert on file content.
        ob_start();
        TranslationExporter::create()->export();
        ob_end_clean();

        $this->deJsonFile = $this->localizationDir . '/de_DE-application-localization-translations.json';
        $this->frJsonFile = $this->localizationDir . '/fr_FR-application-localization-translations.json';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore the original de_DE server INI so the localization/ directory
        // is left in its original state after each test run.
        if ($this->deServerIniBackup !== '') {
            $deServerIni = $this->localizationDir . '/de_DE-application-localization-server.ini';
            file_put_contents($deServerIni, $this->deServerIniBackup);
        }
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Every exported JSON file must contain the seven top-level fields
     * defined in format_version 1.
     */
    public function testExportedJsonHasRequiredTopLevelKeys(): void
    {
        $requiredKeys = ['format_version', 'locale', 'locale_label', 'source_alias', 'source_label', 'exported_at', 'strings'];

        foreach ([$this->deJsonFile, $this->frJsonFile] as $file) {
            $data = $this->readJson($file);

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $data, "Missing top-level key '$key' in $file");
            }
        }
    }

    /**
     * Each entry in the "strings" array must contain exactly the five
     * fields expected by translators and the importer.
     */
    public function testStringEntryHasRequiredFields(): void
    {
        $data = $this->readJson($this->deJsonFile);
        $this->assertNotEmpty($data['strings'], 'Expected at least one string entry');

        $entry = $data['strings'][0];
        $requiredFields = ['hash', 'source_text', 'context', 'files', 'translation'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $entry, "Missing field '$field' in first string entry");
        }
    }

    /**
     * The built-in (source / native) locale en_GB must never produce an
     * export file.
     */
    public function testNativeLocaleIsExcluded(): void
    {
        $enJsonFile = $this->localizationDir . '/en_GB-application-localization-translations.json';
        $this->assertFileDoesNotExist($enJsonFile, 'en_GB export file must never be written');
    }

    /**
     * A hash that has no INI translation entry must appear in the JSON with
     * an empty string as "translation".
     */
    public function testUntranslatedStringHasEmptyTranslation(): void
    {
        $data = $this->readJson($this->deJsonFile);

        $iniFile = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $ini = parse_ini_file($iniFile);

        $foundUntranslated = false;

        foreach ($data['strings'] as $entry) {
            $hash = $entry['hash'];

            if (!is_array($ini) || !isset($ini[$hash])) {
                $this->assertSame(
                    '',
                    $entry['translation'],
                    "Hash $hash has no INI entry so its JSON translation should be empty"
                );
                $foundUntranslated = true;
                break;
            }
        }

        $this->assertTrue($foundUntranslated, 'Expected at least one untranslated string in the export');
    }

    /**
     * A hash that has an INI translation entry must appear in the JSON with
     * exactly that translation (no garbling, no truncation).
     */
    public function testTranslatedStringHasCorrectTranslation(): void
    {
        $data = $this->readJson($this->deJsonFile);

        $iniFile = $this->localizationDir . '/de_DE-application-localization-server.ini';
        $ini = parse_ini_file($iniFile);

        foreach ($data['strings'] as $entry) {
            if (is_array($ini) && isset($ini[$entry['hash']]) && $ini[$entry['hash']] !== '') {
                $this->assertSame(
                    $ini[$entry['hash']],
                    $entry['translation'],
                    "JSON translation should match INI value for hash {$entry['hash']}"
                );
                return;
            }
        }

        $this->fail('Could not find a translated string to validate against INI file');
    }

    /**
     * The "files" arrays must contain relative paths — never absolute paths
     * that start with the project root.
     */
    public function testFilePathsAreRelative(): void
    {
        $data = $this->readJson($this->deJsonFile);
        $projectRoot = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 3)));

        foreach ($data['strings'] as $entry) {
            foreach ($entry['files'] as $filePath) {
                [$path] = explode(':', (string) $filePath);
                $normalizedPath = str_replace('\\', '/', (string) $path);

                $this->assertStringNotContainsString(
                    $projectRoot,
                    $normalizedPath,
                    "File path should not contain the project root: $path"
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Reads and JSON-decodes a file, asserting the result is an array.
     *
     * @return array<string, mixed>
     */
    private function readJson(string $file): array
    {
        $this->assertFileExists($file, "Expected export file to exist: $file");
        $content = file_get_contents($file);
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data, "Expected JSON to decode to array in $file");
        return $data;
    }
}
