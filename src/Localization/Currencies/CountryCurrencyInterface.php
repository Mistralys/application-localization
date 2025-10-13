<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Countries\CountryCurrency;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\LocalizationException;

/**
 * Interface for country-specific currency information. This has some additional
 * methods for formatting and validating numbers in the currency's notation,
 * according to the country's conventions. The implementation is provided
 * by {@see CountryCurrency}.
 *
 * @package Localization
 * @subpackage Currencies
 * @see CountryCurrency
 */
interface CountryCurrencyInterface extends CurrencyInterface
{
    public function getCountry(): CountryInterface;

    /**
     * Checks if the specified number string is a valid
     * numeric notation for this currency.
     *
     * @param string|int|float $number
     * @return bool
     * @throws LocalizationException
     */
    public function isNumberValid($number) : bool;

    public function getFormatHint(): ?string;

    /**
     * Returns examples of the currency's numeric notation, as
     * an indexed array with examples which are used in forms
     * as input help for users.
     *
     * The optional parameter sets how many decimal positions
     * should be included in the examples.
     *
     * @param int $decimalPositions
     * @return string[]
     */
    public function getExamples(int $decimalPositions = 0) : array;

    /**
     * @param string|int|float|CurrencyNumberInfo|NULL $number
     * @return CurrencyNumberInfo|NULL
     */
    public function tryParseNumber($number) : ?CurrencyNumberInfo;

    /**
     * @param string|int|float|CurrencyNumberInfo|NULL $number
     * @return CurrencyNumberInfo
     * @throws LocalizationException
     */
    public function parseNumber($number) : CurrencyNumberInfo;

    /**
     * @param int|float|string|CurrencyNumberInfo|NULL $number
     * @return string
     */
    public function normalizeNumber($number) : string;

    /**
     * @param string|int|float|CurrencyNumberInfo|NULL $number
     * @param int $decimalPositions
     * @return string
     */
    public function formatNumber($number, int $decimalPositions = 2) : string;

    public function getThousandsSeparator(): string;

    public function getDecimalsSeparator(): string;

    /**
     * @param string|int|float|CurrencyNumberInfo|NULL $number
     * @param int $decimalPositions
     * @param bool $addSymbol
     * @return string
     */
    public function makeReadable($number, int $decimalPositions = 2, bool $addSymbol=true) : string;
}
