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
 * Currency: Leu (RON)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyRON extends BaseCurrency
{
    public const ISO_CODE = 'RON';

    public function getSingular() : string
    {
        return t('Leu');
    }

    public function getSymbol() : string
    {
        return '';
    }

    public function getPlural() : string
    {
        return t('Lei');
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
        return false;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{amount} {symbol}';
    }
}
