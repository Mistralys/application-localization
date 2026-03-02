<?php
/**
 * Default Localization configuration for the Application Localization package's own translations.
 *
 * This file is the default configuration bootstrap used by both TranslationExporter and
 * TranslationImporter when no LOCALIZATION_TOOLS_CONFIG constant or environment variable
 * is defined. It sets up the Localization facade for the package's own strings so that
 * `composer export-translations` and `composer import-translations` work out-of-the-box
 * on a clean checkout.
 *
 * @package AppLocalize
 * @subpackage Tools
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

use AppLocalize\Localization;

$root = __DIR__;

$autoload = realpath($root . '/vendor/autoload.php');

if ($autoload === false) {
    echo 'ERROR: Autoloader not present. Run composer install first.' . PHP_EOL;
    exit(1);
}

require_once $autoload;

// The directory in which the .ini translation files and storage.json are stored.
$storageFolder = $root . '/localization';

// The path to the storage.json scan cache file.
$storageFile = $storageFolder . '/storage.json';

// The directory where generated JavaScript client library files are written.
$librariesFolder = $storageFolder;

// Register the application locales managed by this package.
Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');

// NOTE: Localization::init() already registers the package's own
// 'application-localization' source (src/ → localization/).
// Do NOT call addSourceFolder() again here to avoid duplicate source registrations.

// Finalise the configuration — must be called after all sources and locales are registered.
Localization::configure($storageFile, $librariesFolder);
