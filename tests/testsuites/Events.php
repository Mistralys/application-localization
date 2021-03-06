<?php

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization_Event_LocaleChanged;

final class EventsTest extends TestCase
{
   /**
    * @var Localization_Event_LocaleChanged
    */
    protected $changeEvent = null;
    
   /**
    * @var string
    */
    protected $changeFooValue = '';
    
    protected function setUp() : void
    {
        Localization::reset();
        
        $this->changeEvent = null;
        $this->changeFooValue = '';
    }

   /**
    * Event handler used to store the result of a triggered event.
    *
    * @param Localization_Event_LocaleChanged $event
    * @param string $foo
    */
    public function handle_onLocaleChanged(Localization_Event_LocaleChanged $event, string $foo='')
    {
        $this->changeEvent = $event;
        $this->changeFooValue = $foo;
    }
    
   /**
    * Ensure that the onLocaleChanged event is triggered
    * as expected.
    */
    public function test_changeLocale()
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));
        
        Localization::addAppLocale('de_DE');
        
        Localization::selectAppLocale('de_DE');
        
        $this->assertInstanceOf(Localization_Event_LocaleChanged::class, $this->changeEvent);
        $this->assertEquals($this->changeEvent->getPrevious()->getName(), 'en_UK');
        $this->assertEquals($this->changeEvent->getCurrent()->getName(), 'de_DE');
        
        $this->changeEvent = null;
        
        Localization::selectAppLocale('en_UK');
        
        $this->assertInstanceOf(Localization_Event_LocaleChanged::class, $this->changeEvent);
        $this->assertEquals($this->changeEvent->getPrevious()->getName(), 'de_DE');
        $this->assertEquals($this->changeEvent->getCurrent()->getName(), 'en_UK');
    }

   /**
    * Ensure that optional event listener arguments
    * are passed through as expected.
    */
    public function test_changeLocale_args()
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'), array('bar'));
        
        Localization::addAppLocale('de_DE');
        
        Localization::selectAppLocale('de_DE');
        
        $this->assertEquals($this->changeFooValue, 'bar');
    }
    
   /**
    * Make sure that trying to set the same locale again
    * does not trigger the change locale event.
    */
    public function test_changeLocale_unchanged()
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));
        
        Localization::selectAppLocale('en_UK');
        
        $this->assertNull($this->changeEvent);
    }
    
   /**
    * The event must specifiy the correct namespace information.
    */
    public function test_changeLocale_namespace()
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));
        
        Localization::addAppLocale('de_DE');
        
        Localization::selectAppLocale('de_DE');
        
        $this->assertEquals($this->changeEvent->getNamespace(), Localization::NAMESPACE_APPLICATION);
        $this->assertTrue($this->changeEvent->isAppLocale());
        $this->assertFalse($this->changeEvent->isContentLocale());
    }
}
