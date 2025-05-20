<?php

declare(strict_types=1);

namespace AppLocalize\Tools;

use AppLocalize\Localization\Countries\CountryCollection;
use AppUtils\ClassHelper;

class ReleaseBuilder
{
    public static function build() : void
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        self::generateCannedCountries();
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
        $imports = array();
        $methods = array();
        foreach(CountryCollection::getInstance()->getAll() as $country) {
            $imports[] = 'use '.get_class($country).';';
            $methods[] = sprintf(
                self::CODE_COUNTRY,
                strtolower($country->getCode()),
                ClassHelper::getClassTypeName($country)
            );
        }

        $placeholders = array(
            '{IMPORTS}' => implode("\n", $imports),
            '{METHODS}' => implode("\n\n", $methods),
        );

        $code = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            file_get_contents(__DIR__.'/Templates/CannedCountriesTemplate.php.spf')
        );

        file_put_contents(__DIR__.'/../Localization/Countries/CannedCountries.php', $code);
    }
}
