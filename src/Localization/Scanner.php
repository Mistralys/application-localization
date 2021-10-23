<?php
/**
 * File containing the {@see \AppLocalize\Localization_Scanner} class.
 *
 * @package AppLocalize
 * @subpackage Scanner
 * @see \AppLocalize\Localization_Scanner
 */

declare(strict_types=1);

namespace AppLocalize;

use AppUtils\FileHelper;

/**
 * The scanner is used to go through all PHP and JavaScript file
 * sources that have been configured, and detects all translatable
 * strings.
 *
 * @package AppLocalize
 * @subpackage Scanner
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Localization_Scanner
{
   /**
    * @var float
    */
    protected $timeStart;
    
   /**
    * @var float
    */
    protected $timeEnd;
    
   /**
    * @var array
    */
    protected $stringInfos = array();
    
   /**
    * @var string
    */
    protected $storageFile;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var Localization_Scanner_StringsCollection|NULL
     * @see Localization_Scanner::getCollection()
     */
    protected $collection;

    /**
     * @var Localization_Parser
     */
    protected $parser;

    public function __construct(string $storageFile)
    {
        $this->storageFile = $storageFile;
    }
    
    public function isScanAvailable() : bool
    {
        return file_exists($this->storageFile);
    }
    
    public function scan() : void
    {
        if(isset($this->collection)) {
            $this->collection = null;
        }
        
        $this->timeStart = microtime(true);
        
        $sources = Localization::getSources();
        foreach($sources as $source) 
        {
            $source->scan($this);
        }
        
        $this->timeEnd = microtime(true);
        
        $this->save();
    }

    public function load() : void
    {
        if(!$this->isScanAvailable()) {
            return;
        }
        
        if($this->loaded) {
            return;
        }
        
        $this->loaded = true;
        
        $data = FileHelper::parseJSONFile($this->storageFile);
        
        if($this->getCollection()->fromArray($data) === true) {
            return;
        }
        
        FileHelper::deleteFile($this->storageFile);
        $this->loaded = false;
        $this->collection = null;
    }
    
    protected function save() : void
    {
        $data = $this->getCollection()->toArray();
        
        FileHelper::saveAsJSON($data, $this->storageFile);
    }
    
    public function getParser() : Localization_Parser
    {
        if(!isset($this->parser)) {
            $this->parser = new Localization_Parser($this);
        }
        
        return $this->parser;
    }
    
    public function getCollection() : Localization_Scanner_StringsCollection
    {
        if(!isset($this->collection)) {
            $this->collection = new Localization_Scanner_StringsCollection($this);
        }
        
        return $this->collection;
    }

    /**
     * Returns the total execution time it took to parse
     * all relevant files and folders.
     *
     * @return float
     */
    public function getExecutionTime() : float
    {
        return $this->timeEnd - $this->timeStart;
    }
    
    public function countHashes() : int
    {
        $this->load();
        
        return $this->getCollection()->countHashes();
    }
    
    public function countFiles() : int
    {
        $this->load();
        
        return $this->getCollection()->countFiles();
    }
    
    public function hasWarnings() : bool
    {
        return $this->getCollection()->hasWarnings();
    }
    
    public function countWarnings() : int
    {
        return $this->getCollection()->countWarnings();
    }
    
   /**
    * Retrieves all warnings that have been registered
    * during the last search for translatable texts.
    * 
    * @return Localization_Scanner_StringsCollection_Warning[]
    */
    public function getWarnings() : array
    {
        return $this->getCollection()->getWarnings();
    }
}
