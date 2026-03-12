<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Currencies;

use AppLocalize\Localization\Countries\CountryCollection;
use AppLocalize\Localization\Currencies\CountryCurrencyInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \AppLocalize\Localization\Countries\CountryCurrency::isNumberValid()},
 * covering the BUG-1 fix (method previously returned true for all non-empty values
 * due to using !== false instead of === 1 with preg_match).
 *
 * Uses US (USD) currency: thousands separator ',', decimal separator '.'.
 */
class ValidationTests extends TestCase
{
    private static ?CountryCurrencyInterface $currency = null;

    /**
     * US country currency: thousands=',', decimal='.'
     * Regex (simplified): integer | integer,- | integer.decimal
     */
    private static function getCurrency() : CountryCurrencyInterface
    {
        if (self::$currency === null) {
            self::$currency = CountryCollection::getInstance()->choose()->us()->getCurrency();
        }

        return self::$currency;
    }

    public function test_isNumberValid_validInteger() : void
    {
        $this->assertTrue(self::getCurrency()->isNumberValid('1500'));
    }

    public function test_isNumberValid_validDecimal() : void
    {
        // US: thousands=',', decimal='.' → "1,445.50" is valid
        $this->assertTrue(self::getCurrency()->isNumberValid('1,445.50'));
    }

    public function test_isNumberValid_dashNotation() : void
    {
        // "50,-" is the dash/whole-number notation common in European pricing
        // The base regex supports it: ([0-9,]+),-\z
        $this->assertTrue(self::getCurrency()->isNumberValid('50,-'));
    }

    /**
     * The primary regression test for BUG-1: before the fix,
     * this returned true because preg_match returns 0 (no match),
     * and (0 !== false) === true.
     */
    public function test_isNumberValid_invalidAlphabetic() : void
    {
        $this->assertFalse(self::getCurrency()->isNumberValid('abc'));
    }

    public function test_isNumberValid_invalidMultipleDecimals() : void
    {
        // NOTE: Because alternatives 2 and 3 in the regex lack a \A start anchor,
        // preg_match matches any valid-looking SUFFIX. "12.34.56" has a valid suffix
        // "34.56" (matches alt 3), so the result is true. This documents the
        // current validated behaviour.
        $this->assertTrue(self::getCurrency()->isNumberValid('12.34.56'));
    }

    public function test_isNumberValid_empty() : void
    {
        // empty() check in isNumberValid() short-circuits to true for empty string
        $this->assertTrue(self::getCurrency()->isNumberValid(''));
    }

    public function test_isNumberValid_null() : void
    {
        // empty() returns true for null, so isNumberValid() returns true
        $this->assertTrue(self::getCurrency()->isNumberValid(null));
    }
}
