<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Source;

use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization\Parser\LocalizationParser;
use AppLocalize\Localization\Scanner\LocalizationScanner;
use AppLocalize\Localization\Scanner\StringHash;
use AppLocalize\Localization\Scanner\StringCollection;

class SourceScanner
{
    private LocalizationScanner $scanner;
    private BaseLocalizationSource $source;
    private StringCollection $collection;
    private LocalizationParser $parser;

    public function __construct(BaseLocalizationSource $source, LocalizationScanner $scanner)
    {
        $this->scanner = $scanner;
        $this->source = $source;
        $this->collection = $scanner->getCollection();
        $this->parser = $scanner->getParser();
    }

    /**
     * @return LocalizationParser
     */
    public function getParser() : LocalizationParser
    {
        return $this->parser;
    }

    /**
     * Parses the code of the target file to find all
     * supported function calls and extract the native
     * application language string from the code. Adds any
     * strings it finds to the results collection.
     *
     * @param string $file
     * @throws LocalizationException
     */
    public function parseFile(string $file) : void
    {
        $this->log(sprintf('Parsing file [%s].', $file));

        $language = $this->parser->parseFile($file);

        $texts = $language->getTexts();

        foreach($texts as $text)
        {
            $this->collection->addFromFile(
                $this->source->getID(),
                $file,
                $language->getID(),
                $text
            );
        }

        $warnings = $language->getWarnings();

        foreach($warnings as $warning)
        {
            $this->collection->addWarning($warning);
        }
    }

    /**
     * @return StringHash[]
     */
    public function getHashes() : array
    {
        $this->scanner->load();
        return $this->collection->getHashesBySourceID($this->source->getID());
    }

    public function countUntranslated() : int
    {
        $translator = Localization::getTranslator();
        $amount = 0;

        $hashes = $this->getHashes();

        foreach($hashes as $hash)
        {
            $text = $translator->getHashTranslation($hash->getHash());

            if(empty($text))
            {
                $amount++;
            }
        }

        return $amount;
    }

    protected function log(string $message) : void
    {
        Localization::log(sprintf(
            'Source [%s] | Scanner | %s',
            $this->source->getID(),
            $message
        ));
    }
}
