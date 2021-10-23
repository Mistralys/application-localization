<?php
/**
 * File containing the {@link Localization_Editor} class.
 * 
 * @package Localization
 * @subpackage Editor
 * @see Localization_Translator
 */

declare(strict_types=1);

namespace AppLocalize;

use AppUtils\OutputBuffering_Exception;
use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;
use AppUtils\Request;

/**
 * User Interface handler for editing localization files.
 *
 * @package Localization
 * @subpackage Editor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Localization_Editor implements Interface_Optionable
{
    use Traits_Optionable;
    
    const MESSAGE_INFO = 'info';
    const MESSAGE_ERROR = 'danger';
    const MESSAGE_WARNING = 'warning';
    const MESSAGE_SUCCESS = 'success';
    
    const ERROR_NO_SOURCES_AVAILABLE = 40001;
    const ERROR_LOCAL_PATH_NOT_FOUND = 40002;
    const ERROR_STRING_HASH_WITHOUT_TEXT = 40003;

   /**
    * @var string
    */
    protected $installPath;
    
   /**
    * @var Localization_Source[]
    */
    protected $sources;
    
   /**
    * @var Request
    */
    protected $request;
    
   /**
    * @var Localization_Source
    */
    protected $activeSource;
    
   /**
    * @var Localization_Scanner
    */
    protected $scanner;
    
   /**
    * @var Localization_Locale[]
    */
    protected $appLocales = array();
    
   /**
    * @var Localization_Locale
    */
    protected $activeAppLocale;
    
   /**
    * @var Localization_Editor_Filters
    */
    protected $filters;

   /**
    * @var array<string,string>
    */
    protected $requestParams = array();
    
   /**
    * @var string
    */
    protected $varPrefix = 'applocalize_';

    /**
     * @var int
     */
    protected $perPage = 20;

    /**
     * @throws Localization_Exception
     * @see \AppLocalize\Localization_Editor::ERROR_LOCAL_PATH_NOT_FOUND
     */
    public function __construct()
    {
        $path = realpath(__DIR__.'/../');
        if($path === false)
        {
            throw new Localization_Exception(
                'Local path not found',
                sprintf(
                    'Could not get the parent folder\'s real path from [%s].',
                    __DIR__
                ),
                self::ERROR_LOCAL_PATH_NOT_FOUND
            );
        }

        $this->installPath = $path;
        $this->request = new Request();
        $this->scanner = Localization::createScanner();
        $this->scanner->load();

        $this->initSession();
        $this->initAppLocales();
    }
    
    public function getRequest() : Request
    {
        return $this->request;
    }
    
   /**
    * Adds a request parameter that will be persisted in all URLs
    * within the editor. This can be used when integrating the
    * editor in an existing page that needs specific request params.
    * 
    * @param string $name
    * @param string $value
    * @return Localization_Editor
    */
    public function addRequestParam(string $name, string $value) : Localization_Editor
    {
        $this->requestParams[$name] = $value;
        return $this;
    }
    
    public function getActiveSource() : Localization_Source
    {
        return $this->activeSource;
    }
    
    protected function initSession() : void
    {
        if(session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if(!isset($_SESSION['localization_messages'])) {
            $_SESSION['localization_messages'] = array();
        }
    }
    
    public function getVarName(string $name) : string
    {
        return $this->varPrefix.$name;
    }

    /**
     * @throws Localization_Exception
     */
    protected function initSources() : void
    {
        $this->sources = Localization::getSources();
        
        if(empty($this->sources)) 
        {
            throw new Localization_Exception(
                'Cannot start editor: no sources defined.',
                null,
                self::ERROR_NO_SOURCES_AVAILABLE
            );
        }
        
        $activeID = $this->request->registerParam($this->getVarName('source'))->setEnum(Localization::getSourceIDs())->get();
        if(empty($activeID)) {
            $activeID = $this->getDefaultSourceID();
        }
        
        $this->activeSource = Localization::getSourceByID($activeID);
    }
    
    protected function getDefaultSourceID() : string
    {
        $default = $this->getOption('default-source');
        if(!empty($default) && Localization::sourceAliasExists($default)) {
            return Localization::getSourceByAlias($default)->getID();
        }
        
        return $this->sources[0]->getID();
    }
    
    protected function initAppLocales() : void
    {
        $names = array();
        
        $locales = Localization::getAppLocales();
        foreach($locales as $locale) {
            if(!$locale->isNative()) {
                $this->appLocales[] = $locale;
                $names[] = $locale->getName();
            }
        }
        
        // use the default locale if no other is available.
        if(empty($names)) {
            $this->activeAppLocale = Localization::getAppLocale();
            return;
        }
       
        $activeID = $this->request->registerParam($this->getVarName('locale'))->setEnum($names)->get();
        if(empty($activeID)) {
            $activeID = $this->appLocales[0]->getName();
        }
        
        $this->activeAppLocale = Localization::getAppLocaleByName($activeID);
        
        Localization::selectAppLocale($activeID);
    }

    /**
     * @return Localization_Locale[]
     */
    public function getAppLocales() : array
    {
        return $this->appLocales;
    }

    /**
     * @return Localization_Source[]
     */
    public function getSources() : array
    {
        return $this->sources;
    }

    public function getBackURL() : string
    {
        return strval($this->getOption('back-url'));
    }

    public function getBackButtonLabel() : string
    {
        return strval($this->getOption('back-label'));
    }

    protected function handleActions() : void
    {
        $this->initSources();
        
        $this->filters = new Localization_Editor_Filters($this);
        
        if($this->request->getBool($this->getVarName('scan'))) 
        {
            $this->executeScan();
        } 
        else if($this->request->getBool($this->getVarName('save'))) 
        {
            $this->executeSave();
        }
    }
    
    public function getScanner() : Localization_Scanner
    {
        return $this->scanner;
    }

    /**
     * @return string
     * @throws OutputBuffering_Exception
     */
    public function render() : string
    {
        $this->handleActions();
        
        return (new Localization_Editor_Template_PageScaffold($this))->render();
    }

    /**
     * @return Localization_Scanner_StringsCollection_Warning[]
     */
    public function getScannerWarnings() : array
    {
        return $this->scanner->getWarnings();
    }

    public function hasAppLocales() : bool
    {
        return !empty($this->appLocales);
    }

    public function isShowWarningsEnabled() : bool
    {
        return $this->request->getBool($this->getVarName('warnings'));
    }

    public function getFilters() : Localization_Editor_Filters
    {
        return $this->filters;
    }

    /**
     * @return Localization_Scanner_StringHash[]
     */
    public function getFilteredStrings() : array
    {
        $strings = $this->activeSource->getSourceScanner($this->scanner)->getHashes();
        
        $result = array();
        
        foreach($strings as $string)
        {
            if($this->filters->isStringMatch($string)) {
                $result[] = $string;
            }
        }

        return $result;
    }
    
    public function getRequestParams() : array
    {
        $params = $this->requestParams;
        $params[$this->getVarName('locale')] = $this->activeAppLocale->getName();
        $params[$this->getVarName('source')] = $this->activeSource->getID();
        $params[$this->getVarName('page')] = $this->getPageNumber();

        return $params;
    }

    public function getAmountPerPage() : int
    {
        return $this->perPage;
    }
    
    public function getPageNumber() : int
    {
        return intval($this->request
            ->registerParam($this->getVarName('page'))
            ->setInteger()
            ->get(0)
        );
    }
    
    public function getActiveLocale() : Localization_Locale
    {
        return $this->activeAppLocale;
    }

    public function getPaginationURL(int $page, array $params=array()) : string
    {
        $params[$this->getVarName('page')] = $page;
        
        return $this->getURL($params);
    }
    
    public function detectVariables(string $string) : array
    {
        $result = array();
        preg_match_all('/%[0-9]+d|%s|%[0-9]+\$s/i', $string, $result, PREG_PATTERN_ORDER);

        if(isset($result[0]) && !empty($result[0])) {
            return $result[0];
        }
        
        return array();
    }
    
    public function display() : void
    {
        echo $this->render();
    }
    
    public function getSourceURL(Localization_Source $source, array $params=array()) : string
    {
        $params[$this->getVarName('source')] = $source->getID();
        
        return $this->getURL($params);
    }
    
    public function getLocaleURL(Localization_Locale $locale, array $params=array()) : string
    {
        $params[$this->getVarName('locale')] = $locale->getName();
        
        return $this->getURL($params);
    }
    
    public function getScanURL() : string
    {
        return $this->getSourceURL($this->activeSource, array($this->getVarName('scan') => 'yes'));
    }
    
    public function getWarningsURL() : string
    {
        return $this->getSourceURL($this->activeSource, array($this->getVarName('warnings') => 'yes'));
    }
    
    public function getURL(array $params=array()) : string
    {
        $persist = $this->getRequestParams();
        
        foreach($persist as $name => $value) {
            if(!isset($params[$name])) {
                $params[$name] = $value;
            }
        }
        
        return '?'.http_build_query($params);
    }

    /**
     * @param string $url
     * @return never-returns
     */
    public function redirect(string $url) : void
    {
        header('Location:'.$url);
        exit;
    }
    
    protected function executeScan() : void
    {
        $this->scanner->scan();

        $this->addMessage(
            t('The source files have been analyzed successfully at %1$s.', date('H:i:s')),
            self::MESSAGE_SUCCESS
        );
        
        $this->redirect($this->getSourceURL($this->activeSource));
    }
    
    protected function executeSave() : void
    {
        $data = $_POST;
        
        $translator = Localization::getTranslator($this->activeAppLocale);
        
        $strings = $data[$this->getVarName('strings')];
        foreach($strings as $hash => $text) 
        {
            $text = trim($text);
            
            if(empty($text)) {
                continue;
            } 
            
            $translator->setTranslation($hash, $text);
        }
        
        $translator->save($this->activeSource, $this->scanner->getCollection());
        
        // refresh all the client files
        Localization::writeClientFiles(true);
        
        $this->addMessage(
            t('The texts have been updated successfully at %1$s.', date('H:i:s')),
            self::MESSAGE_SUCCESS
        );
        
        $this->redirect($this->getURL());
    }
    
    protected function addMessage(string $message, string $type=self::MESSAGE_INFO) : void
    {
        $_SESSION['localization_messages'][] = array(
            'text' => $message,
            'type' => $type
        );
    }

    /**
     * @return array<string,string>
     */
    public function getDefaultOptions() : array
    {
        return array(
            'appname' => '',
            'default-source' => '',
            'back-url' => '',
            'back-label' => ''
        );
    }
    
   /**
    * Sets the application name shown in the main navigation
    * in the user interface.
    * 
    * @param string $name
    * @return Localization_Editor
    */
    public function setAppName(string $name) : Localization_Editor
    {
        $this->setOption('appname', $name);
        return $this;
    }
    
    public function getAppName() : string
    {
        $name = $this->getOption('appname');
        if(!empty($name)) {
            return $name;
        }
        
        return t('Localization editor');
    }

    /**
     * Selects the default source to use if none has been
     * explicitly selected.
     *
     * @param string $sourceID
     * @return Localization_Editor
     */
    public function selectDefaultSource(string $sourceID) : Localization_Editor
    {
        $this->setOption('default-source', $sourceID);
        return $this;
    }
    
   /**
    * Sets an URL that the translators can use to go back to
    * the main application, for example if it is integrated into
    * an existing application.
    * 
    * @param string $url The URL to use for the link
    * @param string $label Label of the link
    * @return Localization_Editor
    */
    public function setBackURL(string $url, string $label) : Localization_Editor
    {
        $this->setOption('back-url', $url);
        $this->setOption('back-label', $label);
        return $this;
    }

    public function getInstallPath() : string
    {
        return $this->installPath;
    }
}
