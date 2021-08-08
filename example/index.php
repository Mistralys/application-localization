<?php
/**
 * Example UI script
 * 
 * @package AppLocalize
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    declare(strict_types=1);

    use AppLocalize\Localization;
    use function AppLocalize\t;

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
    $storageFolder = $root.'/localization';
    
    // where the source files analysis cache should be stored
    $storageFile = $storageFolder.'/storage.json';
    
    // where the javascript includes should be written
    $librariesFolder = $root.'/client-libraries';
    
    // add the locales we wish to manage (en_UK is always present)
    Localization::addAppLocale('de_DE');
    Localization::addAppLocale('fr_FR');
    
    define('LOCALIZATION_EXAMPLES_SOURCE_ID', 'localization-examples');
    
    // register the sources folder: this file's folder
    // so even this file is included in the search.
    $source = Localization::addSourceFolder(
        LOCALIZATION_EXAMPLES_SOURCE_ID, 
        'Example texts', 
        'Examples', 
        $storageFolder, 
        $root
    );
    
    // these folders will be ignored when searching for source files to analyze
    $source->excludeFolders(array(
        'localization',
        'client-libraries',
        'excludeme'
    )); 

    // has to be called last after all sources and locales have been configured
    Localization::configure($storageFile, $librariesFolder);
    
    // create the editor UI and start it
    $editor = Localization::createEditor();

    // selects the default texts source we wish to edit
    $editor->selectDefaultSource(LOCALIZATION_EXAMPLES_SOURCE_ID);
    
    // customize the name shown in the UI (and translatable
    // too, using the t() function)
    $editor->setAppName(t('Example translator'));
    
    $editor->setBackURL(
        'https://github.com/Mistralys/application-localization', 
        t('Project Github page')
    );
    
    // display the editor UI
    $editor->display();
