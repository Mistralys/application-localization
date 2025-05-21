<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Core;

use HTML_QuickForm2;
use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;
use function AppLocalize\t;

final class LocalizationCoreTests extends TestCase
{
    protected function setUp(): void
    {
        Localization::reset();
    }

    public function test_defaultLocale_app(): void
    {
        $this->assertEquals(
            Localization::BUILTIN_LOCALE_NAME,
            Localization::getAppLocaleName(),
            'Default app locale should match'
        );

        $this->assertCount(
            1,
            Localization::getAppLocales(),
            'By default a single app locale should be present.'
        );
    }

    public function test_defaultLocale_content(): void
    {
        $this->assertEquals(
            Localization::BUILTIN_LOCALE_NAME,
            Localization::getContentLocaleName(),
            'Default content locale should match'
        );

        $this->assertCount(
            1,
            Localization::getContentLocales(),
            'By default a single content locale should be present.'
        );
    }

    /**
     * Custom namespaces have no default locale.
     */
    public function test_defaultLocale_custom(): void
    {
        $this->expectException(LocalizationException::class);

        Localization::getLocaleNameByNS('custom');
    }

    public function test_addLocale_app(): void
    {
        Localization::addAppLocale('de_DE');

        $this->assertEquals('de_DE', Localization::getAppLocaleByName('de_DE')->getName());
    }

    public function test_addLocale_content(): void
    {
        Localization::addContentLocale('de_DE');

        $this->assertEquals('de_DE', Localization::getContentLocaleByName('de_DE')->getName());
    }

    public function test_addLocale_custom(): void
    {
        Localization::addLocaleByNS('de_DE', 'custom');

        $this->assertEquals('de_DE', Localization::getLocaleByNameNS('de_DE', 'custom')->getName());
    }

    public function test_addLocale_invalidLocale(): void
    {
        try {
            Localization::addAppLocale('invalid_locale');
        } catch (LocalizationException $e) {
            $this->assertSame(LocalizationException::ERROR_LOCALE_NOT_FOUND, $e->getCode());
            return;
        }

        $this->fail('No exception or other exception thrown.');
    }

    public function test_selectLocale_content(): void
    {
        Localization::addContentLocale('de_DE');
        Localization::selectContentLocale('de_DE');

        $this->assertEquals(
            'de_DE',
            Localization::getContentLocale()->getName(),
            'Content locale name should match the selected locale.'
        );

        $this->assertEquals(
            'de_DE',
            Localization::getContentLocaleName(),
            'Content locale name method should match as well as the locale instance.'
        );
    }

    public function test_selectLocale_app(): void
    {
        Localization::addAppLocale('de_DE');
        Localization::selectAppLocale('de_DE');

        $this->assertEquals(
            'de_DE',
            Localization::getAppLocale()->getName(),
            'App locale name should match the selected locale.'
        );

        $this->assertEquals(
            'de_DE',
            Localization::getAppLocaleName(),
            'App locale name method should match as well as the locale instance.'
        );
    }

    public function test_selectLocale_custom(): void
    {
        Localization::addLocaleByNS('de_DE', 'custom');
        Localization::selectLocaleByNS('de_DE', 'custom');

        $this->assertEquals(
            'de_DE',
            Localization::getSelectedLocaleByNS('custom')->getName(),
            'Custom locale name should match the selected locale.'
        );

        $this->assertEquals(
            'de_DE',
            Localization::getLocaleNameByNS('custom'),
            'Custom locale name method should match as well as the locale instance.'
        );
    }

    public function test_getLocaleNames_app(): void
    {
        Localization::addAppLocale('de_DE');

        $list = array(
            Localization::BUILTIN_LOCALE_NAME,
            'de_DE'
        );

        // namespaces are sorted automatically
        sort($list);

        $this->assertEquals(
            $list,
            Localization::getAppLocaleNames(),
            'The list of app locales should match.'
        );
    }

    public function test_getLocaleNames_content(): void
    {
        Localization::addContentLocale('de_DE');

        $list = array(
            Localization::BUILTIN_LOCALE_NAME,
            'de_DE'
        );

        // namespaces are sorted automatically
        sort($list);

        $this->assertEquals(
            $list,
            Localization::getContentLocaleNames(),
            'The list of content locales should match.'
        );
    }

    public function test_getLocaleNames_custom(): void
    {
        Localization::addLocaleByNS('de_DE', 'custom');

        // no default locale for custom namespaces
        $list = array(
            'de_DE'
        );

        $this->assertEquals(
            $list,
            Localization::getLocaleNamesByNS('custom'),
            'The list of custom locales should match.'
        );
    }

    public function test_localeExists_app(): void
    {
        $this->assertFalse(Localization::appLocaleExists('de_DE'));

        Localization::addAppLocale('de_DE');

        $this->assertTrue(Localization::appLocaleExists('de_DE'));
    }

    public function test_localeExists_content(): void
    {
        $this->assertFalse(Localization::contentLocaleExists('de_DE'));

        Localization::addContentLocale('de_DE');

        $this->assertTrue(Localization::contentLocaleExists('de_DE'));
    }

    public function test_localeExists_custom(): void
    {
        $this->assertFalse(Localization::localeExistsInNS('de_DE', 'custom'));

        Localization::addLocaleByNS('de_DE', 'custom');

        $this->assertTrue(Localization::localeExistsInNS('de_DE', 'custom'));
    }

    public function test_injectLocalesSelector_app(): void
    {
        $form = new HTML_QuickForm2('dummy');

        Localization::addAppLocale('de_DE');

        $select = Localization::injectAppLocalesSelector('select-app-locale', $form);

        $this->assertEquals('select-app-locale', $select->getName());
        $this->assertEquals(t('Language'), $select->getLabel());
        $this->assertEquals(2, $select->countOptions());
    }

    public function test_injectLocalesSelector_content(): void
    {
        $form = new HTML_QuickForm2('dummy');

        Localization::addContentLocale('fr_FR');

        $select = Localization::injectContentLocalesSelector('select-content-locale', $form);

        $this->assertEquals('select-content-locale', $select->getName());
        $this->assertEquals(t('Language'), $select->getLabel());
        $this->assertEquals(2, $select->countOptions());
    }

    public function test_getSelectedCurrency_app(): void
    {
        Localization::addAppLocale('de_DE');
        Localization::selectAppLocale('de_DE');

        $currency = Localization::getAppCurrency();

        $this->assertEquals('€', $currency->getSymbol());
        $this->assertEquals('EUR', $currency->getISO());
    }

    public function test_getSelectedCurrency_content(): void
    {
        Localization::addContentLocale('de_DE');
        Localization::selectContentLocale('de_DE');

        $currency = Localization::getContentCurrency();

        $this->assertEquals('€', $currency->getSymbol());
        $this->assertEquals('EUR', $currency->getISO());
    }

    public function test_getSelectedCurrency_custom(): void
    {
        Localization::addLocaleByNS('de_DE', 'custom');
        Localization::selectLocaleByNS('de_DE', 'custom');

        $currency = Localization::getCurrencyNS('custom');

        $this->assertEquals('€', $currency->getSymbol());
        $this->assertEquals('EUR', $currency->getISO());
    }
}
