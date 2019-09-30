<?php

namespace AppLocalize;

class Localization_Scanner
{
    protected $timeStart;
    
    protected $timeEnd;
    
    protected $stringInfos = array();
    
    protected $storageFile;
    
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
        
        $this->getCollection()->fromArray($data);
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
    
   /**
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;
    
    public function getCollection()
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
}