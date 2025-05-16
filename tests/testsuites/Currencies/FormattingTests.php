<?php

declare(strict_types=1);

namespace testsuites\Currencies;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization_Country_CA;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization\Country\CountryMX;
use AppLocalize\Localization\Country\CountryUK;
use AppLocalize\Localization\Country\CountryUS;
use PHPUnit\Framework\TestCase;

class FormattingTests extends TestCase
{
    public function test_US_USD() : void
    {
        $currency = CountryCollection::getInstance()->choose()->us()->getCurrency();

        $this->assertSame('USD', $currency->getISO());
        $this->assertSame('1,445.45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1,445.45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445,45'));
        $this->assertSame('$1,445.45', $currency->makeReadable(1445.45));
        $this->assertSame('-$1,445.45', $currency->makeReadable(-1445.45));
    }

    public function test_DE_EUR() : void
    {
        $currency = CountryCollection::getInstance()->choose()->de()->getCurrency();

        $this->assertSame('EUR', $currency->getISO());
        $this->assertSame('1.445,45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1.445,45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445.45'));
        $this->assertSame('1.445,45 €', $currency->makeReadable(1445.45));
        $this->assertSame('-1.445,45 €', $currency->makeReadable(-1445.45));
    }

    public function test_FR_EUR() : void
    {
        $currency = CountryCollection::getInstance()->choose()->fr()->getCurrency();

        $this->assertSame('EUR', $currency->getISO());
        $this->assertSame('1 445,45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1.445,45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445.45'));
        $this->assertSame('1 445,45 €', $currency->makeReadable(1445.45));
        $this->assertSame('- 1 445,45 €', $currency->makeReadable(-1445.45));
    }

    public function test_MX_MXN() : void
    {
        $currency = CountryCollection::getInstance()->choose()->mx()->getCurrency();

        $this->assertSame('MXN', $currency->getISO());
        $this->assertSame('1,445.45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1.445,45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445.45'));
        $this->assertSame('MXN 1,445.45', $currency->makeReadable(1445.45));
        $this->assertSame('MXN -1,445.45', $currency->makeReadable(-1445.45));
    }

    public function test_CA_CAD() : void
    {
        $currency = CountryCollection::getInstance()->choose()->ca()->getCurrency();

        $this->assertSame('CAD', $currency->getISO());
        $this->assertSame('1,445.45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1.445,45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445.45'));
        $this->assertSame('$ 1,445.45', $currency->makeReadable(1445.45));
        $this->assertSame('$ -1,445.45', $currency->makeReadable(-1445.45));
    }

    public function test_UK_GBP() : void
    {
        $currency = CountryCollection::getInstance()->choose()->uk()->getCurrency();

        $this->assertSame('GBP', $currency->getISO());
        $this->assertSame('1,445.45', $currency->formatNumber(1445.45));
        $this->assertSame('1445.45', $currency->normalizeNumber('1.445,45'));
        $this->assertSame('1445.45', $currency->normalizeNumber('1 445.45'));
        $this->assertSame('£1,445.45', $currency->makeReadable(1445.45));
        $this->assertSame('-£1,445.45', $currency->makeReadable(-1445.45));
    }
}
