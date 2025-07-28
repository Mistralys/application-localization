<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\TimeZones;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Country\CountryAT;
use AppLocalize\Localization\Country\CountryBE;
use AppLocalize\Localization\Country\CountryCH;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryES;
use AppLocalize\Localization\Country\CountryFI;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization\Country\CountryGB;
use AppLocalize\Localization\Country\CountryIE;
use AppLocalize\Localization\Country\CountryIT;
use AppLocalize\Localization\Country\CountryNL;
use AppLocalize\Localization\Country\CountryPL;
use AppLocalize\Localization\Country\CountryRO;
use AppLocalize\Localization\Country\CountrySE;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppLocalize\Localization\TimeZone\Globals\GlobalCETTimeZone;
use AppLocalize\Localization\TimeZone\Globals\GlobalUTCTimeZone;
use AppLocalize\Localization\TimeZones\TimeZoneCollection;
use PHPUnit\Framework\TestCase;

class TimeZoneTests extends TestCase
{
    public function test_getByISO() : void
    {
        $tests = array(
            'at' => 'Europe/Vienna',
            'be' => 'Europe/Brussels',
            'ca' => 'America/Vancouver',
            'ch' => 'Europe/Zurich',
            'de' => 'Europe/Berlin',
            'es' => 'Europe/Madrid',
            'fi' => 'Europe/Helsinki',
            'fr' => 'Europe/Paris',
            'gb' => 'Europe/London',
            'ie' => 'Europe/Dublin',
            'it' => 'Europe/Rome',
            'mx' => 'America/Mexico_City',
            'nl' => 'Europe/Amsterdam',
            'pl' => 'Europe/Warsaw',
            'ro' => 'Europe/Bucharest',
            'se' => 'Europe/Stockholm',
            'sg' => 'Asia/Singapore',
            'uk' => 'Europe/London',
            'us' => 'US/Eastern',
        );

        $timeZones = TimeZoneCollection::getInstance();

        foreach($tests as $iso => $timezone) {
            $zone = $timeZones->findByCountry($iso);
            $this->assertNotNull($zone, "Time zone not found for ISO: $iso");
            $this->assertSame($timezone, $zone->getID());
        }
    }

    public function test_getByLocale() : void
    {
        $tests = array(
            'de_AT' => 'Europe/Vienna',
            'de_CH' => 'Europe/Zurich',
            'de_DE' => 'Europe/Berlin',
            'en_CA' => 'America/Vancouver',
            'en_GB' => 'Europe/London',
            'en_IE' => 'Europe/Dublin',
            'en_SG' => 'Asia/Singapore',
            'en_UK' => 'Europe/London',
            'en_US' => 'US/Eastern',
            'es_ES' => 'Europe/Madrid',
            'es_MX' => 'America/Mexico_City',
            'fi_FI' => 'Europe/Helsinki',
            'fr_FR' => 'Europe/Paris',
            'it_IT' => 'Europe/Rome',
            'nl_NL' => 'Europe/Amsterdam',
            'pl_PL' => 'Europe/Warsaw',
            'ro_RO' => 'Europe/Bucharest',
            'sv_SE' => 'Europe/Stockholm'
        );

        $timeZones = TimeZoneCollection::getInstance();

        foreach($tests as $locale => $timezone) {
            $zone = $timeZones->findByLocale($locale);
            $this->assertNotNull($zone, "Time zone not found for locale: $locale");
            $this->assertSame($timezone, $zone->getID(), "Time zone should be $timezone for locale $locale, but got " . $zone->getID());
        }
    }

    public function test_UTCTimeZoneContainsAllKnownCountries() : void
    {
        $timeZone = TimeZoneCollection::getInstance()->getByID(GlobalUTCTimeZone::ZONE_ID);

        $this->assertInstanceOf(GlobalUTCTimeZone::class, $timeZone);

        $this->assertSame(CountryCollection::getInstance()->countRecords(), $timeZone->getCountries()->countRecords(), 'Global UTC time zone should have the same number of countries as the country collection.');
    }

    public function test_UTCTimeZoneContainsAllKnownLocales() : void
    {
        $timeZone = TimeZoneCollection::getInstance()->getByID(GlobalUTCTimeZone::ZONE_ID);

        $this->assertInstanceOf(GlobalUTCTimeZone::class, $timeZone);

        $this->assertSame(LocalesCollection::getInstance()->countRecords(), $timeZone->getLocales()->countRecords(), 'Global UTC time zone should have the same number of locales as the locale collection.');
    }

    public function test_CETContainsAllKnownEuropeanCountries() : void
    {
        $timeZone = TimeZoneCollection::getInstance()->getByID(GlobalCETTimeZone::ZONE_ID);

        $this->assertInstanceOf(GlobalCETTimeZone::class, $timeZone);

        $expected = array(
            CountryNL::ISO_CODE,
            CountryDE::ISO_CODE,
            CountryFR::ISO_CODE,
            CountryBE::ISO_CODE,
            CountryPL::ISO_CODE,
            CountryRO::ISO_CODE,
            CountryIT::ISO_CODE,
            CountryES::ISO_CODE,
            CountryAT::ISO_CODE,
            CountryCH::ISO_CODE,
            CountryIE::ISO_CODE,
            CountryGB::ISO_CODE,
            CountryFI::ISO_CODE,
            CountrySE::ISO_CODE,
        );

        sort($expected);

        $this->assertSame($expected, $timeZone->getCountries()->getIDs());
    }
}
