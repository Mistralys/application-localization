<?php

namespace AppLocalize;

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
     * @var Localization_Scanner_StringsCollection|NULL
     * @see Localization_Scanner::getCollection()
     */
    protected $collection;
    
    public function __construct(string $storageFile)
    {
        $this->storageFile = $storageFile;
    }
    
    public function isScanAvailable()
    {
        return file_exists($this->storageFile);
    }
    
    public function scan()
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
    
    protected $loaded = false;
    
    public function load()
    {
        if(!$this->isScanAvailable()) {
            return;
        }
        
        if($this->loaded) {
            return;
        }
        
        $this->loaded = true;
        
        $data = \AppUtils\FileHelper::parseJSONFile($this->storageFile);
        
        if($this->getCollection()->fromArray($data) === true) {
            return;
        }
        
        \AppUtils\FileHelper::deleteFile($this->storageFile);
        $this->loaded = false;
        $this->collection = null;
    }
    
    protected function save()
    {
        $data = $this->collection->toArray();
        
        \AppUtils\FileHelper::saveAsJSON($data, $this->storageFile);
    }
    
   /**
    * @var Localization_Parser
    */
    protected $parser;

    public function getParser()
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
    public function getExecutionTime()
    {
        return $this->timeEnd - $this->timeStart;
    }
    
    public function countHashes()
    {
        $this->load();
        
        return $this->getCollection()->countHashes();
    }
    
    public function countFiles()
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
    * during the last search for translateable texts.
    * 
    * @return \AppLocalize\Localization_Scanner_StringsCollection_Warning[]
    */
    public function getWarnings()
    {
        return $this->getCollection()->getWarnings();
    }
}