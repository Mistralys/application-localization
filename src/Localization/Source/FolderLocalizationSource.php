<?php
/**
 * @package Localization
 * @subpackage Parser
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Source;

use AppLocalize\Localization\LocalizationException;
use DirectoryIterator;

/**
 * Localization source that reads text to translate from files
 * stored in a folder.
 *
 * @package Localization
 * @subpackage Parser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FolderLocalizationSource extends BaseLocalizationSource
{
   /**
    * The folder under which all translatable files are kept.
    * @var string
    */
    protected string $sourcesFolder;
    
    protected string $id;

   /**
    * @param string $alias An alias for this source, to recognize it by.
    * @param string $label The human-readable label, used in the editor.
    * @param string $group A human-readable group label to group several sources by. Used in the editor.
    * @param string $storageFolder The folder in which to store the localization files.
    * @param string $sourcesFolder The folder in which to analyze files to find translatable strings.
    */
    public function __construct(string $alias, string $label, string $group, string $storageFolder, string $sourcesFolder)
    {
        parent::__construct($alias, $label, $group, $storageFolder);
        
        $this->sourcesFolder = $sourcesFolder;
        $this->id = md5($sourcesFolder);
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getSourcesFolder() : string
    {
        return $this->sourcesFolder;
    }

    /**
     * @var array<string,string[]>
     */
    protected array $excludes = array(
        'folders' => array(),
        'files' => array()
    );
    
    public function excludeFolder(string $folder) : FolderLocalizationSource
    {
        if(!in_array($folder, $this->excludes['folders'], true)) {
            $this->excludes['folders'][] = $folder;
        }
        
        return $this;
    }

    /**
     * @param string[] $folders
     * @return $this
     */
    public function excludeFolders(array $folders) : FolderLocalizationSource
    {
        foreach($folders as $folder) {
            $this->excludeFolder($folder);
        }
        
        return $this;
    }

    /**
     * @param string[] $files
     * @return $this
     */
    public function excludeFiles(array $files) : FolderLocalizationSource
    {
        $this->excludes['files'] = array_merge($this->excludes['files'], $files);
        return $this;
    }
    
    protected function _scan(SourceScanner $scanner) : void
    {
        $this->processFolder($this->getSourcesFolder(), $scanner);
    }

    /**
     * Processes the target folder, and recurses into subfolders.
     * @param string $folder
     * @param SourceScanner $scanner
     * @throws LocalizationException
     */
    protected function processFolder(string $folder, SourceScanner $scanner) : void
    {
        $parser = $scanner->getParser();
        $d = new DirectoryIterator($folder);
        foreach ($d as $item) 
        {
            if ($item->isDot()) {
                continue;
            }
            
            $filename = $item->getFilename();
            
            if ($item->isDir() && !in_array($filename, $this->excludes['folders'])) {
                $this->processFolder($item->getPathname(), $scanner);
                continue;
            }
            
            if ($item->isFile() && $parser->isFileSupported($filename) && !$this->isExcluded($filename)) {
                $scanner->parseFile($item->getPathname());
            }
        }
    }
    
    protected function isExcluded(string $filename) : bool
    {
        foreach ($this->excludes['files'] as $search) {
            if (stristr($filename, $search)) {
                return true;
            }
        }
        
        return false;
    }
}
