<?php

namespace AppLocalize;

abstract class Localization_Parser_Language
{
    protected $code;

    /**
     * @var Localization_Parser
     */
    protected $parser;

    protected $functionNames;

    public function __construct(Localization_Parser $parser)
    {
        $this->parser = $parser;
        $this->functionNames = $this->getFunctionNames();
    }
    
    protected $id;
    
    public function getID()
    {
        if(!isset($this->id)) {
            $this->id = str_replace('AppLocalize\Localization_Parser_Language_', '', get_class($this));
        }
        
        return $this->id;
    }
    
    public function parse($content)
    {
        $this->content = $content;
        $this->texts = array();
        
        $this->_parse();
    }
    
    public function getTexts()
    {
        return $this->texts;
    }
    
    abstract protected function _parse();

    protected $texts;
    
    protected function addResult($text, $line=null)
    {
        $this->log(sprintf('Line [%1$s] | Found string [%2$s]', $line, $text));
        $this->texts[] = array(
            'text' => $text, 
            'line' => $line
        );
    }

    abstract protected function getFunctionNames();

    protected function log($message)
    {
        Localization::log(sprintf('%1$s parser | %2$s', $this->getID(), $message));
    }
}