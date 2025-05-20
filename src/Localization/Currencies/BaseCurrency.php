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
