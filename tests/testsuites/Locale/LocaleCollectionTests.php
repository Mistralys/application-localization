<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Locale;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;

final class LocaleCollectionTests extends TestCase
{
    public function test_allLocalesAccountedFor() : void
    {
        $collection = Localization\Locales\LocalesCollection::getInstance();
        $expectedNumber = 18;

        $this->assertCount($expectedNumber, $collection->getAll());

        $canned = array(
            $collection->choose()->de_AT(),
            $collection->choose()->de_CH(),
            $collection->choose()->de_DE(),
            $collection->choose()->en_CA(),
            $collection->choose()->en_GB(),
            $collection->choose()->en_IE(),
            $collection->choose()->en_SG(),
            $collection->choose()->en_US(),
            $collection->choose()->es_ES(),
            $collection->choose()->es_MX(),
            $collection->choose()->fi_FI(),
            $collection->choose()->fr_BE(),
            $collection->choose()->fr_FR(),
            $collection->choose()->it_IT(),
            $collection->choose()->nl_NL(),
            $collection->choose()->pl_PL(),
            $collection->choose()->ro_RO(),
            $collection->choose()->sv_SE(),
        );

        $this->assertCount($expectedNumber, $canned);
    }
}
