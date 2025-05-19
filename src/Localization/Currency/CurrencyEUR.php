<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currency;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Country\CountryFR;
use AppLocalize\Localization_Currency_EUR;
use function AppLocalize\t;

/**
 * Currency: Euro (EUR)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyEUR extends Localization_Currency_EUR
{
    public const ISO_CODE = 'EUR';

    public function getSingular() : string
    {
        return t('Euro');
    }

    public function getSymbol() : string
    {
        return '€';
    }

    public function getPlural() : string
    {
        return t('Euros');
    }

    public function isSymbolOnFront() : bool
    {
        return false;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        if($country instanceof CountryFR) {
            return '- {amount} {symbol}';
        }

        return '-{amount} {symbol}';
    }

    public function getISO() : string
    {
        return self::ISO_CODE;
    }
}
