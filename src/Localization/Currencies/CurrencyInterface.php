<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for currency objects.
 * A base implementation is provided by {@see BaseCurrency}.
 *
 * @package Localization
 * @subpackage Currencies
 */
interface CurrencyInterface
    extends
    StringableInterface,
    StringPrimaryRecordInterface
{
    /**
     * The currency ISO code, e.g. {@see CurrencyEUR::ISO_CODE}.
     */
    public function getID() : string;

    /**
     * @return CountryInterface[]
     */
    public function getCountries() : array;

    /**
     * The singular label of the currency, e.g. "Dollar", "Pound"
     * @return string
     */
    public function getSingular() : string;

    /**
     * The plural label of the currency, e.g. "Dollars", "Pounds"
     * @return string
     */
    public function getPlural() : string;

    /**
     * The currency symbol, e.g. "$", "€"
     * @return string
     */
    public function getSymbol() : string;

    public function getPreferredSymbol() : string;

    public function getISO() : string;

    /**
     * Gets the formatting template for the currency.
     * @param CountryInterface|NULL $country Some currencies have different formatting based on the country.
     *      Add the country to guarantee the matching formatting, otherwise the default formatting is returned.
     * @return string
     */
    public function getStructuralTemplate(?CountryInterface $country=null) : string;

    /**
     * Whether the symbol is typically shown at the beginning of the amount.
     * @return bool
     */
    public function isSymbolOnFront() : bool;

    /**
     * Whether the currency name is preferred over the symbol.
     * @return bool
     */
    public function isNamePreferred() : bool;
}
