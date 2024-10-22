<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

/**
 * Number container for currency price notations, used to
 * provide a simple API to work with currency-specific
 * price notations.
 *
 * @package Localization
 * @subpackage Currencies
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 * @see BaseCurrency::tryParseNumber()
 */
class CurrencyNumberInfo
{
    protected int $number;
    protected int $decimals;

    /**
     * @var float
     */
    protected $float;

    /**
     * @param int $number
     * @param int $decimals
     */
    public function __construct(int $number, int $decimals = 0)
    {
        $this->number = $number;
        $this->decimals = $decimals;
    }

    public function isNegative() : bool
    {
        return $this->number < 0;
    }

    /**
     * Gets the number as a float, e.g. 100.25
     * @return float
     */
    public function getFloat() : float
    {
        return (float)($this->getString());
    }

    /**
     * Returns the number without decimals (positive integer). e.g. 100
     * @return int
     */
    public function getNumber() : int
    {
        return $this->number;
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
     * Counts the number of decimals in the number.
     * @return int
     */
    public function countDecimals() : int
    {
        if ($this->decimals === 0) {
            return 0;
        }

        return strlen((string)$this->decimals);
    }

    public function getString() : string
    {
        return $this->number . '.' . $this->decimals;
    }
}
