<?php
/**
 * Main bootstrapper used to set up the testsuites environment.
 * 
 * @package Localization
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    declare(strict_types=1);

    use AppLocalize\Localization;

    const TESTS_ROOT = __DIR__;

    $autoloader = realpath(TESTS_ROOT.'/../vendor/autoload.php');
    if($autoloader === false) {
        die('Cannot run tests: the autoload is not present. Please run composer update first.');
    }
    
    $sourcesPath = realpath(__DIR__.'/../example/sources');
    if($sourcesPath === false) {
        die('Cannot run tests: the example sources could not be found.');
    }
    
    // where the test files are stored
    $storageFolder = TESTS_ROOT.'/storage';
    
    // where the source files analysis cache should be stored
    $storageFile = $storageFolder.'/storage.json';
    
    // where the javascript includes should be written
    $librariesFolder = $storageFolder;
    
    // register the sources folder.
    $source = Localization::addSourceFolder(
        'main',
        'Main translation texts',
        'Core files',
        $storageFolder,
        $sourcesPath
    );
    
    // this folder will be ignored when searching for source files to analyze
    $source->excludeFolder('excludeme');
    
    // has to be called last after all sources and locales have been configured
    Localization::configure($storageFile, $librariesFolder);
    