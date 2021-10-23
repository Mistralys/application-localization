<?php
/**
 * File containing the class {@see \AppLocalize\Localization_Source}.
 *
 * @package Localization
 * @subpackage Parser
 * @see \AppLocalize\Localization_Source
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * Base source class for a location that stores texts to translate.
 * This can be from the file system, as well as a database for example.
 * Each source type must extend this class.
 *
 * Sources are added manually when configuring the localization layer.
 * See for example {@see Localization::addSourceFolder()}.
 *
 * @package Localization
 * @subpackage Parser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Localization_Source_Folder
 */
abstract class Localization_Source
{
   /**
    * Human-readable label for the source.
    * @var string
    */
    protected $label;
    
   /**
    * Human-readable group name to categorize the source.
    * @var string
    */
    protected $group;
    
   /**
    * The folder in which the localization files are stored
    * @var string
    */
    protected $storageFolder;
    
   /**
    * @var string
    */
    protected $alias;

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
    
    public function scan(Localization_Scanner $scanner) : void
    {
        $this->_scan($this->getSourceScanner($scanner));
    }

    /**
     * Retrieves the scanner instance that can access this
     * source's string hashes and parsing methods.
     *
     * @param Localization_Scanner $scanner
     * @return Localization_Source_Scanner
     */
    public function getSourceScanner(Localization_Scanner $scanner) : Localization_Source_Scanner
    {
        return new Localization_Source_Scanner($this, $scanner);
    }
    
    abstract protected function _scan(Localization_Source_Scanner $scanner) : void;

    protected function log(string $message) : void
    {
        Localization::log(sprintf(
            'Source [%s] | %s',
            $this->getID(),
            $message
        ));
    }
}
