<?php
/**
 * File containing the {@link Localization_Currency_Number} class.
 * @package Localization
 * @subpackage Currencies
 * @see Localization_Currency_Number
 */

declare(strict_types=1);

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
    /**
     * @var number|string
     */
    protected $number;

    /**
     * @var int
     */
    protected $decimals = 0;

    /**
     * @var float
     */
    protected $float;

    /**
     * @param string|number $number
     * @param int $decimals
     */
    public function __construct($number, int $decimals = 0)
    {
        $this->number = floatval($number);
        $this->decimals = $decimals;
    }

    /**
     * Gets the number as a float, e.g. 100.25
     * @return float
     */
    public function getFloat() : float
    {
        return floatval($this->number . '.' . $this->decimals);
    }

    /**
     * Returns the number without decimals (positive integer). e.g. 100
     * @return int
     */
    public function getNumber() : int
    {
        return intval($this->number);
    }

    /**
     * Returns the decimals of the number, if any.
     * @return int
     */
    public function getDecimals() : int
    {
        return $this->decimals;
    }

    /**
     * Counts the amount of decimals in the number.
     * @return int
     */
    public function countDecimals() : int
    {
        if ($this->decimals === 0) {
            return 0;
        }

        return strlen(strval($this->decimals));
    }
}
