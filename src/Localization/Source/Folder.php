<?php

namespace AppLocalize;

class Localization_Source_Folder extends Localization_Source
{
   /**
    * The folder under which all translateable files are kept.
    * @var string
    */
    protected $rootFolder;
    
    protected $id;
    
    public function __construct($alias, $label, $group, $storageFolder, $rootFolder)
    {
        parent::__construct($alias, $label, $group, $storageFolder);
        
        $this->rootFolder = $rootFolder;
        $this->id = md5($rootFolder);
    }
    
    public function getID()
    {
        return $this->id;
    }
    
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    protected $excludes = array(
        'folders' => array(),
        'files' => array()
    );
    
    public function excludeFolders($folders)
    {
        $this->excludes['folders'] = array_merge($this->excludes['folders'], $folders);
        return $this;
    }
    
    public function excludeFiles($files)
    {
        $this->excludes['files'] = array_merge($this->excludes['files'], $files);
        return $this;
    }
    
    protected function _scan()
    {
        $this->processFolder($this->getRootFolder());
    }

    /**
     * Processes the target folder, and recurses into subfolders.
     * @param string $folder
     */
    protected function processFolder($folder)
    {
        /* @var $item \DirectoryIterator */
        $d = new \DirectoryIterator($folder);
        foreach ($d as $item) 
        {
            if ($item->isDot()) {
                continue;
            }
            
            $filename = $item->getFilename();
            
            if ($item->isDir() && !in_array($filename, $this->excludes['folders'])) {
                $this->processFolder($item->getPathname());
                continue;
            }
            
            if ($item->isFile() && $this->parser->isFileSupported($filename) && !$this->isExcluded($filename)) {
                $this->parseFile($item->getPathname());
            }
        }
    }
    
    protected function isExcluded($filename)
    {
        foreach ($this->excludes['files'] as $search) {
            if (stristr($filename, $search)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function relativizePath($filePath)
    {
        return ltrim(str_replace('\\', '/', str_replace($this->getRootFolder(), '', $filePath)), '/');
    }
}