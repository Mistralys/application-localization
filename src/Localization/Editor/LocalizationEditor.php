<?php
/**
 * @package Localization
 * @subpackage Editor
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Editor;

use AppLocalize\Localization;
use AppLocalize\Localization\Editor\Template\PageScaffold;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Scanner\LocalizationScanner;
use AppLocalize\Localization\Scanner\StringHash;
use AppLocalize\Localization\Scanner\CollectionWarning;
use AppLocalize\Localization\Source\BaseLocalizationSource;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OutputBuffering_Exception;
use AppUtils\Traits\OptionableTrait;
use AppUtils\Request;
use function AppLocalize\t;

/**
 * User Interface handler for editing localization files.
 *
 * @package Localization
 * @subpackage Editor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LocalizationEditor implements OptionableInterface
{
    use OptionableTrait;
    
    public const MESSAGE_INFO = 'info';
    public const MESSAGE_ERROR = 'danger';
    public const MESSAGE_WARNING = 'warning';
    public const MESSAGE_SUCCESS = 'success';
    
    public const ERROR_NO_SOURCES_AVAILABLE = 40001;
    public const ERROR_LOCAL_PATH_NOT_FOUND = 40002;
    public const ERROR_STRING_HASH_WITHOUT_TEXT = 40003;
    public const VARIABLE_STRINGS = 'strings';
    public const VARIABLE_SAVE = 'save';
    public const VARIABLE_SCAN = 'scan';
    public const VARIABLE_WARNINGS = 'warnings';

    protected string $installPath;
    protected Request $request;
    protected BaseLocalizationSource $activeSource;
    protected LocalizationScanner $scanner;
    protected LocaleInterface $activeAppLocale;
    protected EditorFilters $filters;
    protected string $varPrefix = 'applocalize_';
    protected int $perPage = 20;

    /**
     * @var BaseLocalizationSource[]
     */
    protected array $sources;

    /**
    * @var LocaleInterface[]
    */
    protected array $appLocales = array();

   /**
    * @var array<string,string|int>
    */
    protected array $requestParams = array();

    /**
     * @throws LocalizationException
     * @see \AppLocalize\LocalizationEditor::ERROR_LOCAL_PATH_NOT_FOUND
     */
    public function __construct()
    {
        $path = __DIR__.'/../../';
        if(!is_dir($path.'/js'))
        {
            throw new LocalizationException(
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
    * @return LocalizationEditor
    */
    public function addRequestParam(string $name, string $value) : LocalizationEditor
    {
        $this->requestParams[$name] = $value;
        return $this;
    }
    
    public function getActiveSource() : BaseLocalizationSource
    {
        return $this->activeSource;
    }
    
    protected function initSession() : void
    {
        if(session_status() !== PHP_SESSION_ACTIVE) {
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
     * @throws LocalizationException
     */
    protected function initSources() : void
    {
        $this->sources = Localization::getSources();
        
        if(empty($this->sources)) 
        {
            throw new LocalizationException(
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
     * @return LocaleInterface[]
     */
    public function getAppLocales() : array
    {
        return $this->appLocales;
    }

    /**
     * @return BaseLocalizationSource[]
     */
    public function getSources() : array
    {
        return $this->sources;
    }

    public function getBackURL() : string
    {
        return (string)$this->getOption('back-url');
    }

    public function getBackButtonLabel() : string
    {
        return (string)$this->getOption('back-label');
    }

    public function getSaveVariableName() : string
    {
        return $this->getVarName(self::VARIABLE_SAVE);
    }

    public function getStringsVariableName() : string
    {
        return $this->getVarName(self::VARIABLE_STRINGS);
    }

    protected function handleActions() : void
    {
        $this->initSources();
        
        $this->filters = new EditorFilters($this);
        
        if($this->request->getBool($this->getVarName(self::VARIABLE_SCAN)))
        {
            $this->executeScan();
        } 
        else if($this->request->getBool($this->getSaveVariableName()))
        {
            $this->executeSave();
        }
    }
    
    public function getScanner() : LocalizationScanner
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
        
        return (new PageScaffold($this))->render();
    }

    /**
     * @return CollectionWarning[]
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
        return $this->request->getBool($this->getVarName(self::VARIABLE_WARNINGS));
    }

    public function getFilters() : EditorFilters
    {
        return $this->filters;
    }

    /**
     * @return StringHash[]
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

    /**
     * @return array<string,string|int>
     */
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
        return (int)$this->request
            ->registerParam($this->getVarName('page'))
            ->setInteger()
            ->get(0);
    }
    
    public function getActiveLocale() : LocaleInterface
    {
        return $this->activeAppLocale;
    }

    /**
     * @param int $page
     * @param array<string, string|int> $params
     * @return string
     */
    public function getPaginationURL(int $page, array $params=array()) : string
    {
        $params[$this->getVarName('page')] = $page;
        
        return $this->getURL($params);
    }

    /**
     * @param string $string
     * @return string[]
     */
    public function detectVariables(string $string) : array
    {
        $result = array();
        preg_match_all('/%[0-9]+d|%s|%[0-9]+\$s/i', $string, $result, PREG_PATTERN_ORDER);

        if(!empty($result[0])) {
            return $result[0];
        }
        
        return array();
    }
    
    public function display() : void
    {
        echo $this->render();
    }

    /**
     * @param BaseLocalizationSource $source
     * @param array<string, string|int> $params
     * @return string
     */
    public function getSourceURL(BaseLocalizationSource $source, array $params=array()) : string
    {
        $params[$this->getVarName('source')] = $source->getID();
        
        return $this->getURL($params);
    }

    /**
     * @param LocaleInterface $locale
     * @param array<string, string|int> $params
     * @return string
     */
    public function getLocaleURL(LocaleInterface $locale, array $params=array()) : string
    {
        $params[$this->getVarName('locale')] = $locale->getName();
        
        return $this->getURL($params);
    }
    
    public function getScanURL() : string
    {
        return $this->getSourceURL($this->activeSource, array($this->getVarName(self::VARIABLE_SCAN) => 'yes'));
    }
    
    public function getWarningsURL() : string
    {
        return $this->getSourceURL($this->activeSource, array($this->getVarName(self::VARIABLE_WARNINGS) => 'yes'));
    }

    /**
     * @param array<string, string|int> $params
     * @return string
     */
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
        
        $strings = $data[$this->getVarName(self::VARIABLE_STRINGS)];
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
    * @return LocalizationEditor
    */
    public function setAppName(string $name) : LocalizationEditor
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
     * @return LocalizationEditor
     */
    public function selectDefaultSource(string $sourceID) : LocalizationEditor
    {
        $this->setOption('default-source', $sourceID);
        return $this;
    }
    
   /**
    * Sets a URL that the translators can use to go back to
    * the main application, for example, if it is integrated into
    * an existing application.
    * 
    * @param string $url The URL to use for the link
    * @param string $label Label of the link
    * @return LocalizationEditor
    */
    public function setBackURL(string $url, string $label) : LocalizationEditor
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
