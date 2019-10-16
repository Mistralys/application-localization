<?php

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization_Exception;

final class LocalizationTest extends TestCase
{
    protected function setUp() : void
    {
        Localization::reset();
    }
    
    public function test_defaultLocale_app()
    {
        $this->assertEquals(
            Localization::BUILTIN_LOCALE_NAME, 
            Localization::getAppLocaleName(), 
            'Default app locale should match'
        );
        
        $this->assertEquals(
            1, 
            count(Localization::getAppLocales()), 
            'By default a single app locale should be present.'
        );
    }
    
    public function test_defaultLocale_content()
    {
        $this->assertEquals(
            Localization::BUILTIN_LOCALE_NAME,
            Localization::getContentLocaleName(),
            'Default content locale should match'
        );
        
        $this->assertEquals(
            1,
            count(Localization::getContentLocales()),
            'By default a single content locale should be present.'
        );
    }
    
   /**
    * Custom namespaces have no default locale.
    */
    public function test_defaultLocale_custom()
    {
        $this->expectException(Localization_Exception::class);
        
        Localization::getLocaleNameByNS('custom');
    }
    
    public function test_addLocale_app()
    {
        Localization::addAppLocale('de_DE');
        
        $this->assertEquals('de_DE', Localization::getAppLocaleByName('de_DE')->getName());
    }

    public function test_addLocale_content()
    {
        Localization::addContentLocale('de_DE');
        
        $this->assertEquals('de_DE', Localization::getContentLocaleByName('de_DE')->getName());
    }
    
    public function test_addLocale_custom()
    {
        Localization::addLocaleByNS('de_DE', 'custom');
        
        $this->assertEquals('de_DE', Localization::getLocaleByNameNS('de_DE', 'custom')->getName());
    }
    
    public function test_addLocale_invalidLocale()
    {
        $this->expectException(Localization_Exception::class);
        
        Localization::addAppLocale('invalid_locale');
    }
    
    public function test_selectLocale_content()
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
    
    public function test_selectLocale_app()
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
    
    public function test_selectLocale_custom()
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
    
    public function test_getLocaleNames_app()
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

    public function test_getLocaleNames_content()
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
    
    public function test_getLocaleNames_custom()
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
    
    public function test_localeExists_app()
    {
        $this->assertFalse(Localization::appLocaleExists('de_DE'));
        
        Localization::addAppLocale('de_DE');
        
        $this->assertTrue(Localization::appLocaleExists('de_DE'));
    }

    public function test_localeExists_content()
    {
        $this->assertFalse(Localization::contentLocaleExists('de_DE'));
        
        Localization::addContentLocale('de_DE');
        
        $this->assertTrue(Localization::contentLocaleExists('de_DE'));
    }

    public function test_localeExists_custom()
    {
        $this->assertFalse(Localization::localeExistsInNS('de_DE', 'custom'));
        
        Localization::addLocaleByNS('de_DE', 'custom');
        
        $this->assertTrue(Localization::localeExistsInNS('de_DE', 'custom'));
    }
    
    public function test_injectLocalesSelector_app()
    {
        $form = new HTML_QuickForm2('dummy');
        
        Localization::addAppLocale('de_DE');
        
        $select = Localization::injectAppLocalesSelector('select-app-locale', $form);
        
        $this->assertEquals('select-app-locale', $select->getName());
        $this->assertEquals(\AppLocalize\t('Language'), $select->getLabel());
        $this->assertEquals(2, $select->countOptions());
    }
    
    public function test_injectLocalesSelector_content()
    {
        $form = new HTML_QuickForm2('dummy');
        
        Localization::addContentLocale('fr_FR');
        
        $select = Localization::injectContentLocalesSelector('select-content-locale', $form);
        
        $this->assertEquals('select-content-locale', $select->getName());
        $this->assertEquals(\AppLocalize\t('Language'), $select->getLabel());
        $this->assertEquals(2, $select->countOptions());
    }
}
