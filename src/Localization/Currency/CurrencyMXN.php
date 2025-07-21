<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currency;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\BaseCurrency;
use function AppLocalize\t;

/**
 * Currency: Mexican Peso (MXN)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyMXN extends BaseCurrency
{
    public const ISO_CODE = 'MXN';

    public function getSingular() : string
    {
        return t('Mexican peso');
    }

    public function getSingularInvariant() : string
    {
        return 'Mexican peso';
    }

    public function getSymbol() : string
    {
        return '$';
    }

    public function getPlural() : string
    {
        return t('Mexican peso');
    }

    public function getPluralInvariant() : string
    {
        return 'Mexican pesos';
    }
    
    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function isSymbolOnFront() : bool
    {
        return false;
    }

    public function isNamePreferred() : bool
    {
        return true;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '{symbol} -{amount}';
    }
}
