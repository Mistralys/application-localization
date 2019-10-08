<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Editor
{
    const MESSAGE_INFO = 'info';
    
    const MESSAGE_ERROR = 'danger';
    
    const MESSAGE_WARNING = 'warning';
    
    const MESSAGE_SUCCESS = 'success';
    
   /**
    * @var string
    */
    protected $installPath;
    
   /**
    * @var Localization_Source[]
    */
    protected $sources;
    
   /**
    * @var \AppUtils\Request
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

    protected $requestParams = array();
    
    protected $varPrefix = 'applocalize_';
    
    public function __construct()
    {
        $this->installPath = realpath(__DIR__.'/../');
        $this->request = new \AppUtils\Request();

        $this->initSession();
        $this->initAppLocales();
        $this->initSources();

        $this->scanner = Localization::createScanner();
        $this->scanner->load();
        
        $this->filters = new Localization_Editor_Filters($this);
    }
    
    public function getRequest()
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
    
    public function getActiveLocale() : Localization_Locale
    {
        return $this->activeAppLocale;
    }
    
    public function getActiveSource() : Localization_Source 
    {
        return $this->activeSource;
    }
    
    protected function initSession()
    {
        if(session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if(!isset($_SESSION['localization_messages'])) {
            $_SESSION['localization_messages'] = array();
        }
    }
    
    public function getVarName($name)
    {
        return $this->varPrefix.$name;
    }
    
    protected function initSources()
    {
        $this->sources = Localization::getSources();
        
        $activeID = $this->request->registerParam($this->getVarName('source'))->setEnum(Localization::getSourceIDs())->get();
        if(empty($activeID)) {
            $activeID = $this->sources[0]->getID();
        }
        
        $this->activeSource = Localization::getSourceByID($activeID);
    }
    
    protected function initAppLocales()
    {
        $names = array();
        
        $locales = Localization::getAppLocales();
        foreach($locales as $locale) {
            if(!$locale->isNative()) {
                $this->appLocales[] = $locale;
                $names[] = $locale->getName();
            }
        }
       
        $activeID = $this->request->registerParam($this->getVarName('locale'))->setEnum($names)->get();
        if(empty($activeID)) {
            $activeID = $this->appLocales[0]->getName();
        }
        
        $this->activeAppLocale = Localization::getAppLocaleByName($activeID);
        
        Localization::selectAppLocale($activeID);
    }
    
    protected function handleActions()
    {
        if($this->request->getBool($this->getVarName('scan'))) 
        {
            $this->executeScan();
        } 
        else if($this->request->getBool($this->getVarName('save'))) 
        {
            $this->executeSave();
        }
    }
    
    public function render()
    {
        $this->handleActions();
        
        ob_start();
        
?><!doctype html>
<html lang="en">
	<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <title><?php pt('Localization editor') ?></title>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/54212b9b2b.js" crossorigin="anonymous"></script>
        <script><?php echo $this->getJavascript() ?></script>
        <style><?php echo $this->getCSS() ?></style>
	</head>
	<body>
        <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
            <a class="navbar-brand" href="<?php echo $this->getURL() ?>"><?php pt('Localization editor') ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
			
            <div class="collapse navbar-collapse" id="navbarsExampleDefault">
                <ul class="navbar-nav mr-auto">
            		<li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        	<?php pt('Text sources') ?>
                    	</a>
                        <div class="dropdown-menu" aria-labelledby="dropdown01">
                        	<?php 
                        	    foreach($this->sources as $source)
                        	    {
                        	       ?>
                            			<a class="dropdown-item" href="<?php echo $this->getSourceURL($source) ?>">
                            				<?php echo $source->getLabel() ?>
                            				<?php
                            				    $untranslated = $source->countUntranslated($this->scanner);
                            				    if($untranslated > 0) {
                            				        ?>
                            				        	(<span class="text-danger" title="<?php pt('%1$s texts have not been translated in this text source.', $untranslated) ?>"><?php echo $untranslated ?></span>)
                    				            	<?php 
                            				    }
                        				    ?>
                        				</a>
                        			<?php 
                        	    }
                    	    ?>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        	<?php echo $this->activeAppLocale->getLabel() ?>
                    	</a>
                        <div class="dropdown-menu" aria-labelledby="dropdown01">
                        	<?php 
                        	    foreach($this->appLocales as $locale)
                        	    {
                        	       ?>
                            			<a class="dropdown-item" href="<?php echo $this->getLocaleURL($locale) ?>">
                            				<?php echo $locale->getLabel() ?>
                        				</a>
                        			<?php 
                        	    }
                    	    ?>
                        </div>
                    </li>
                    <li class="nav-item">
        				<a href="<?php echo $this->getScanURL() ?>" class="btn btn-light btn-sm" title="<?php pt('Scan all source files to find translateable texts.') ?>">
                        	<i class="fa fa-refresh"></i>
                        	<?php pt('Scan') ?>
                        </a>
        			</li>
                </ul>
    		</div>
		</nav>
		<main role="main" class="container">
			<div>
				<h1><?php echo $this->activeSource->getLabel() ?></h1>
				<?php 
    				if(!empty($_SESSION['localization_messages'])) 
    				{
    				    foreach($_SESSION['localization_messages'] as $def)
    				    {
    				        ?>
    				        	<div class="alert alert-<?php echo $def['type'] ?>" role="alert">
                            		<?php echo $def['text'] ?>
                            		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    									<span aria-hidden="true">&times;</span>
									</button>
                            	</div>
				        	<?php 
    				    }
    				    
    				    // reset the messages after having displayed them
    				    $_SESSION['localization_messages'] = array();
    				}
				?>
				<p>
					<?php 
				        pt(
    					    'You are translating to %1$s', 
    					    '<span class="badge badge-info">'.
    					       $this->activeAppLocale->getLabel().
    				        '</span>'
                        );
				    ?><br>
					<?php pt('Found %1$s texts to translate.', $this->activeSource->countUntranslated($this->scanner)) ?>
				</p>
				<br>
				<?php 
    				if(!$this->scanner->isScanAvailable()) 
    				{
    				    ?>
    				    	<div class="alert alert-primary" role="alert">
                            	<b><?php pt('No texts found:') ?></b> 
                            	<?php pt('The source folders have not been scanned yet.') ?>
                            </div>
                            <p>
                                <a href="<?php echo $this->getScanURL() ?>" class="btn btn-primary">
                                	<i class="fa fa-refresh"></i>
                                	<?php pt('Scan files now') ?>
                                </a>
                            </p>
    				    <?php 
    				}
    				else
    				{
    				    echo $this->filters->renderForm();
    				    echo $this->renderList();
    				}
				?>
			</div>
		</main>
	</body>
</html>
<?php

        return ob_get_clean();
    }

    protected function getFilteredStrings()
    {
        $strings = $this->activeSource->getHashes($this->scanner);
        
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

        return $params;
    }
    
    protected $perPage = 20;
    
    protected function renderList()
    {
        $strings = $this->getFilteredStrings();
        
        if(empty($strings))
        {
            ?>
            	<div class="alert alert-info">
            		<?php pt('No matching strings found.') ?>
            	</div>
            <?php 
            
            return;
        }
        
        $total = count($strings);
        $page = intval($this->request->registerParam($this->getVarName('page'))->setInteger()->get(0));
        $pager = new \AppUtils\PaginationHelper($total, $this->perPage, $page);
        
        $keep = array_slice($strings, $pager->getOffsetStart(), $this->perPage);
        
        ?>
			<form method="post">
				<div class="form-hiddens">
					<?php 
    					$params = $this->getRequestParams();
    					foreach($params as $name => $value) {
    					    ?>
    					    	<input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>">
    					    <?php 
    					}
					?>
				</div>
            	<table class="table table-hover">
    				<thead>
    					<tr>
    						<th><?php pt('Text') ?></th>
    						<th class="align-center"><?php pt('Translated?') ?></th>
    						<th class="align-center"><?php pt('Places used') ?></th>
    						<th><?php pt('Location') ?></th>
    						<th><?php pt('Sources') ?></th>
    					</tr>
    				</thead>
    				<tbody>
    					<?php 
    					    foreach($keep as $string)
    					    {
    					        $this->renderListEntry($string);
    					    }
    					?>
    				</tbody>
    			</table>
    			<?php 
        			if($pager->hasPages()) 
        			{
        			    $prevUrl = $this->getPaginationURL($pager->getPreviousPage());
        			    $nextUrl = $this->getPaginationURL($pager->getNextPage());
        			    
        			    ?>
        			    	<nav aria-label="<?php pt('Navigate available pages of texts.') ?>">
                                <ul class="pagination">
                                    <li class="page-item">
                                    	<a class="page-link" href="<?php echo $prevUrl ?>">
                                    		<i class="fa fa-arrow-left"></i>
                                		</a>
                            		</li>
                            		<?php 
                            		    $numbers = $pager->getPageNumbers();
                            		    foreach($numbers as $number) 
                            		    {
                            		        $url = $this->getPaginationURL($number);
                            		        
                            		        ?>
                            		        	<li class="page-item <?php if($pager->isCurrentPage($number)) { echo 'active'; } ?>">
                            		        		<a class="page-link" href="<?php echo $url ?>">
                            		        			<?php echo $number ?>
                        		        			</a>
                        		        		</li>
                            		        <?php 
                            		    }
                            		?>
                                    <li class="page-item">
                                    	<a class="page-link" href="<?php echo $nextUrl ?>">
                                    		<i class="fa fa-arrow-right"></i>
                                		</a>
                                	</li>
                                </ul>
                            </nav>
        			    <?php 
        			}
    			?>
				<br>
				<p>
					<button type="submit" name="<?php echo $this->getVarName('save') ?>" value="yes" class="btn btn-primary">
						<i class="fas fa-save"></i>
						<?php pt('Save now') ?>
					</button>
				</p>
			</form>
			
        <?php 
    }
    
    protected function getPaginationURL(int $page, $params=array())
    {
        $params[$this->getVarName('page')] = $page;
        
        return $this->getURL($params);
    }
    
    protected function renderListEntry(Localization_Scanner_StringHash $string)
    {
        $hash = $string->getHash();
        
        $shortText =  \AppUtils\ConvertHelper::text_cut(htmlspecialchars($string->getTranslatedText()), 50);
        
        ?>
        	<tr class="string-entry inactive" onclick="Editor.Toggle('<?php echo $hash ?>')" data-hash="<?php echo $hash ?>">
        		<td class="string-text"><?php echo htmlspecialchars($shortText) ?></td>
        		<td class="align-center string-status"><?php echo $this->renderStatus($string) ?></td>
        		<td class="align-center"><?php echo $string->countStrings() ?></td>
        		<td class="align-center"><?php echo $this->renderTypes($string) ?></td>
        		<td><?php echo implode(', ', $string->getFiles()) ?></td>
        	</tr>
        	<tr class="string-form">
        		<td colspan="5">
        			<?php echo pt('Native text:') ?>
        			<p class="native-text"><?php echo htmlspecialchars($string->getText()) ?></p>
        			<p>
        				<textarea rows="4" class="form-control" name="<?php echo $this->getVarName('strings') ?>[<?php echo $hash ?>]"><?php echo $string->getTranslatedText() ?></textarea>

        			</p>
        			<p>
	        			<button type="button" class="btn btn-outline-primary btn-sm" onclick="Editor.Confirm('<?php echo $hash ?>')">
	        				<?php pt('OK') ?>
	        			</button>
	        			<button type="button" class="btn btn-outline-secondary btn-sm" onclick="Editor.Toggle('<?php echo $hash ?>')">
	        				<?php pt('Cancel') ?>
	        			</button>
        			</p>
        		</td>
        	</tr>
        <?php 
        
    }
    
    public function display()
    {
        echo $this->render();
    }
    
    protected function getJavascript() : string
    {
        return file_get_contents($this->installPath.'/js/editor.js');
    }
    
    protected function getCSS() : string
    {
        return file_get_contents($this->installPath.'/css/editor.css');
    }
    
    public function getSourceURL(Localization_Source $source, array $params=array())
    {
        $params[$this->getVarName('source')] = $source->getID();
        
        return $this->getURL($params);
    }
    
    public function getLocaleURL(Localization_Locale $locale, array $params=array())
    {
        $params[$this->getVarName('locale')] = $locale->getName();
        
        return $this->getURL($params);
    }
    
    public function getScanURL()
    {
        return $this->getSourceURL($this->activeSource, array($this->getVarName('scan') => 'yes'));
    }
    
    public function getURL(array $params=array())
    {
        $persist = $this->getRequestParams();
        
        foreach($persist as $name => $value) {
            if(!isset($params[$name])) {
                $params[$name] = $value;
            }
        }
        
        return '?'.http_build_query($params);
    }
    
    public function redirect($url)
    {
        header('Location:'.$url);
        exit;
    }
    
    protected function executeScan()
    {
        $this->scanner->scan();

        $this->addMessage(
            t('The source files haved been analyzed successfully at %1$s.', date('H:i:s')),
            self::MESSAGE_SUCCESS
        );
        
        $this->redirect($this->getSourceURL($this->activeSource));
    }
    
    protected function executeSave()
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
            t('The texts haved been updated successfully at %1$s.', date('H:i:s')),
            self::MESSAGE_SUCCESS
        );
        
        $this->redirect($this->getURL());
    }
    
    protected function renderStatus(Localization_Scanner_StringHash $hash)
    {
        if($hash->isTranslated()) {
            return '<i class="fa fa-check text-success"></i>';
        }        
        
        return '<i class="fa fa-ban text-danger"></i>';
    }
    
    protected function renderTypes(Localization_Scanner_StringHash $hash)
    {
        $types = array();
        
        if($hash->hasLanguageType('PHP')) {
            $types[] = t('Server');
        }
        
        if($hash->hasLanguageType('Javascript')) {
            $types[] = t('Client');
        }
        
        return implode(', ', $types);
    }
    
    protected function addMessage($message, $type=self::MESSAGE_INFO)
    {
        $_SESSION['localization_messages'][] = array(
            'text' => $message,
            'type' => $type
        );
    }
}