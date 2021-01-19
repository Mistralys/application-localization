<?php
/**
 * File containing the {@link Localization_Currency_Number} class.
 * @package Localization
 * @subpackage Currencies
 * @see Localization_Currency_Number
 */

namespace AppLocalize;

/**
 * Number container for currency price notations, used to
 * provide a simple API to work with currency-specific
 * price notations.
 *
 * @package Localization
 * @subpackage Currencies
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @see Localization_Currency::tryParseNumber()
 */
class Localization_Currency_Number
{
    protected $number;

    protected $decimals = null;

    protected $float;

    public function __construct($number, $decimals = null)
    {
        $this->number = $number;
        $this->decimals = $decimals;
    }

    /**
     * Gets the number as a float, e.g. 100.25
     * @return number
     */
    public function getFloat()
    {
        if ($this->decimals) {
            return floatval($this->number . '.' . $this->decimals);
        }

        return $this->number;
    }

    /**
     * Returns the number without decimals (positive integer). e.g. 100
     * @return number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Returns the decimals of the number, if any.
     * @return number|NULL
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * Counts the amount of decimals in the number.
     * @return number
     */
    public function countDecimals()
    {
        if ($this->decimals === null) {
            return 0;
        }

        return strlen($this->decimals);
    }
}