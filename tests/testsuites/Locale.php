<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;

final class LocaleTest extends TestCase
{
    protected function setUp() : void
    {
        Localization::reset();
    }
    
    public function test_getLanguageCode() : void
    {
        $tests = array(
            'de_DE' => 'de',
            'en_UK' => 'en',
            'fr_FR' => 'fr'
        );
        
        foreach($tests as $localeName => $languageCode)
        {
            $locale = Localization::addAppLocale($localeName);
            
            $this->assertEquals(
                $languageCode, 
                $locale->getLanguageCode(), 
                'The language code should match locale '.$localeName
            );
        }
    }
    
    public function test_getCountryCode() : void
    {
        $tests = array(
            'de_DE' => 'de',
            'en_UK' => 'uk',
            'fr_FR' => 'fr',
            'en_CA' => 'ca' 
        );
        
        foreach($tests as $localeName => $countryCode)
        {
            $locale = Localization::addAppLocale($localeName);
            
            $this->assertEquals(
                $countryCode,
                $locale->getCountryCode(),
                'The country code should match locale '.$localeName
            );
        }
    }
    
    public function test_getName() : void
    {
        $tests = array(
            'de_DE',
            'en_UK',
            'fr_FR',
            'en_CA'
        );
        
        foreach($tests as $localeName)
        {
            $locale = Localization::addAppLocale($localeName);
            
            $this->assertEquals(
                $localeName,
                $locale->getName(),
                'The locale name should match.'
            );
        }
    }
    
    public function test_isNative() : void
    {
        $tests = array(
            Localization::BUILTIN_LOCALE_NAME => true,
            'de_DE' => false,
            'fr_FR' => false,
        );
        
        foreach($tests as $localeName => $isNative)
        {
            $locale = Localization::addAppLocale($localeName);
            
            $this->assertEquals(
                $isNative,
                $locale->isNative(),
                'The locale native status should match.'
            );
        }
    }
    
    public function test_getLabel() : void
    {
        $this->assertEquals('English (UK)', Localization::getAppLocale()->getLabel());
    }
}
