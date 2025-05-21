<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Countries;

use AppLocalize\Localization\Countries\CountryCollection;
use PHPUnit\Framework\TestCase;

final class CountryCollectionTests extends TestCase
{
    public function test_getByAlias() : void
    {
        $collection = CountryCollection::getInstance();

        $this->assertTrue($collection->idExists('gb'));
        $this->assertTrue($collection->idExists('uk'));

        $country = CountryCollection::getInstance()->getByID('uk');

        $this->assertSame('gb', $country->getID());
    }

    public function test_filterISOs() : void
    {
        $this->assertSame('gb', CountryCollection::getInstance()->filterCode('uk'));
        $this->assertSame('gb', CountryCollection::getInstance()->filterCode('gb'));
        $this->assertSame('fr', CountryCollection::getInstance()->filterCode('fr'));
    }
}
