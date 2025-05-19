<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Parser;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;

final class ParserTests extends TestCase
{
    /**
     * Check that an exception is thrown when trying
     * to parse a file with an extension that is not
     * supported.
     */
    public function test_unsupportedFiles(): void
    {
        $parser = Localization::createScanner()->getParser();

        $this->expectException(LocalizationException::class);

        $parser->parseFile('/path/to/unknown/file.txt');
    }

    /**
     * Check that the file not found exception is triggered
     * if the file to parse does not exist.
     */
    public function test_fileNotFound(): void
    {
        $parser = Localization::createScanner()->getParser();

        $this->expectException(LocalizationException::class);

        $lang = $parser->parseFile('/path/to/unknown/file.js');
    }
}