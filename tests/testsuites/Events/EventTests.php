<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Events;

use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\Event\LocaleChanged;

final class EventTests extends TestCase
{
    protected ?LocaleChanged $changeEvent = null;
    protected string $changeFooValue = '';

    protected function setUp(): void
    {
        Localization::reset();

        $this->assertEquals(Localization::getAppLocaleName(), Localization::BUILTIN_LOCALE_NAME);
        $this->assertEquals(Localization::getContentLocaleName(), Localization::BUILTIN_LOCALE_NAME);

        $this->changeEvent = null;
        $this->changeFooValue = '';
    }

    /**
     * Event handler used to store the result of a triggered event.
     *
     * @param LocaleChanged $event
     * @param string $foo
     */
    public function handle_onLocaleChanged(LocaleChanged $event, string $foo = ''): void
    {
        $this->changeEvent = $event;
        $this->changeFooValue = $foo;
    }

    /**
     * Ensure that the onLocaleChanged event is triggered
     * as expected.
     */
    public function test_changeLocale(): void
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));
        Localization::addAppLocale('de_DE');
        Localization::selectAppLocale('de_DE');

        $this->assertInstanceOf(LocaleChanged::class, $this->changeEvent);

        $this->assertNotNull($this->changeEvent, 'No event triggered');
        $this->assertEquals($this->changeEvent->getPrevious()->getName(), 'en_GB');
        $this->assertEquals($this->changeEvent->getCurrent()->getName(), 'de_DE');

        $this->changeEvent = null;

        Localization::selectAppLocale('en_UK');

        $this->assertInstanceOf(LocaleChanged::class, $this->changeEvent);
        $this->assertEquals($this->changeEvent->getPrevious()->getName(), 'de_DE');
        $this->assertEquals($this->changeEvent->getCurrent()->getName(), 'en_GB');
    }

    /**
     * Ensure that optional event listener arguments
     * are passed through as expected.
     */
    public function test_changeLocale_args(): void
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
    public function test_changeLocale_unchanged(): void
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));

        Localization::selectAppLocale('en_UK');

        $this->assertNull($this->changeEvent);
    }

    /**
     * The event must specify the correct namespace information.
     */
    public function test_changeLocale_namespace(): void
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));

        Localization::addAppLocale('de_DE');

        Localization::selectAppLocale('de_DE');

        $this->assertEquals($this->changeEvent->getNamespace(), Localization::NAMESPACE_APPLICATION);
        $this->assertTrue($this->changeEvent->isAppLocale());
        $this->assertFalse($this->changeEvent->isContentLocale());
    }
}
