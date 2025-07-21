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
 * Currency: Pound Sterling (GBP)
 *
 * @package Localization
 * @subpackage Currencies
 */
class CurrencyGBP extends BaseCurrency
{
    public const ISO_CODE = 'GBP';

    public function getSingular() : string
    {
        return t('Pound');
    }

    public function getSingularInvariant() : string
    {
        return 'Pound';
    }

    public function getSymbol() : string
    {
        return '£';
    }

    public function getPlural() : string
    {
        return t('Pounds');
    }

    public function getPluralInvariant() : string
    {
        return 'Pounds';
    }
    
    public function getISO() : string
    {
        return self::ISO_CODE;
    }

    public function isSymbolOnFront() : bool
    {
        return true;
    }

    public function isNamePreferred() : bool
    {
        return false;
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return '-{symbol}{amount}';
    }
}
