<?php
/**
 * @package Localization
 * @subpackage Editor
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Editor;

use AppLocalize\Localization\Editor\LocalizationEditor;
use AppLocalize\Localization\Scanner\StringHash;
use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use AppUtils\Request;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;

/**
 * Handles the list filters in the editor UI.
 *
 * @package Localization
 * @subpackage Editor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class EditorFilters
{
    protected LocalizationEditor $editor;
    protected Request $request;
    protected string $sessionName = 'localize_filters';
    
   /**
    * @var string[]
    */
    protected array $vars = array(
        'resetfilter' => '',
        'filter' => '',
        'search' => '',
        'status' => '',
        'location' => ''
    );
    
    public function __construct(LocalizationEditor $editor)
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
                ->setEnum('', 'translated', 'untranslated')
                ->get('')
            );
            
            $this->setValue(
                $this->vars['location'],
                $this->request
                ->registerParam($this->vars['location'])
                ->setEnum('', 'client', 'server')
                ->get('')
            );
        }
        else
        {
            $this->parseSearchTerms($this->getValue($this->vars['search']));
        }
    }
    
    protected function setValue(string $filterName, string $value) : void
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

    /**
     * @var string[]
     */
    protected array $searchTerms = array();

    protected string $searchString = '';
    
    protected function parseSearchTerms(string $searchString) : void
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
    
    public function isStringMatch(StringHash $string) : bool
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

    /**
     * @return array<string,string>
     */
    protected function getDefaultValues() : array
    {
        return array(
            $this->vars['search'] => '',
            $this->vars['status'] => 'untranslated',
            $this->vars['location'] => ''
        );
    }
    
    public function renderForm() : string
    {
        OutputBuffering::start();
        
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
            	<div class="form-row">
            		<div class="col-auto">
    		        	<input name="<?php echo $this->vars['search'] ?>" type="text" class="form-control mb-2 mr-sm-2" placeholder="<?php pt('Search...') ?>" value="<?php echo $this->searchString ?>">
    		        </div>
                    <div class="col-auto">
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
                    <div class="col-auto">
	    				<button type="submit" name="<?php echo $this->vars['filter'] ?>" value="yes" class="btn btn-primary mb-2" title="<?php pt('Filter the list with the selected criteria.') ?>" data-toggle="tooltip">
        					<i class="fa fa-filter"></i>
        					<?php pt('Filter') ?>
        				</button> 
        				<button type="submit" name="<?php echo $this->vars['resetfilter']?>" value="yes" class="btn btn-secondary mb-2" title="<?php pt('Reset the filters to their defaults.') ?>" data-toggle="tooltip">
        					<i class="fa fa-times"></i>
        				</button>
    				</div>
				</div>
            </form>
            <p>
            	<small class="text-muted"><?php 
            	   pts('Hint:'); 
            	   pt('Search works in translated and untranslated text, as well as the file name.') 
        	   ?></small>
            </p>
			<br>
        <?php
        
        return OutputBuffering::get();
    }

    /**
     * @param string $filterName
     * @param array<int,array<string,string>> $entries
     * @return string
     * @throws OutputBuffering_Exception
     */
    protected function renderSelect(string $filterName, array $entries) : string
    {
        $value = $this->getValue($filterName);
        
        OutputBuffering::start();
        
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
        
        return OutputBuffering::get();
    }
}
