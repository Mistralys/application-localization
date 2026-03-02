<?php

/**
 * @package AppLocalize
 * @subpackage Tools
 */

declare(strict_types=1);

namespace AppLocalize\Tools;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Parser\Language\JavaScriptLanguage;
use AppLocalize\Localization\Scanner\StringCollection;
use AppLocalize\Localization\Scanner\StringHash;
use AppLocalize\Localization\Source\BaseLocalizationSource;
use AppLocalize\Localization\Translator\LocalizationWriter;

/**
 * Reads per-locale per-source JSON export files (produced by TranslationExporter)
 * and writes the translated strings into the corresponding INI translation files
 * via LocalizationWriter.
 *
 * Entry point: `composer import-translations`
 *
 * @package AppLocalize
 * @subpackage Tools
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class TranslationImporter
{
    // -------------------------------------------------------------------------
    // Constructor / Factory
    // -------------------------------------------------------------------------

    private function __construct()
    {
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Composer entry point: `composer import-translations`
     */
    public static function run(): void
    {
        self::loadConfig();
        self::create()->importAll();
    }

    /**
     * Programmatic / test factory.
     *
     * Requires Localization to have been configured before calling this.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Run the import programmatically (without loading config).
     *
     * Localization must have been configured before calling this.
     */
    public function import(): void
    {
        $this->importAll();
    }

    // -------------------------------------------------------------------------
    // Configuration resolution
    // -------------------------------------------------------------------------

    /**
     * Resolves and requires the Localization configuration file.
     *
     * Resolution order:
     *  1. Constant LOCALIZATION_TOOLS_CONFIG
     *  2. Environment variable LOCALIZATION_TOOLS_CONFIG
     *  3. Default fallback: localization-tools-config.php at project root
     *
     * Prints an error and exits with code 1 if no config file is found.
     */
    private static function loadConfig(): void
    {
        // 1. Constant
        if (defined('LOCALIZATION_TOOLS_CONFIG')) {
            $path = constant('LOCALIZATION_TOOLS_CONFIG');
            if (is_string($path) && file_exists($path)) {
                require_once $path;
                return;
            }
        }

        // 2. Environment variable
        $envPath = getenv('LOCALIZATION_TOOLS_CONFIG');
        if (is_string($envPath) && $envPath !== '' && file_exists($envPath)) {
            require_once $envPath;
            return;
        }

        // 3. Default fallback
        $defaultPath = __DIR__ . '/../../localization-tools-config.php';
        if (file_exists($defaultPath)) {
            require_once $defaultPath;
            return;
        }

        echo 'ERROR: No Localization configuration found.' . PHP_EOL;
        echo '       Create localization-tools-config.php at the project root,' . PHP_EOL;
        echo '       or set the LOCALIZATION_TOOLS_CONFIG constant or environment variable.' . PHP_EOL;
        exit(1);
    }

    // -------------------------------------------------------------------------
    // Import logic
    // -------------------------------------------------------------------------

    /**
     * Outer loop: iterates all registered app locales × sources and imports
     * one JSON file per combination, skipping the native (en_GB) locale.
     */
    private function importAll(): void
    {
        $scanner = Localization::createScanner();
        $scanner->load();

        $collection = $scanner->getCollection();
        $sources = $this->getUniqueSources();

        foreach (Localization::getAppLocales() as $locale) {
            if ($locale->getName() === Localization::BUILTIN_LOCALE_NAME) {
                continue;
            }

            foreach ($sources as $source) {
                $this->importLocaleSource($locale, $source, $collection);
            }
        }

        echo 'Import complete.' . PHP_EOL;
    }

    /**
     * Returns sources deduplicated by alias + normalised storageFolder.
     *
     * Prevents importing the same JSON file twice when the same physical
     * source is registered more than once (e.g. by Localization::init()).
     *
     * @return BaseLocalizationSource[]
     */
    private function getUniqueSources(): array
    {
        $seen = [];
        $unique = [];

        foreach (Localization::getSources() as $source) {
            $storageFolder = $source->getStorageFolder();
            $real = realpath($storageFolder);
            $normalisedStorage = $real !== false ? $real : $storageFolder;

            $key = $source->getAlias() . '|' . str_replace('\\', '/', $normalisedStorage);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $source;
            }
        }

        return $unique;
    }

    /**
     * Reads and processes one JSON export file for a locale × source pair.
     *
     * Skips with a warning if the file is missing or has an unsupported
     * format_version. Logs a warning for each stale hash (present in the
     * JSON but absent from the current StringCollection) and continues.
     *
     * @param LocaleInterface        $locale
     * @param BaseLocalizationSource $source
     * @param StringCollection       $collection
     */
    private function importLocaleSource(
        LocaleInterface $locale,
        BaseLocalizationSource $source,
        StringCollection $collection
    ): void {
        $storageFolder = $this->normalizeFolder($source->getStorageFolder());

        $jsonFile = sprintf(
            '%s/%s-%s-translations.json',
            $storageFolder,
            $locale->getName(),
            $source->getAlias()
        );

        if (!file_exists($jsonFile)) {
            echo sprintf(
                'WARNING: Export file not found, skipping [%s].' . PHP_EOL,
                $jsonFile
            );
            return;
        }

        $rawContent = file_get_contents($jsonFile);
        if ($rawContent === false) {
            echo sprintf(
                'WARNING: Could not read file [%s], skipping.' . PHP_EOL,
                $jsonFile
            );
            return;
        }

        $data = json_decode($rawContent, true);
        if (!is_array($data)) {
            echo sprintf(
                'WARNING: Could not parse JSON from [%s], skipping.' . PHP_EOL,
                $jsonFile
            );
            return;
        }

        $formatVersion = $data['format_version'] ?? null;
        if ($formatVersion !== 1) {
            echo sprintf(
                'WARNING: Unsupported format_version [%s] in [%s], skipping.' . PHP_EOL,
                is_scalar($formatVersion) ? (string)$formatVersion : 'invalid',
                $jsonFile
            );
            return;
        }

        $strings = $data['strings'] ?? [];
        if (!is_array($strings)) {
            echo sprintf(
                'WARNING: "strings" array missing or invalid in [%s], skipping.' . PHP_EOL,
                $jsonFile
            );
            return;
        }

        // Build hash→translation map: skip empty translations, warn on stale hashes
        $translations = $this->buildTranslationMap($strings, $collection, $jsonFile);

        $hashes = $collection->getHashesBySourceID($source->getID());
        $this->writeIniFiles($locale, $source, $storageFolder, $hashes, $translations);

        echo sprintf(
            'Imported [%d/%d] strings for [%s] source [%s].' . PHP_EOL,
            count($translations),
            count($hashes),
            $locale->getName(),
            $source->getAlias()
        );
    }

    /**
     * Builds a hash → translation map from the JSON "strings" array.
     *
     * - Entries with empty or missing translation are silently skipped.
     * - Entries whose hash is not in the current StringCollection are logged
     *   as stale and skipped.
     *
     * @param mixed[]          $strings     The value of data['strings'] from the JSON
     * @param StringCollection $collection
     * @param string           $jsonFile    Used in warning messages only
     * @return string[]                     hash => translated text
     */
    private function buildTranslationMap(
        array $strings,
        StringCollection $collection,
        string $jsonFile
    ): array {
        $translations = [];

        foreach ($strings as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $hash = isset($entry['hash']) && is_string($entry['hash']) ? $entry['hash'] : null;
            $translation = isset($entry['translation']) && is_string($entry['translation'])
                ? $entry['translation']
                : null;

            // Skip if hash or translation is missing, or translation is empty
            if ($hash === null || $translation === null || $translation === '') {
                continue;
            }

            // Warn on stale hashes
            if (!$collection->hashExists($hash)) {
                echo sprintf(
                    'WARNING: Stale hash [%s] in [%s] not found in current scan — skipped.' . PHP_EOL,
                    $hash,
                    $jsonFile
                );
                continue;
            }

            $translations[$hash] = $translation;
        }

        return $translations;
    }

    // -------------------------------------------------------------------------
    // INI file writing
    // -------------------------------------------------------------------------

    /**
     * Writes server and client INI files for one locale × source pair.
     *
     * Server INI  → all hashes that have a translation entry.
     * Client INI  → subset of server: only hashes with language type "JavaScript".
     *
     * @param LocaleInterface        $locale
     * @param BaseLocalizationSource $source
     * @param string                 $storageFolder  Normalised path (no trailing slash)
     * @param StringHash[]           $hashes         Hashes belonging to this source
     * @param string[]               $translations   hash => translated text
     */
    private function writeIniFiles(
        LocaleInterface $locale,
        BaseLocalizationSource $source,
        string $storageFolder,
        array $hashes,
        array $translations
    ): void {
        $serverFile = sprintf(
            '%s/%s-%s-server.ini',
            $storageFolder,
            $locale->getName(),
            $source->getAlias()
        );

        $clientFile = sprintf(
            '%s/%s-%s-client.ini',
            $storageFolder,
            $locale->getName(),
            $source->getAlias()
        );

        // Server INI is user-editable; client INI is derived (DO NOT EDIT)
        $serverWriter = new LocalizationWriter($locale, 'Serverside', $serverFile);
        $serverWriter->makeEditable();

        $clientWriter = new LocalizationWriter($locale, 'Clientside', $clientFile);

        foreach ($hashes as $hash) {
            $hashKey = $hash->getHash();

            if (!isset($translations[$hashKey])) {
                continue;
            }

            $text = $translations[$hashKey];

            $serverWriter->addHash($hashKey, $text);

            if ($hash->hasLanguageType(JavaScriptLanguage::LANGUAGE_ID)) {
                $clientWriter->addHash($hashKey, $text);
            }
        }

        $serverWriter->writeFile();
        $clientWriter->writeFile();
    }

    // -------------------------------------------------------------------------
    // Path utilities
    // -------------------------------------------------------------------------

    /**
     * Normalises a folder path using realpath() with a forward-slash fallback.
     * Returns the path without a trailing slash.
     *
     * @param string $folder
     * @return string
     */
    private function normalizeFolder(string $folder): string
    {
        $real = realpath($folder);
        return rtrim(str_replace('\\', '/', $real !== false ? $real : $folder), '/');
    }
}
