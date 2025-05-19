<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Translator;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;

final class TranslatorTests extends TestCase
{
    protected function setUp(): void
    {
        Localization::reset();
    }

    /**
     * Ensure that the bundled translations work out of the box.
     */
    public function test_bundledTexts(): void
    {
        Localization::addAppLocale('de_DE');

        Localization::selectAppLocale('de_DE');

        $this->assertEquals('Deutschland', Localization::getAppLocale()->getCountry()->getLabel());
    }
}
