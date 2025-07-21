<?php

declare(strict_types=1);

namespace AppLocalize\Tools;

use AppLocalize\Localization;
use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\CurrencyCollection;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Mistralys\ChangelogParser\ChangelogParser;

class ReleaseBuilder
{
    public static function build() : void
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        self::generateCannedCountries();
        self::generateCannedCurrencies();
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
            self::logLine(' - '.$country->getCode());

            $imports[] = 'use '.get_class($country).';';
            $methods[] = sprintf(
                self::CODE_COUNTRY,
                strtolower($country->getCode()),
                ClassHelper::getClassTypeName($country)
            );

            foreach($country->getAliases() as $alias) {
                $methods[] = sprintf(
                    self::CODE_COUNTRY,
                    $alias,
                    ClassHelper::getClassTypeName($country)
                );
            }
        }

        sort($imports);
        sort($methods);

        $placeholders = array(
            '{IMPORTS}' => implode("\n", $imports),
            '{METHODS}' => implode("\n\n", $methods),
        );

        $code = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents(__DIR__.'/Templates/CannedCountriesTemplate.php.spf')
        );

        $outputFile = __DIR__.'/../Localization/Countries/CannedCountries.php';

        file_put_contents($outputFile, $code);

        self::logLine(' - Written to '.basename($outputFile));
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
            self::logLine(' - '.$currency->getISO());

            $imports[] = 'use '.get_class($currency).';';
            $methods[] = sprintf(
                self::CODE_CURRENCY,
                strtolower($currency->getISO()),
                ClassHelper::getClassTypeName($currency),
                $currency->getSingularInvariant(),
                ConvertHelper::implodeWithAnd(self::compileCountryCodes($currency->getCountries()), ', ', ' and ')
            );
        }

        $placeholders = array(
            '{IMPORTS}' => implode("\n", $imports),
            '{METHODS}' => implode("\n\n", $methods),
        );

        $code = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents(__DIR__.'/Templates/CannedCurrenciesTemplate.php.spf')
        );

        $outputFile = __DIR__.'/../Localization/Currencies/CannedCurrencies.php';

        file_put_contents($outputFile, $code);

        self::logLine(' - Written to '.basename($outputFile));
        self::logLine(' - DONE.');
        self::logNL();
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
