<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Locale;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;

final class LocaleTests extends TestCase
{
    protected function setUp(): void
    {
        Localization::reset();
    }

    public function test_getLanguageCode(): void
    {
        $tests = array(
            'de_DE' => 'de',
            'en_UK' => 'en',
            'fr_FR' => 'fr'
        );

        foreach ($tests as $localeName => $languageCode) {
            $locale = Localization::addAppLocale($localeName);

            $this->assertEquals(
                $languageCode,
                $locale->getLanguageCode(),
                'The language code should match locale ' . $localeName
            );
        }
    }

    public function test_getCountryCode(): void
    {
        $tests = array(
            'de_DE' => 'de',
            'en_UK' => 'gb',
            'fr_FR' => 'fr',
            'en_CA' => 'ca'
        );

        foreach ($tests as $localeName => $countryCode) {
            $locale = Localization::addAppLocale($localeName);

            $this->assertEquals(
                $countryCode,
                $locale->getCountryCode(),
                'The country code should match locale ' . $localeName
            );
        }
    }

    public function test_getName(): void
    {
        $tests = array(
            'de_DE' => 'de_DE',
            'en_UK' => 'en_GB',
            'fr_FR' => 'fr_FR',
            'en_CA' => 'en_CA',
        );

        foreach ($tests as $localeName => $actualName) {
            $locale = Localization::addAppLocale($localeName);

            $this->assertEquals(
                $actualName,
                $locale->getName(),
                'The locale name should match.'
            );
        }
    }

    public function test_isNative(): void
    {
        $tests = array(
            Localization::BUILTIN_LOCALE_NAME => true,
            'de_DE' => false,
            'fr_FR' => false,
        );

        foreach ($tests as $localeName => $isNative) {
            $locale = Localization::addAppLocale($localeName);

            $this->assertEquals(
                $isNative,
                $locale->isNative(),
                'The locale native status should match.'
            );
        }
    }

    public function test_getLabel(): void
    {
        $this->assertEquals('English (Great Britain)', Localization::getAppLocale()->getLabel());
    }
}
