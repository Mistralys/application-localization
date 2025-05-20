<?php
/**
 * Translation UI for the localizable strings in the package.
 *
 * @package Localization Utils
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

use AppLocalize\Localization;

$installFolder = __DIR__.'/../';

$autoload = __DIR__.'/../vendor/autoload.php';

// we need the autoloader to be present
if(!file_exists($autoload)) {
    die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
}

/**
 * The composer autoloader
 */
require_once $autoload;

// add the locales we wish to manage (en_US is always present)
Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');

// has to be called last after all sources and locales have been configured
Localization::configure(__DIR__.'/storage.json', '');

// create the editor UI and start it
$editor = Localization::createEditor();
$editor->display();
