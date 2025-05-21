<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Translator;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\LocalizationException;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;

class ClientFilesGenerator
{
    public const ERROR_JS_FOLDER_NOT_FOUND = 39302;
    public const ERROR_TARGET_FOLDER_NOT_WRITABLE = 39303;

    protected LocalizationTranslator $translator;
    protected ?FolderInfo $targetFolder = null;
    protected ?FileInfo $cacheKeyFile = null;
    protected ?string $cacheKey = null;

    public function __construct()
    {
        $this->translator = Localization::getTranslator();

        Localization::onLocaleChanged(function () {
            $this->handleLocaleChanged();
        });

        Localization::onCacheKeyChanged(function () {
            $this->handleCacheKeyChanged();
        });

        Localization::onClientFolderChanged(function () {
            $this->handleFolderChanged();
        });
    }

    private function getTargetFolder() : ?FolderInfo
    {
        $folder = Localization::getClientFolder();

        if(!empty($folder)) {
            return FolderInfo::factory($folder);
        }

        return null;
    }

    private function getCacheKeyFile() : ?FileInfo
    {
        $folder = $this->getTargetFolder();

        if($folder !== null) {
            return FileInfo::factory($folder.'/cachekey.txt');
        }

        return null;
    }

    private function handleLocaleChanged() : void
    {
        self::log('EVENT | Locale changed | Resetting internal cache.');

        $this->handleCacheKeyChanged();
    }

    private function handleCacheKeyChanged() : void
    {
        self::log('EVENT | Cache Key changed | Resetting internal cache.');

        $this->cacheKey = null;
        self::$systemKey = null;
    }

    private function handleFolderChanged() : void
    {
        self::log('EVENT | Client folder changed | Resetting internal cache.');

        $this->targetFolder = null;
        $this->cacheKeyFile = null;
    }

    public static function setLoggingEnabled(bool $enabled) : void
    {
        self::$logging = $enabled;
    }

    private static bool $logging = false;

    /**
     * @param string $message
     * @param string|int|float|NULL ...$args
     * @return void
     */
    private static function log(string $message, ...$args) : void
    {
        if(self::$logging === false) {
            return;
        }

        echo sprintf($message, ...$args).PHP_EOL;
    }

    public function getCacheKey() : ?string
    {
        if(isset($this->cacheKey)) {
            return $this->cacheKey;
        }

        $file = $this->getCacheKeyFile();

        if($file !== null && $file->exists()) {
            $this->cacheKey = $file->getContents();
        }

        return $this->cacheKey;
    }

    /**
     * Writes the localization client files to disk.
     *
     * @param bool $force
     * @throws LocalizationException
     * @throws FileHelper_Exception
     */
    public function writeFiles(bool $force=false) : void
    {
        self::log('Write Files');

        if(!$force && $this->getCacheKey() === self::getSystemKey()) {
            self::log('Write Files | SKIP | Still up to date.');
            return;
        }

        $targetFolder = $this->getTargetFolder();

        // no client libraries folder set: ignore.
        if($targetFolder === null) {
            self::log('Write Files | SKIP | No folder set.');
            return;
        }

        $targetFolder
            ->create()
            ->requireReadable(self::ERROR_TARGET_FOLDER_NOT_WRITABLE);

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
        
        foreach(self::getTargetLocales() as $locale)
        {
            $files[] = $this->getLocaleFilePath($locale);
        }
        
        return $files;
    }
    
    protected function writeLocaleFiles() : void
    {
        self::log('Write Files | Writing locales.');

        foreach(self::getTargetLocales() as $locale)
        {
            $this->writeLocaleFile($locale);
        }
    }

    /**
     * @var string[]
     */
    protected array $libraries = array(
        'translator.js',
        'md5.min.js'
    );

    /**
     * @throws FileHelper_Exception
     * @throws LocalizationException
     */
    protected function writeLibraryFiles() : void
    {
        $sourceFolder = FolderInfo::factory(__DIR__.'/../../js');

        if(!$sourceFolder->exists())
        {
            throw new LocalizationException(
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
            $sourceFile = $sourceFolder.'/'.$fileName;
            
            FileHelper::copyFile($sourceFile, $targetFile);
        }
    }

    /**
     * @return LocaleInterface[]
     */
    protected static function getTargetLocales() : array
    {
        $result = array();

        foreach(Localization::getAppLocales() as $locale) {
            if($locale->isNative()) {
                continue;
            }

            $result[] = $locale;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    protected static function getTargetLocaleIDs() : array
    {
        $result = array();

        foreach(self::getTargetLocales() as $locale) {
            $result[] = $locale->getName();
        }

        return $result;
    }
    
    protected function getLibraryFilePath(string $fileName) : string
    {
        return $this->getTargetFolder().'/'.$fileName;
    }
    
    protected function getLocaleFilePath(LocaleInterface $locale) : string
    {
        return sprintf(
            '%s/locale-%s.js',
            $this->getTargetFolder(),
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
     * 1) Save it to a JavaScript file and include that
     * 2) Serve it as application/javascript content via PHP
     *
     * > NOTE: Caching has to be handled on the application side.
     * > This method creates a fresh collection each time.
     *
     * @param LocaleInterface $locale
     */
    protected function writeLocaleFile(LocaleInterface $locale) : void
    {
        self::log('Write Files | Writing locale [%s].', $locale->getName());

        $path = $this->getLocaleFilePath($locale);
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
                JSONConverter::var2json($text)
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
    }

    private static ?string $systemKey = null;

    public static function getSystemKey() : string
    {
        if(!isset(self::$systemKey)) {
            self::$systemKey = sprintf(
                'Lib:%s|System:%s|Locales:%s',
                Localization::getClientLibrariesCacheKey(),
                Localization::getVersion(),
                implode(',', self::getTargetLocaleIDs())
            );

            self::log('System Key generated: [%s].', self::$systemKey);
        }

        return self::$systemKey;
    }

   /**
    * Generates the cache key file, which is used to determine
    * automatically whether the client libraries need to be 
    * refreshed.
    */
    protected function writeCacheKey() : void
    {
        $this->cacheKey = self::getSystemKey();

        $file = $this->getCacheKeyFile();

        if($file !== null) {
            $file->putContents($this->cacheKey);
        }

        self::log('Write Files | Cache key written.');
    }

    /**
     * Whether the localization files have been written to
     * disk this session.
     *
     * @return bool
     */
    public function areFilesWritten() : bool
    {
        return $this->getCacheKey() === self::getSystemKey();
    }
}
