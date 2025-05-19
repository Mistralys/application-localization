<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Currency\CurrencyUSD;
use AppLocalize\Localization;
use AppLocalize\Localization\Countries\CountryInterface;

/**
 * Individual currency representation.
 *
 * @package Localization
 * @subpackage Currencies
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseCurrency implements CurrencyInterface
{
    /**
     * Creates a new currency object.
     *
     * @param string $id
     * @return CurrencyInterface
     * @deprecated Use the collection {@see Localization::createCurrencies()} instead.
     */
    public static function create(string $id) : CurrencyInterface
    {
        return Localization::createCurrencies()->getByID($id);
    }

    public function __construct()
    {
    }

    public function getID(): string
    {
        return $this->getISO();
    }

    /**
     * Gets all countries that this currency is used in.
     * @return CountryInterface[]
     */
    public function getCountries() : array
    {
        $countries = array();
        $iso = $this->getISO();

        foreach(Localization::createCountries()->getAll() as $country) {
            if($country->getCurrencyISO() === $iso) {
                $countries[] = $country;
            }
        }

        return $countries;
    }

    /**
     * Checks whether the specified currency name is known
     * (supported by the application)
     *
     * @param string $currencyName Currency code, e.g. {@see \AppLocalize\Localization\Currency\CurrencyUSD::ISO_CODE}.
     * @return boolean
     * @deprecated Use {@see CurrencyCollection::idExists()} instead.
     */
    public static function isCurrencyKnown(string $currencyName) : bool
    {
        return Localization::createCurrencies()->idExists($currencyName);
    }

    /**
     * Returns the singular of the currency label.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSingular();
    }

    public function getPreferredSymbol(): string
    {
        if($this->isNamePreferred()) {
            return $this->getISO();
        }

        return $this->getSymbol();
    }
}
