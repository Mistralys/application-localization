<?php

namespace AppLocalize;

class Localization_ClientGenerator
{
    const ERROR_CANNOT_WRITE_LOCALE_FILE = 39301;
    
    const ERROR_JS_FOLDER_NOT_FOUND = 39302;
    
    const ERROR_CANNOT_COPY_LIBRARY_FILE = 39303;
    
   /**
    * @var bool
    */
    protected $force = false;
    
   /**
    * @var Localization_Translator
    */
    protected $translator;
    
    protected $targetFolder;
    
    public function __construct()
    {
        $this->translator = Localization::getTranslator();
        $this->targetFolder = Localization::getClientFolder();
    }
    
    public function writeFiles()
    {
        $this->writeLocaleFiles();
        $this->writeLibraryFiles();
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
    
    protected function writeLibraryFiles()
    {
        $libraries = array(
            'translator.js',
            'md5.min.js'
        );
        
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
        
        foreach($libraries as $fileName)
        {
            $targetFile = $this->targetFolder.'/'.$fileName;
            
            if(file_exists($targetFile) && !$this->force) {
                continue;
            }
            
            $sourceFile = $sourceFolder.'/'.$fileName;
            
            if(!copy($sourceFile, $targetFile)) 
            {
                throw new Localization_Exception(
                    'Cannot copy localization client library file.',
                    sprintf(
                        'Tried copying the file [%s] to the target location at [%s].',
                        $fileName,
                        $targetFile
                    ),
                    self::ERROR_CANNOT_COPY_LIBRARY_FILE
                );    
            }
        }
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
        $path = sprintf(
            '%s/locale-%s.js',
            $this->targetFolder,
            $locale->getShortName()
        );
        
        if(file_exists($path) && !$this->force) {
            return;
        }
        
        $strings = array();
        
        if($this->translator->hasStrings($locale)) {
            $strings = $this->translator->getStrings($locale);
        }
        
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
            return '/* No strings found. */';
        }
        
        $content =
        '/**'.PHP_EOL.
        ' * @generated '.date('Y-m-d H:i:s').PHP_EOL.
        ' */'.PHP_EOL.
        'AppLocalize_Translator.'.implode('.', $tokens).';';
        
        if(file_put_contents($path, $content)) {
            return; 
        }
        
        throw new Localization_Exception(
            'Cannot write localization client library file.',
            sprintf(
                'Tried writing the file for locale [%s] with path [%s].',
                $locale->getName(),
                $path
            ),
            self::ERROR_CANNOT_WRITE_LOCALE_FILE
        );
    }
}