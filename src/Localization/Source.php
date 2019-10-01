<?php

declare(strict_types=1);

namespace AppLocalize;

abstract class Localization_Source
{
   /**
    * Human readable label for the source.
    * @var string
    */
    protected $label;
    
   /**
    * Human readable group name to categorize the source.
    * @var string
    */
    protected $group;
    
   /**
    * The folder in which the localization files are stored
    * @var string
    */
    protected $storageFolder;
    
    public function __construct(string $alias, string $label, string $group, string $storageFolder)
    {
        $this->alias = $alias;
        $this->label = $label;
        $this->group = $group;
        $this->storageFolder = $storageFolder;
    }
    
    abstract public function getID() : string;
    
    public function getAlias() : string
    {
        return $this->alias;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function getGroup() : string
    {
        return $this->group;
    }
    
    public function getStorageFolder() : string
    {
        return $this->storageFolder;
    }
    
   /**
    * Available during scanning.
    * @var Localization_Scanner_StringsCollection
    */
    protected $collection;
    
   /**
    * Available during scanning.
    * @var Localization_Parser
    */
    protected $parser;
    
    public function scan(Localization_Scanner $scanner)
    {
        $this->collection = $scanner->getCollection();
        $this->parser = $scanner->getParser();
        
        $this->_scan();
        
        $this->collection = null;
    }

    protected $extensions = array(
        'js' => 'Javascript',
        'php' => 'PHP'
    );
    
    /**
     * Parses the code of the target file to find all
     * supported function calls and extract the native
     * application language string from the code. Adds any
     * strings it finds to the results collection.
     *
     * @param string $file
     */
    protected function parseFile($file)
    {
        $this->log(sprintf('Parsing file [' . $file . '].'));
        
        $language = $this->parser->parseFile($file);
        
        $relative = $this->relativizePath($file);
        
        $texts = $language->getTexts();
        foreach($texts as $def) 
        {
            $this->collection->addFromFile(
                $this->getID(),
                $relative,
                $language->getID(), 
                $def['text'],
                $def['line']
            );
        } 
    }
    
    protected function relativizePath($filePath)
    {
        return $filePath;
    }
    
    protected function log($message)
    {
        Localization::log(sprintf(
            'Source [%s] | %s',
            $this->getID(),
            $message
        ));
    }
    
    public function getHashes(Localization_Scanner $scanner)
    {
        $scanner->load();
        $collection = $scanner->getCollection();
        return $collection->getHashesBySourceID($this->getID());
    }
    
    public function countUntranslated($scanner)
    {
        $translator = Localization::getTranslator();
        $amount = 0;
        
        $hashes = $this->getHashes($scanner);
        foreach($hashes as $hash) {
            $text = $translator->getHashTranslation($hash->getHash());
            if(empty($text)) {
                $amount++;
            }
        }
        
        return $amount;
    }
}
