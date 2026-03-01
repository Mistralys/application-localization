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
    protected string $decimals;

    /**
     * @param int $number
     * @param int|string $decimals Decimal digits as string to preserve leading zeros (e.g. '05').
     *                             Passing an int is accepted for backward compatibility but cannot
     *                             represent leading zeros.
     */
    public function __construct(int $number, int|string $decimals = 0)
    {
        $this->number = $number;
        $this->decimals = (string)$decimals;
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
     * Returns the decimals of the number as a string, preserving leading zeros (e.g. '05').
     * @return string
     */
    public function getDecimals() : string
    {
        return $this->decimals;
    }

    /**
     * Counts the number of decimal digits. Returns 0 when decimals are '0'.
     * @return int
     */
    public function countDecimals() : int
    {
        if($this->decimals === '0') {
            return 0;
        }

        return strlen($this->decimals);
    }

    public function getString() : string
    {
        return $this->number . '.' . $this->decimals;
    }
}
