<?php
/**
 * Example UI script
 * 
 * @package AppLocalize
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    $root = __DIR__;
    
    $autoload = realpath($root.'/../vendor/autoload.php');
    
    // we need the autoloader to be present
    if(!file_exists($autoload)) {
        die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
    }

   /**
    * The composer autoloader
    */
    require_once $autoload;

    // the folder in which the localization .ini files are stored
    $storageFolder = $root.'/data';
    
    // where the source files analysis cache should be stored
    $storageFile = $storageFolder.'/storage.json';
    
    // the folder in which to search for files to extract texts from
    $sourcesPath = $root.'/sources';
    
    // where the javascript includes should be written
    $librariesFolder = $root.'/data/client-libraries';
    
    // add the locales we wish to manage (en_UK is always present)
    \AppLocalize\Localization::addAppLocale('de_DE');
    \AppLocalize\Localization::addAppLocale('fr_FR');
    
    // register the sources folder.
    $source = \AppLocalize\Localization::addSourceFolder(
        'main', 
        'Main translation texts', 
        'Core files', 
        $storageFolder, 
        $sourcesPath
    );
    
    // this folder will be ignored when searching for source files to analyze
    $source->excludeFolder('excludeme'); 

    // has to be called last after all sources and locales have been configured
    \AppLocalize\Localization::configure($storageFile, $librariesFolder);
    
    // create the editor UI and start it
    $editor = \AppLocalize\Localization::createEditor();
    $editor->display();
    