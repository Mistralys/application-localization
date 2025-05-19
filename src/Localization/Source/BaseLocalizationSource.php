<?php
/**
 * @package Localization
 * @subpackage Parser
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Source;

use AppLocalize\Localization;
use AppLocalize\Localization\Scanner\LocalizationScanner;

/**
 * Base source class for a location that stores texts to translate.
 * This can be from the file system, as well as a database, for example.
 * Each source type must extend this class.
 *
 * Sources are added manually when configuring the localization layer.
 * For example, see {@see Localization::addSourceFolder()}.
 *
 * @package Localization
 * @subpackage Parser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see FolderLocalizationSource
 */
abstract class BaseLocalizationSource
{
   /**
    * Human-readable label for the source.
    * @var string
    */
    protected string $label;
    
   /**
    * Human-readable group name to categorize the source.
    * @var string
    */
    protected string $group;
    
   /**
    * The folder in which the localization files are stored
    * @var string
    */
    protected string $storageFolder;
    
   /**
    * @var string
    */
    protected string $alias;

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
    
    public function scan(LocalizationScanner $scanner) : void
    {
        $this->_scan($this->getSourceScanner($scanner));
    }

    /**
     * Retrieves the scanner instance that can access this
     * source's string hashes and parsing methods.
     *
     * @param LocalizationScanner $scanner
     * @return SourceScanner
     */
    public function getSourceScanner(LocalizationScanner $scanner) : SourceScanner
    {
        return new SourceScanner($this, $scanner);
    }
    
    abstract protected function _scan(SourceScanner $scanner) : void;

    protected function log(string $message) : void
    {
        Localization::log(sprintf(
            'Source [%s] | %s',
            $this->getID(),
            $message
        ));
    }
}
