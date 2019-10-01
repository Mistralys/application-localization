<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Editor
{
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
    
    public function __construct()
    {
        $this->installPath = realpath(__DIR__.'/../');
        $this->sources = Localization::getSources();
        $this->request = new \AppUtils\Request();
        $this->scanner = Localization::createScanner();
        
        $activeID = $this->request->registerParam('source')->setEnum(Localization::getSourceIDs())->get();
        if(empty($activeID)) {
            $activeID = $this->sources[0]->getID();
        }
        
        $this->activeSource = Localization::getSourceByID($activeID);
        
        if($this->request->getBool('scan')) {
            $this->executeScan();
        }
    }
    
    public function render()
    {
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
            <a class="navbar-brand" href="#"><?php pt('Localization editor') ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
			
            <div class="collapse navbar-collapse" id="navbarsExampleDefault">
                <ul class="navbar-nav mr-auto">
        			<!--  <li class="nav-item">
        				<a class="nav-link" href="#">List</a>
        			</li>-->
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
                        				</a>
                        			<?php 
                        	    }
                    	    ?>
                        </div>
                    </li>
                </ul>
    		</div>
		</nav>
		<main role="main" class="container">
			<div>
				<h1><?php echo $this->activeSource->getLabel() ?></h1>
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
                                	<?php pt('Scan now') ?>
                                </a>
                            </p>
    				    <?php 
    				}
    				else
    				{
        				?>
            				<table class="table table-hover">
            					<thead>
            						<tr>
            							<th><?php pt('Text') ?></th>
            							<th class="align-center"><?php pt('Translated?') ?></th>
            							<th class="align-center"><?php pt('Places used') ?></th>
            							<th><?php pt('Sources') ?></th>
            						</tr>
            					</thead>
            					<tbody>
            						<?php 
            						    $strings = $this->activeSource->getHashes($this->scanner);
            						    
            						    foreach($strings as $string)
            						    {
            						        ?>
            						        	<tr>
            						        		<td><?php echo $string->getText() ?></td>
            						        		<td class="align-center"><?php echo $this->renderStatus($string) ?></td>
            						        		<td class="align-center"><?php echo $string->countStrings() ?></td>
            						        		<td><?php echo implode(', ', $string->getFiles()) ?></td>
            						        	</tr>
            						        <?php 
            						    }
            						?>
            					</tbody>
            				</table>
            				<br>
            				<p>
                                <a href="<?php echo $this->getScanURL() ?>" class="btn btn-primary">
                                	<?php pt('Scan for texts') ?>
                                </a>
            				</p>
        				<?php 
    				}
				?>
			</div>
		</main>
	</body>
</html>
<?php

        return ob_get_clean();
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
    
    protected function getSourceURL(Localization_Source $source, array $params=array())
    {
        $params['source'] = $source->getID();
        
        return '?'.http_build_query($params);
    }
    
    protected function getScanURL()
    {
        return $this->getSourceURL($this->activeSource, array('scan' => 'yes'));
    }
    
    protected function executeScan()
    {
        $this->scanner->scan();
        
        header('Location:'.$this->getSourceURL($this->activeSource));
    }
    
    protected function renderStatus(Localization_Scanner_StringHash $hash)
    {
        if($hash->isTranslated()) {
            return '<i class="fa fa-check text-success"></i>';
        }        
        
        return '<i class="fa fa-ban text-danger"></i>';
    }
}