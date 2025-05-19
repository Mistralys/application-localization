<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currency;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization_Currency_MXN;
use function AppLocalize\t;

/**
 * Currency: Mexican Peso (MXN)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyMXN extends Localization_Currency_MXN
{
    public const ISO_CODE = 'MXN';

    public function getSingular() : string
    {
        return t('Mexican peso');
    }

    public function getSymbol() : string
    {
        return '$';
    }

    public function getPlural() : string
    {
        return t('Mexican peso');
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
