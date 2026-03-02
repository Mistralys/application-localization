<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Tools;

use AppLocalize\Localization;
use AppLocalize\Tools\TranslationExporter;
use AppLocalize\Tools\TranslationImporter;
use PHPUnit\Framework\TestCase;

/**
 * Smoke tests for the {@see Localization} facade factory methods
 * {@see Localization::createExporter()} and {@see Localization::createImporter()}.
 *
 * @package AppLocalize
 * @subpackage Tests
 */
class LocalizationFacadeToolsTest extends TestCase
{
    public function test_createExporter_returnsTranslationExporterInstance() : void
    {
        $this->assertInstanceOf(
            TranslationExporter::class,
            Localization::createExporter()
        );
    }

    public function test_createImporter_returnsTranslationImporterInstance() : void
    {
        $this->assertInstanceOf(
            TranslationImporter::class,
            Localization::createImporter()
        );
    }
}
