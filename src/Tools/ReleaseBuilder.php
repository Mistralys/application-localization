<?php

declare(strict_types=1);

namespace AppLocalize\Tools;

use AppLocalize\Localization;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\CurrencyCollection;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppLocalize\Localization\TimeZones\TimeZoneCollection;
use AppLocalize\Localization\TimeZones\TimeZoneInterface;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper\FileInfo;
use Mistralys\ChangelogParser\ChangelogParser;

class ReleaseBuilder
{
    public static function build() : void
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        ClassHelper::setCacheFolder(__DIR__.'/../../cache');

        self::generateCannedCountries();
        self::generateCannedCurrencies();
        self::generateCannedLocales();
        self::generateOverviewMarkdown();
        self::generateVersionFile();
    }

    private static function generateVersionFile() : void
    {
        self::logHeader('Generating version file');

        Localization::getVersionFile()
            ->putContents(ChangelogParser::parseMarkdownFile(__DIR__.'/../../changelog.md')
            ->requireLatestVersion()
            ->getVersionInfo()
            ->getTagVersion());

        self::logLine(' - Written to '.Localization::getVersionFile()->getBaseName());
        self::logLine(' - DONE.');
        self::logNL();
    }

    private static function logHeader(string $header) : void
    {
         self::logLine(str_repeat('-', 70));
         self::logLine(mb_strtoupper($header));
         self::logLine(str_repeat('-', 70));

    }

    private static function logLine(string $line) : void
    {
        echo $line.PHP_EOL;
    }

    private static function logNL() : void
    {
        echo PHP_EOL;
    }

    private const CODE_COUNTRY = <<<'PHP'
    public function %1$s() : %2$s
    {
        return ClassHelper::requireObjectInstanceOf(
            %2$s::class,
            $this->collection->getByID(%2$s::ISO_CODE)
        );
    }
PHP;

    private static function generateCannedCountries() : void
    {
        self::logHeader('Generating canned countries');

        $imports = array();
        $methods = array();
        foreach(CountryCollection::getInstance()->getAll() as $country)
        {
            $code = $country->getCode();
            self::logLine(' - '.$code);

            $imports[] = 'use '.get_class($country).';';
            $methods[$code] = sprintf(
                self::CODE_COUNTRY,
                strtolower($code),
                ClassHelper::getClassTypeName($country)
            );

            foreach($country->getAliases() as $alias) {
                $methods[$alias] = sprintf(
                    self::CODE_COUNTRY,
                    $alias,
                    ClassHelper::getClassTypeName($country)
                );
            }
        }

        self::writeClassTemplate(
            'CannedCountriesTemplate.php.spf',
            __DIR__.'/../Localization/Countries/CannedCountries.php',
            $imports,
            $methods
        );

        self::logLine(' - DONE.');
        self::logNL();
    }

    private const CODE_CURRENCY = <<<'PHP'
    /**
     * Gets the %3$s currency.
     * 
     * This is used by the countries: %4$s.
     *
     * @return %2$s
     */
    public function %1$s() : %2$s
    {
        return ClassHelper::requireObjectInstanceOf(
            %2$s::class,
            $this->currencies->getByID(%2$s::ISO_CODE)
        );
    }
PHP;


    private static function generateCannedCurrencies() : void
    {
        self::logHeader('Generating canned currencies');

        $imports = array();
        $methods = array();
        foreach(CurrencyCollection::getInstance()->getAll() as $currency)
        {
            $iso = $currency->getISO();
            self::logLine(' - '.$iso);

            $imports[] = 'use '.get_class($currency).';';
            $methods[$iso] = sprintf(
                self::CODE_CURRENCY,
                strtolower($iso),
                ClassHelper::getClassTypeName($currency),
                $currency->getSingularInvariant(),
                ConvertHelper::implodeWithAnd(self::compileCountryCodes($currency->getCountries()), ', ', ' and ')
            );
        }

        self::writeClassTemplate(
            'CannedCurrenciesTemplate.php.spf',
            __DIR__.'/../Localization/Currencies/CannedCurrencies.php',
            $imports,
            $methods
        );

        self::logLine(' - DONE.');
        self::logNL();
    }

    private const CODE_LOCALE = <<<'PHP'
    /**
     * Gets the locale `%1$s` for "%3$s".
     * 
     * @return %2$s
     */
    public function %1$s() : %2$s
    {
        return ClassHelper::requireObjectInstanceOf(
            %2$s::class,
            $this->locales->getByID(%2$s::LOCALE_NAME)
        );
    }
PHP;


    private static function generateCannedLocales() : void
    {
        self::logHeader('Generating canned locales');

        $imports = array();
        $methods = array();
        foreach(LocalesCollection::getInstance()->getAll() as $locale)
        {
            $name = $locale->getName();

            self::logLine(' - '.$name);

            $imports[] = 'use '.get_class($locale).';';
            $methods[$name] = sprintf(
                self::CODE_LOCALE,
                $name,
                $name,
                $locale->getLabelInvariant()
            );
        }

        self::writeClassTemplate(
            'CannedLocalesTemplate.php.spf',
            __DIR__.'/../Localization/Locales/CannedLocales.php',
            $imports,
            $methods
        );

        self::logLine(' - DONE.');
        self::logNL();
    }

    /**
     * @param string $templateFile
     * @param string $outputFile
     * @param array<int,string> $imports Sorted.
     * @param array<string,string> $methods Sorted by key.
     * @return void
     */
    private static function writeClassTemplate(string $templateFile, string $outputFile, array $imports, array $methods) : void
    {
        sort($imports);
        ksort($methods);

        $placeholders = array(
            '{IMPORTS}' => implode("\n", $imports),
            '{METHODS}' => implode("\n\n", $methods),
        );

        $code = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            FileInfo::factory(__DIR__.'/Templates/'.$templateFile)->getContents()
        );

        file_put_contents($outputFile, $code);

        self::logLine(' - Written to '.basename($outputFile));
    }

    private static function generateOverviewMarkdown() : void
    {
        self::logHeader('Generating markdown documentation');

        $lines = array();
        $lines[] = '# Supported countries and locales';
        $lines[] = '';
        $lines[] = '## Countries';
        $lines[] = '';

        foreach(CountryCollection::getInstance()->getAll() as $country)
        {
            $aliases = $country->getAliases();

            if(!empty($aliases)) {
                $lines[] = sprintf(
                    '- `%s` %s (aka %s)',
                    $country->getCode(),
                    $country->getLabel(),
                    '`'.implode('`, `', $aliases).'`'
                );
            } else {
                $lines[] = sprintf(
                    '- `%s` %s',
                    $country->getCode(),
                    $country->getLabel()
                );
            }
        }

        $lines[] = '';
        $lines[] = '## Locales';
        $lines[] = '';

        foreach(LocalesCollection::getInstance()->getAll() as $locale) {
            $lines[] = sprintf(
                '- `%s` %s',
                $locale->getName(),
                $locale->getLabel()
            );
        }

        $lines[] = '';
        $lines[] = '## Currencies';
        $lines[] = '';

        foreach (CurrencyCollection::getInstance()->getAll() as $currency)
        {
            $symbol = $currency->getSymbol();

            if (empty($symbol)) {
                $symbol = $currency->getPlural();
            }

            $lines[] = sprintf(
                '- `%s` %s (%s)',
                $currency->getISO(),
                $currency->getPlural(),
                $symbol
            );
        }

        $lines[] = '';
        $lines[] = '## Time Zones';
        $lines[] = '';

        $zones = TimeZoneCollection::getInstance()->getAll();
        usort($zones, static function(TimeZoneInterface $a, TimeZoneInterface $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        foreach($zones as $zone) {
            $lines[] = sprintf(
                '- %s - `%s`',
                $zone->getLabel(),
                $zone->getID()
            );
        }

        $lines[] = '';

        $outputFile = __DIR__.'/../../docs/overview.md';

        file_put_contents($outputFile, implode("\n", $lines));

        self::logLine(' - Written to '.basename($outputFile));
        self::logLine(' - DONE.');
        self::logNL();
    }

    /**
     * @param CountryInterface[] $countries
     * @return string[]
     */
    private static function compileCountryCodes(array $countries) : array
    {
        $result = array();
        foreach($countries as $country) {
            $result[] = strtoupper($country->getCode());
        }

        return $result;
    }
}
