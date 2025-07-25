<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Events;

use AppLocalize\Localization\Locale\de_DE;
use AppLocalize\Localization\Locale\en_GB;
use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppLocalize\Localization\Event\LocaleChanged;

final class EventTests extends TestCase
{
    // region: _Tests

    /**
     * Ensure that the onLocaleChanged event is triggered
     * as expected.
     */
    public function test_changeLocale(): void
    {
        Localization::onLocaleChanged(array($this, 'handle_onLocaleChanged'));
        Localization::addAppLocale(de_DE::LOCALE_NAME);
        Localization::selectAppLocale(de_DE::LOCALE_NAME);

        $this->assertLocaleChangedEvent(en_GB::LOCALE_NAME, de_DE::LOCALE_NAME);

        $this->changeEvent = null;

        Localization::selectAppLocale(en_GB::LOCALE_NAME);

        $this->assertLocaleChangedEvent(de_DE::LOCALE_NAME, en_GB::LOCALE_NAME);
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

        $this->assertEquals('bar', $this->changeFooValue);
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

        $event = $this->changeEvent; // As local variable for PHPStan
        $this->assertNotNull($event, 'No event triggered');
        $this->assertInstanceOf(LocaleChanged::class, $event);

        $this->assertEquals(Localization::NAMESPACE_APPLICATION, $event->getNamespace());
        $this->assertTrue($event->isAppLocale());
        $this->assertFalse($event->isContentLocale());
    }

    // endregion

    // region: Support methods

    protected ?LocaleChanged $changeEvent = null;
    protected string $changeFooValue = '';

    protected function setUp(): void
    {
        Localization::reset();

        $this->assertEquals(Localization::BUILTIN_LOCALE_NAME, Localization::getAppLocaleName());
        $this->assertEquals(Localization::BUILTIN_LOCALE_NAME, Localization::getContentLocaleName());

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

    private function assertLocaleChangedEvent(string $fromLocale, string $toLocale) : void
    {
        $event = $this->changeEvent; // As local variable for PHPStan

        $this->assertNotNull($event, 'No event triggered');
        $this->assertInstanceOf(LocaleChanged::class, $event);

        $previous = $event->getPrevious();

        $this->assertNotNull($previous, 'No previous locale set');
        $this->assertEquals($fromLocale, $previous->getName());
        $this->assertEquals($toLocale, $event->getCurrent()->getName());
    }

    // endregion
}
