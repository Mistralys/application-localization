<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization_Currency_CAD;
use AppLocalize\Localization_Currency_CHF;
use AppLocalize\Localization_Currency_EUR;
use AppLocalize\Localization_Currency_GBP;
use AppLocalize\Localization_Currency_MXN;
use AppLocalize\Localization_Currency_PLN;
use AppLocalize\Localization_Currency_RON;
use AppLocalize\Localization_Currency_USD;
use AppUtils\ClassHelper;

/**
 * Canned list of known currencies for easy access.
 *
 * Use {@see CurrencyCollection::choose()} to access this.
 *
 * @package Localization
 * @subpackage Currencies
 */
class CannedCurrencies
{
    private CurrencyCollection $currencies;

    public function __construct()
    {
        $this->currencies = CurrencyCollection::getInstance();
    }

    public function cad() : Localization_Currency_CAD
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_CAD::class,
            $this->currencies->getByID(Localization_Currency_CAD::ISO_CODE)
        );
    }

    public function chf() : Localization_Currency_CHF
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_CHF::class,
            $this->currencies->getByID(Localization_Currency_CHF::ISO_CODE)
        );
    }

    public function eur() : Localization_Currency_EUR
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_EUR::class,
            $this->currencies->getByID(Localization_Currency_EUR::ISO_CODE)
        );
    }

    public function gbp() : Localization_Currency_GBP
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_GBP::class,
            $this->currencies->getByID(Localization_Currency_GBP::ISO_CODE)
        );
    }

    public function mxn() : Localization_Currency_MXN
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_MXN::class,
            $this->currencies->getByID(Localization_Currency_MXN::ISO_CODE)
        );
    }

    public function pln() : Localization_Currency_PLN
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_PLN::class,
            $this->currencies->getByID(Localization_Currency_PLN::ISO_CODE)
        );
    }

    public function ron() : Localization_Currency_RON
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_RON::class,
            $this->currencies->getByID(Localization_Currency_RON::ISO_CODE)
        );
    }

    public function usd() : Localization_Currency_USD
    {
        return ClassHelper::requireObjectInstanceOf(
            Localization_Currency_USD::class,
            $this->currencies->getByID(Localization_Currency_USD::ISO_CODE)
        );
    }
}