<?php

/**
 * @package AppLocalize
 * @subpackage Tools
 */

declare(strict_types=1);

namespace AppLocalize\Tools;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Scanner\StringHash;
use AppLocalize\Localization\Source\BaseLocalizationSource;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Generates per-locale per-source JSON export files from the
 * storage.json scan results and existing INI translation files.
 *
 * Each export file is written as:
 *   {storageFolder}/{locale}-{source-alias}-translations.json
 *
 * Entry point: `composer export-translations`
 *
 * @package AppLocalize
 * @subpackage Tools
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class TranslationExporter
{
    /**
     * Normalised, forward-slash-terminated absolute path to the
     * Composer project root (two levels up from src/Tools/).
     *
     * @var string
     */
    private string $projectRoot;

    // -------------------------------------------------------------------------
    // Constructor / Factory
    // -------------------------------------------------------------------------

    private function __construct()
    {
        $root = realpath(__DIR__ . '/../../');
        $this->projectRoot = rtrim(
            str_replace('\\', '/', $root !== false ? $root : __DIR__ . '/../../'),
            '/'
        ) . '/';
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Composer entry point: `composer export-translations`
     */
    public static function run(): void
    {
        self::loadConfig();
        self::create()->exportAll();
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
     * Run the export programmatically (without loading config).
     *
     * Localization must have been configured before calling this.
     */
    public function export(): void
    {
        $this->exportAll();
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
        $defaultPath = getcwd() . '/localization-tools-config.php';
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
    // Export logic
    // -------------------------------------------------------------------------

    /**
     * Outer loop: iterates all registered app locales × sources and exports
     * one JSON file per combination, skipping the native (en_GB) locale.
     */
    private function exportAll(): void
    {
        $scanner = Localization::createScanner();

        if ($scanner->isScanAvailable()) {
            $scanner->load();
        } else {
            echo 'WARNING: storage.json not found — export will contain empty strings arrays.' . PHP_EOL;
        }

        $collection = $scanner->getCollection();
        $sources = $this->getUniqueSources();

        foreach (Localization::getAppLocales() as $locale) {
            if ($locale->getName() === Localization::BUILTIN_LOCALE_NAME) {
                continue;
            }

            $translator = Localization::getTranslator($locale);
            $translations = $translator->getStrings($locale);

            foreach ($sources as $source) {
                $hashes = $collection->getHashesBySourceID($source->getID());
                $this->exportLocaleSource($locale, $source, $hashes, $translations);
            }
        }

        echo 'Export complete.' . PHP_EOL;
    }

    /**
     * Returns sources deduplicated by alias + normalised storageFolder.
     *
     * Prevents writing the same export file twice when the same physical
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
     * Builds and writes one JSON export file for a locale × source pair.
     *
     * @param LocaleInterface        $locale
     * @param BaseLocalizationSource $source
     * @param StringHash[]           $hashes
     * @param string[]               $translations  hash => translated text map
     */
    private function exportLocaleSource(
        LocaleInterface $locale,
        BaseLocalizationSource $source,
        array $hashes,
        array $translations
    ): void {
        $entries = [];

        foreach ($hashes as $hash) {
            $entries[] = $this->buildEntry($hash, $translations[$hash->getHash()] ?? '');
        }

        $payload = [
            'format_version' => 1,
            'locale'         => $locale->getName(),
            'locale_label'   => $locale->getLabel(),
            'source_alias'   => $source->getAlias(),
            'source_label'   => $source->getLabel(),
            'exported_at'    => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'strings'        => $entries,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            echo sprintf(
                'ERROR: Failed to JSON-encode export for locale [%s] source [%s].' . PHP_EOL,
                $locale->getName(),
                $source->getAlias()
            );
            return;
        }

        $storageFolder = $source->getStorageFolder();
        $real = realpath($storageFolder);
        $normalisedStorage = $real !== false ? $real : $storageFolder;

        $outputFile = sprintf(
            '%s/%s-%s-translations.json',
            rtrim(str_replace('\\', '/', $normalisedStorage), '/'),
            $locale->getName(),
            $source->getAlias()
        );

        $written = file_put_contents($outputFile, $json);

        if ($written === false) {
            echo sprintf(
                'ERROR: Could not write export file [%s].' . PHP_EOL,
                $outputFile
            );
            return;
        }

        echo sprintf(
            'Exported [%d] strings to [%s].' . PHP_EOL,
            count($entries),
            $outputFile
        );
    }

    // -------------------------------------------------------------------------
    // Entry assembly helpers
    // -------------------------------------------------------------------------

    /**
     * Assembles one entry in the "strings" array.
     *
     * @param StringHash $hash
     * @param string     $translation  Current translated text, or "" if absent
     * @return array<string,mixed>
     */
    private function buildEntry(StringHash $hash, string $translation): array
    {
        $text = $hash->getText();

        return [
            'hash'        => $hash->getHash(),
            'source_text' => $text !== null ? $text->getText() : '',
            'context'     => $text !== null ? $text->getExplanation() : '',
            'files'       => $this->getFilePaths($hash),
            'translation' => $translation,
        ];
    }

    /**
     * Collects all "relativePath:line" strings from every StringInfo
     * occurrence of the hash, normalising absolute paths to be relative
     * to the project root.
     *
     * @param StringHash $hash
     * @return string[]
     */
    private function getFilePaths(StringHash $hash): array
    {
        $result = [];

        foreach ($hash->getStrings() as $info) {
            $absolute = $info->getSourceFile();

            // Normalise to forward slashes
            $normalised = str_replace('\\', '/', $absolute);

            // Attempt realpath() to resolve any /../ segments
            $real = realpath($absolute);
            if ($real !== false) {
                $normalised = str_replace('\\', '/', $real);
            } else {
                // Manual collapse of /../ and // for paths that no longer exist
                $parts = explode('/', $normalised);
                $resolved = [];
                foreach ($parts as $part) {
                    if ($part === '..') {
                        if (!empty($resolved)) {
                            array_pop($resolved);
                        }
                    } elseif ($part !== '' || empty($resolved)) {
                        // preserve leading empty segment for absolute paths
                        $resolved[] = $part;
                    }
                }
                $normalised = implode('/', $resolved);
            }

            // Strip project root prefix to make the path relative
            if (str_starts_with($normalised, $this->projectRoot)) {
                $normalised = substr($normalised, strlen($this->projectRoot));
            }

            $result[] = $normalised . ':' . $info->getLine();
        }

        // Remove duplicates (same file:line may appear from multiple source registrations)
        return array_values(array_unique($result));
    }
}
