<?php

    $root = __DIR__;
    $autoload = realpath($root.'/../vendor/autoload.php');
    
    if(!file_exists($autoload)) {
        die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
    }
    
    require_once $autoload;

    // the folder in which the localization files are stored
    $storageFolder = $root.'/data';
    
    // the folder in which to search for files to extract texts from
    $sourcesPath = $root.'/sources';
    
    // add the locales we wish to manage (en_UK is always present)
    \AppLocalize\Localization::addAppLocale('de_DE');
    
    // register the sources folder.
    \AppLocalize\Localization::addSourceFolder(
        'main', 
        'Main translation texts', 
        'Core files', 
        $storageFolder, 
        $sourcesPath
    )
    ->excludeFolder('excludeme');

   // has to be called last after all sources and locales have been configured
    \AppLocalize\Localization::configure(
        $root.'/data/storage.json',
        $root.'/data/client-libraries'
    );
    
    // create the editor and start it
    $editor = \AppLocalize\Localization::createEditor();
    $editor->display();