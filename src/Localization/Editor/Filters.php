<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Editor_Filters
{
   /**
    * @var Localization_Editor
    */
    protected $editor;
    
   /**
    * @var \AppUtils\Request
    */
    
    protected $request; 
     
   /**
    * @var string
    */
    protected $sessionName = 'localize_filters';
    
    protected $vars = array(
        'resetfilter' => null,
        'filter' => null,
        'search' => null,
        'status' => null,
        'location' => null
    );
    
    public function __construct(Localization_Editor $editor)
    {
        $this->editor = $editor;
        $this->request = $editor->getRequest();
        
        foreach($this->vars as $var => $name) {
            $this->vars[$var] = $this->editor->getVarName($var);
        }

        if(!isset($_SESSION[$this->sessionName])) {
            $_SESSION[$this->sessionName] = array();
        }

        if($this->request->getBool($this->vars['resetfilter']))
        {
            $defaults = $this->getDefaultValues();
            
            foreach($defaults as $name => $val) {
                $this->setValue($name, $val);
            }
        }
        else if($this->request->getBool($this->vars['filter'])) 
        {
            $this->parseSearchTerms($this->request->getParam($this->vars['search']));
            
            $this->setValue($this->vars['search'], $this->searchString);
            
            $this->setValue(
                $this->vars['status'], 
                $this->request
                ->registerParam($this->vars['status'])
                ->setEnum('translated', 'untranslated')
                ->get('')
            );
            
            $this->setValue(
                $this->vars['location'],
                $this->request
                ->registerParam($this->vars['location'])
                ->setEnum('client', 'server')
                ->get('server')
            );
        }
        else
        {
            $this->parseSearchTerms($this->getValue($this->vars['search']));
        }
    }
    
    protected function setValue(string $filterName, string $value)
    {
        $_SESSION[$this->sessionName][$filterName] = $value;
    }
    
    protected function getValue(string $filterName) : string
    {
        if(isset($_SESSION[$this->sessionName][$filterName])) {
            return $_SESSION[$this->sessionName][$filterName];
        }
        
        $defaults = $this->getDefaultValues();
        if(isset($defaults[$filterName])) {
            return $defaults[$filterName];
        }
        
        return '';
    }
 
    protected $searchTerms = array();
    protected $searchString = '';
    
    protected function parseSearchTerms(string $searchString)
    {
        if(empty($searchString)) 
        {
            $this->searchTerms = array();
            $this->searchString = '';
            return;
        }
        
        $search = strip_tags($searchString);
        $search = htmlspecialchars($search);
        
        $terms = explode(' ', $search);
        $terms = array_map('trim', $terms);
        
        $keep = array();
        foreach($terms as $term) {
            if(!empty($term)) {
                $keep[] = $term;
            }
        }
        
        $this->searchTerms = $keep;
        $this->searchString = implode(' ', $keep);
    }
    
    public function isStringMatch(Localization_Scanner_StringHash $string)
    {
        if(!empty($this->searchTerms)) 
        {
            $haystack = $string->getSearchString();
            
            foreach($this->searchTerms as $term) {
                if(!mb_stristr($haystack, $term)) {
                    return false;
                }
            }
        }
        
        $status = $this->getValue($this->vars['status']);
        if($status === 'untranslated' && $string->isTranslated()) {
            return false;
        } else if($status === 'translated' && !$string->isTranslated()) {
            return false;
        }
        
        $location = $this->getValue($this->vars['location']);
        if($location === 'client' && !$string->hasLanguageType('Javascript')) {
            return false;
        } else if($location === 'server' && !$string->hasLanguageType('PHP')) {
            return false;
        }
        
        return true;
    }
    
    protected function getDefaultValues()
    {
        return array(
            $this->vars['search'] => '',
            $this->vars['status'] => 'untranslated',
            $this->vars['location'] => ''
        );
    }
    
    public function renderForm()
    {
        ob_start();
        
        ?>
            <form class="form-inline">
            	<div class="form-hiddens">
            		<?php 
    					$params = $this->editor->getRequestParams();
    					foreach($params as $name => $value) {
    					    ?>
    					    	<input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>">
    					    <?php 
    					}
					?>
            	</div>
		        <input name="<?php echo $this->vars['search'] ?>" type="text" class="form-control mb-2 mr-sm-2" placeholder="<?php pt('Search...') ?>" value="<?php echo $this->searchString ?>">
                <div class="input-group mb-2 mr-sm-2">
                	<?php
                	    echo $this->renderSelect(
                	       $this->vars['status'],
                	       array(
                    	       array(
                    	            'value' => '',
                    	            'label' => t('Status...')
                    	       ),
                    	       array(
                    	           'value' => 'untranslated',
                    	           'label' => t('Not translated')
                    	       ),
                    	       array(
                    	           'value' => 'translated',
                    	           'label' => t('Translated')
                    	       )
                	       )
                	   );
                	
                	   echo $this->renderSelect(
                	       $this->vars['location'],
                	       array(
                	           array(
                	               'value' => '',
                	               'label' => t('Location...')
                	           ),
                	           array(
                	               'value' => 'client',
                	               'label' => t('Clientside')
                	           ),
                	           array(
                	               'value' => 'server',
                	               'label' => t('Serverside')
                	           )
                	       )
            	       );
                	?>
                </div>
				<button type="submit" name="<?php echo $this->vars['filter'] ?>" value="yes" class="btn btn-primary mb-2">
					<i class="fa fa-filter"></i>
					<?php pt('Filter') ?>
				</button> 
				&#160;
				<button type="submit" name="<?php echo $this->vars['resetfilter']?>" value="yes" class="btn btn-secondary mb-2" title="<?php pt('Reset the filters') ?>">
					<i class="fa fa-times"></i>
				</button>
            </form>
			<br>
        <?php
        
        return ob_get_clean();
    }
    
    protected function renderSelect(string $filterName, $entries)
    {
        $value = $this->getValue($filterName);
        
        ob_start();
        
        ?>
        	<select class="form-control" name="<?php echo $filterName ?>">
        		<?php 
                    foreach($entries as $entry) 
                    {
                        $selected = '';
                        if($entry['value'] === $value) {
                            $selected = ' selected';
                        }
                        
                        ?>
                        	<option value="<?php echo $entry['value'] ?>"<?php echo $selected ?>>
                        		<?php echo $entry['label'] ?>
                    		</option>
                        <?php 
                    }
                ?>
            </select>
        <?php
        
        return ob_get_clean();
    }
}