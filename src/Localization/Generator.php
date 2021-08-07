<?php

declare(strict_types=1);

namespace AppLocalize;

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class Localization_ClientGenerator
{
    const ERROR_JS_FOLDER_NOT_FOUND = 39302;
    const ERROR_TARGET_FOLDER_NOT_WRITABLE = 39303;
    const ERROR_INVALID_FILES_IDENTIFIER = 39304;

    const FILES_LIBRARIES = 'libs';
    const FILES_LOCALES = 'locales';
    const FILES_CACHE_KEY = 'cachekey';
    
   /**
    * @var bool
    */
    protected $force = false;
    
   /**
    * @var Localization_Translator
    */
    protected $translator;
    
   /**
    * @var string
    */
    protected $targetFolder;
    
   /**
    * @var string
    */
    protected $cacheKeyFile;

    /**
     * @var string
     */
    protected $cacheKey = '';

    /**
     * @var array<string,bool>
     */
    protected $written = array(
        self::FILES_CACHE_KEY => false,
        self::FILES_LIBRARIES => false,
        self::FILES_LOCALES => false
    );
    
    public function __construct()
    {
        $this->translator = Localization::getTranslator();
        $this->targetFolder = Localization::getClientFolder();
        $this->cacheKeyFile = $this->targetFolder.'/cachekey.txt';
        
        $this->initCache();
    }

    /**
     * @throws FileHelper_Exception
     */
    protected function initCache() : void
    {
        // ignore it if it does not exist.
        if(!file_exists($this->cacheKeyFile)) {
            return;
        }
        
        $this->cacheKey = FileHelper::readContents($this->cacheKeyFile);
    }

    /**
     * @param bool $force
     * @throws Localization_Exception
     * @throws FileHelper_Exception
     */
    public function writeFiles(bool $force=false) : void
    {
        // reset the write states for all files
        $fileIDs = array_keys($this->written);
        foreach($fileIDs as $fileID) {
            $this->written[$fileID] = false;
        }
        
        // no client libraries folder set: ignore.
        if(empty($this->targetFolder)) {
            return;
        }
        
        FileHelper::createFolder($this->targetFolder);
        
        if(!is_writable($this->targetFolder)) 
        {
            throw new Localization_Exception(
                sprintf(
                    'Cannot write client libraries: folder [%s] is not writable.', 
                    basename($this->targetFolder)
                ),
                sprintf(
                    'Tried accessing folder at [%s].',
                    $this->targetFolder
                ),
                self::ERROR_TARGET_FOLDER_NOT_WRITABLE
            );
        }
        
        if($this->cacheKey !== Localization::getClientLibrariesCacheKey()) {
            $force = true;
        }
        
        $this->force = $force;
        
        $this->writeLocaleFiles();
        $this->writeLibraryFiles();
        $this->writeCacheKey();
    }
    
   /**
    * Retrieves a list of all localization client 
    * files that are written to disk. This includes
    * the locale files and the libraries required
    * to make it work clientside.
    * 
    * @return string[]
    */
    public function getFilesList() : array
    {
        $files = array();
        
        foreach($this->libraries as $fileName)
        {
            $files[] = $this->getLibraryFilePath($fileName);
        }
        
        $locales = Localization::getAppLocales();
        
        foreach($locales as $locale)
        {
            if($locale->isNative()) {
                continue;
            }
            
            $files[] = $this->getLocaleFilePath($locale);
        }
        
        return $files;
    }
    
    protected function writeLocaleFiles() : void
    {
        $locales = Localization::getAppLocales();
        
        foreach($locales as $locale)
        {
            if($locale->isNative()) {
                continue;
            }
            
            $this->writeLocaleFile($locale);
        }
    }

    /**
     * @var string[]
     */
    protected $libraries = array(
        'translator.js',
        'md5.min.js'
    );

    /**
     * @throws FileHelper_Exception
     * @throws Localization_Exception
     */
    protected function writeLibraryFiles() : void
    {
        $sourceFolder = realpath(__DIR__.'/../js');
        
        if($sourceFolder === false) 
        {
            throw new Localization_Exception(
                'Unexpected folder structure encountered.',
                sprintf(
                    'The [js] folder is not in the expected location at [%s].',
                    $sourceFolder
                ),
                self::ERROR_JS_FOLDER_NOT_FOUND
            );
        }
        
        foreach($this->libraries as $fileName)
        {
            $targetFile = $this->getLibraryFilePath($fileName);
            
            if(file_exists($targetFile) && !$this->force) {
                continue;
            }
            
            $sourceFile = $sourceFolder.'/'.$fileName;
            
            FileHelper::copyFile($sourceFile, $targetFile);
            
            $this->written[self::FILES_LIBRARIES] = true;
        }
    }
    
    protected function getLibraryFilePath(string $fileName) : string
    {
        return $this->targetFolder.'/'.$fileName;
    }
    
    protected function getLocaleFilePath(Localization_Locale $locale) : string
    {
        return sprintf(
            '%s/locale-%s.js',
            $this->targetFolder,
            $locale->getLanguageCode()
        );
    }
    
    /**
     * Generates the JavaScript code to register all
     * clientside strings using the bundled client
     * libraries.
     *
     * The application has to decide how to serve this
     * content in its pages: There are two main ways to
     * do it:
     *
     * 1) Save it to a Javascript file and include that
     * 2) Serve it as application/javascript content via PHP
     *
     * NOTE: Caching has to be handled on the application
     * side. This method creates a fresh collection each time.
     */
    protected function writeLocaleFile(Localization_Locale $locale) : void
    {
        $path = $this->getLocaleFilePath($locale);
        
        if(file_exists($path) && !$this->force) {
            return;
        }
        
        $strings = $this->translator->getClientStrings($locale);
        
        $tokens = array();
        foreach($strings as $hash => $text)
        {
            if(empty($text)) {
                continue;
            }
            
            $tokens[] = sprintf(
                "a('%s',%s)",
                $hash,
                json_encode($text)
            );
        }
        
        if(empty($tokens))
        {
            $content = '/* No strings found. */';
        }
        else
        {
            $content =
            '/**'.PHP_EOL.
            ' * @generated '.date('Y-m-d H:i:s').PHP_EOL.
            ' */'.PHP_EOL.
            'AppLocalize_Translator.'.implode('.', $tokens).';';
        }
        
        FileHelper::saveFile($path, $content);
        
        $this->written[self::FILES_LOCALES] = true;
    }
    
   /**
    * Generates the cache key file, which is used to determine
    * automatically whether the client libraries need to be 
    * refreshed.
    */
    protected function writeCacheKey() : void
    {
        if(file_exists($this->cacheKeyFile) && !$this->force) {
            return;
        }
        
        FileHelper::saveFile($this->cacheKeyFile, Localization::getClientLibrariesCacheKey());
        
        $this->written[self::FILES_CACHE_KEY] = true;
    }

    /**
     * Whether the specified files have been written to
     * disk this session.
     *
     * NOTE: only useful when called after <code>writeFiles</code>.
     *
     * @param string $filesID
     *
     * @return bool
     * @throws Localization_Exception
     * @see Localization_ClientGenerator::FILES_LOCALES
     * @see Localization_ClientGenerator::FILES_LIBRARIES
     * @see Localization_ClientGenerator::FILES_CACHE_KEY
     */
    public function areFilesWritten(string $filesID) : bool
    {
        if(isset($this->written[$filesID])) {
            return $this->written[$filesID];
        }
        
        throw new Localization_Exception(
            'Invalid written files identifier.',
            sprintf(
                'Unknown identifier [%s]. Valid identifiers are: [%s] (use class constants).',
                $filesID,
                implode(', ', array_keys($this->written))
            ),
            self::ERROR_INVALID_FILES_IDENTIFIER
        );
    }
}
