<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Currencies;

use AppLocalize\Localization\Currencies\CurrencyNumberInfo;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see CurrencyNumberInfo}, covering the BUG-2 fix
 * (decimal precision loss when decimals have leading zeros).
 */
class CurrencyNumberInfoTests extends TestCase
{
    // -------------------------------------------------------------------------
    // getString() tests
    // -------------------------------------------------------------------------

    /**
     * Primary regression test for BUG-2: '05' decimals must not become '5'.
     */
    public function test_getString_preservesLeadingZeroDecimals() : void
    {
        $info = new CurrencyNumberInfo(1445, '05');
        $this->assertSame('1445.05', $info->getString());
    }

    public function test_getString_zeroDecimals() : void
    {
        $info = new CurrencyNumberInfo(100, '0');
        $this->assertSame('100.0', $info->getString());
    }

    public function test_getString_leadingZeroOnly() : void
    {
        $info = new CurrencyNumberInfo(0, '50');
        $this->assertSame('0.50', $info->getString());
    }

    // -------------------------------------------------------------------------
    // getFloat() tests
    // -------------------------------------------------------------------------

    public function test_getFloat() : void
    {
        $info = new CurrencyNumberInfo(1445, '05');
        $this->assertEqualsWithDelta(1445.05, $info->getFloat(), PHP_FLOAT_EPSILON);
    }

    // -------------------------------------------------------------------------
    // countDecimals() tests
    // -------------------------------------------------------------------------

    public function test_countDecimals_leadingZero() : void
    {
        $info = new CurrencyNumberInfo(100, '05');
        $this->assertSame(2, $info->countDecimals());
    }

    public function test_countDecimals_noDecimals() : void
    {
        $info = new CurrencyNumberInfo(100, '0');
        $this->assertSame(0, $info->countDecimals());
    }

    public function test_countDecimals_threeDigits() : void
    {
        $info = new CurrencyNumberInfo(100, '500');
        $this->assertSame(3, $info->countDecimals());
    }

    // -------------------------------------------------------------------------
    // isNegative() tests
    // -------------------------------------------------------------------------

    public function test_isNegative_negative() : void
    {
        $info = new CurrencyNumberInfo(-50, '0');
        $this->assertTrue($info->isNegative());
    }

    public function test_isNegative_positive() : void
    {
        $info = new CurrencyNumberInfo(50, '0');
        $this->assertFalse($info->isNegative());
    }

    public function test_isNegative_zero() : void
    {
        $info = new CurrencyNumberInfo(0, '0');
        $this->assertFalse($info->isNegative());
    }
}
