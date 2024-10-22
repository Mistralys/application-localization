<?php

declare(strict_types=1);

namespace testsuites\Currencies;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\CurrencyCollection;
use AppLocalize\Localization\Currencies\CurrencyInterface;
use AppLocalize\Localization_Currency_EUR;
use AppLocalize\Localization_Currency_USD;
use PHPUnit\Framework\TestCase;

class CurrencyTests extends TestCase
{
    public function test_choose() : void
    {
        $usd = CurrencyCollection::getInstance()->choose()->usd();

        $this->assertSame('USD', $usd->getISO());
        $this->assertTrue($usd->isSymbolOnFront());
    }

    public function test_getByISO() : void
    {
        $this->assertInstanceOf(Localization_Currency_USD::class, CurrencyCollection::getInstance()->getByISO('USD'));
        $this->assertInstanceOf(Localization_Currency_USD::class, CurrencyCollection::getInstance()->getByISO('usd'));
    }

    public function test_isoExists() : void
    {
        $this->assertTrue(CurrencyCollection::getInstance()->isoExists('USD'));
        $this->assertTrue(CurrencyCollection::getInstance()->isoExists('usd'));
    }

    public function test_EUR_getCountries() : void
    {
        $countries = CountryCollection::getInstance();
        $currency = CurrencyCollection::getInstance()->choose()->eur();

        $this->assertCount(5, $currency->getCountries());
        $this->assertCurrencyHasCountry($currency, $countries->choose()->de());
        $this->assertCurrencyHasCountry($currency, $countries->choose()->es());
        $this->assertCurrencyHasCountry($currency, $countries->choose()->fr());
        $this->assertCurrencyHasCountry($currency, $countries->choose()->it());
        $this->assertCurrencyHasCountry($currency, $countries->choose()->at());
    }

    public function assertCurrencyHasCountry(CurrencyInterface $currency, CountryInterface $country) : void
    {
        $targetCode = $country->getCode();

        $found = false;
        $codes = array();
        foreach($currency->getCountries() as $c) {
            $codes[] = $c->getCode();
            if($c->getCode() === $targetCode) {
                $found = true;
            }
        }

        $this->assertTrue(
            $found,
            sprintf(
                'The currency [%s] does not have the country [%s]. '.PHP_EOL.
                'Available countries are: '.PHP_EOL.
                '- %s',
                $currency->getISO(),
                $country->getCode(),
                implode(PHP_EOL.'- ', $codes)
            )
        );
    }
}
