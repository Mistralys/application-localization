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

    public function test_allCountriesAccountedFor() : void
    {
        $countries = CountryCollection::getInstance();
        $expectedCount = 19;

        $checkCountries = array(
            $countries->choose()->at(),
            $countries->choose()->be(),
            $countries->choose()->ca(),
            $countries->choose()->ch(),
            $countries->choose()->de(),
            $countries->choose()->es(),
            $countries->choose()->fi(),
            $countries->choose()->fr(),
            $countries->choose()->gb(),
            $countries->choose()->ie(),
            $countries->choose()->it(),
            $countries->choose()->mx(),
            $countries->choose()->nl(),
            $countries->choose()->pl(),
            $countries->choose()->ro(),
            $countries->choose()->se(),
            $countries->choose()->sg(),
            $countries->choose()->us(),
            $countries->choose()->zz()
        );

        $this->assertCount($expectedCount, $countries->getAll());
        $this->assertCount($expectedCount, $checkCountries);
    }
}
