<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\TimeZones;

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
}
