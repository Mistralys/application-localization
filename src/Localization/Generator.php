<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_ClientGenerator
{
    const ERROR_JS_FOLDER_NOT_FOUND = 39302;
    
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
    
    public function __construct()
    {
        $this->translator = Localization::getTranslator();
        $this->targetFolder = Localization::getClientFolder();
    }
    
    public function writeFiles(bool $force=false) : void
    {
        $this->force = $force;
        
        $this->writeLocaleFiles();
        $this->writeLibraryFiles();
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
    
    protected function writeLocaleFiles()
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
    
    protected $libraries = array(
        'translator.js',
        'md5.min.js'
    );
    
    protected function writeLibraryFiles()
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
            
            \AppUtils\FileHelper::copyFile($sourceFile, $targetFile);
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
     *
     * @return string
     */
    protected function writeLocaleFile(Localization_Locale $locale)
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
        
        \AppUtils\FileHelper::saveFile($path, $content);
    }
}