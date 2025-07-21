<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Currency\CurrencyCAD;
use AppLocalize\Localization\Currency\CurrencyCHF;
use AppLocalize\Localization\Currency\CurrencyEUR;
use AppLocalize\Localization\Currency\CurrencyGBP;
use AppLocalize\Localization\Currency\CurrencyMXN;
use AppLocalize\Localization\Currency\CurrencyPLN;
use AppLocalize\Localization\Currency\CurrencyRON;
use AppLocalize\Localization\Currency\CurrencySEK;
use AppLocalize\Localization\Currency\CurrencySGD;
use AppLocalize\Localization\Currency\CurrencyUSD;
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

    /**
     * Gets the Canadian Dollar currency.
     * 
     * This is used by the countries: CA.
     *
     * @return CurrencyCAD
     */
    public function cad() : CurrencyCAD
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyCAD::class,
            $this->currencies->getByID(CurrencyCAD::ISO_CODE)
        );
    }

    /**
     * Gets the Swiss Franc currency.
     * 
     * This is used by the countries: .
     *
     * @return CurrencyCHF
     */
    public function chf() : CurrencyCHF
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyCHF::class,
            $this->currencies->getByID(CurrencyCHF::ISO_CODE)
        );
    }

    /**
     * Gets the Euro currency.
     * 
     * This is used by the countries: AT, BE, CH, DE, ES, FI, FR, IE, IT and NL.
     *
     * @return CurrencyEUR
     */
    public function eur() : CurrencyEUR
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyEUR::class,
            $this->currencies->getByID(CurrencyEUR::ISO_CODE)
        );
    }

    /**
     * Gets the Pound currency.
     * 
     * This is used by the countries: GB.
     *
     * @return CurrencyGBP
     */
    public function gbp() : CurrencyGBP
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyGBP::class,
            $this->currencies->getByID(CurrencyGBP::ISO_CODE)
        );
    }

    /**
     * Gets the Mexican peso currency.
     * 
     * This is used by the countries: MX.
     *
     * @return CurrencyMXN
     */
    public function mxn() : CurrencyMXN
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyMXN::class,
            $this->currencies->getByID(CurrencyMXN::ISO_CODE)
        );
    }

    /**
     * Gets the złoty currency.
     * 
     * This is used by the countries: PL.
     *
     * @return CurrencyPLN
     */
    public function pln() : CurrencyPLN
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyPLN::class,
            $this->currencies->getByID(CurrencyPLN::ISO_CODE)
        );
    }

    /**
     * Gets the Leu currency.
     * 
     * This is used by the countries: RO.
     *
     * @return CurrencyRON
     */
    public function ron() : CurrencyRON
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyRON::class,
            $this->currencies->getByID(CurrencyRON::ISO_CODE)
        );
    }

    /**
     * Gets the Krona currency.
     * 
     * This is used by the countries: SE.
     *
     * @return CurrencySEK
     */
    public function sek() : CurrencySEK
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencySEK::class,
            $this->currencies->getByID(CurrencySEK::ISO_CODE)
        );
    }

    /**
     * Gets the Singapore Dollar currency.
     * 
     * This is used by the countries: SG.
     *
     * @return CurrencySGD
     */
    public function sgd() : CurrencySGD
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencySGD::class,
            $this->currencies->getByID(CurrencySGD::ISO_CODE)
        );
    }

    /**
     * Gets the Dollar currency.
     * 
     * This is used by the countries: US and ZZ.
     *
     * @return CurrencyUSD
     */
    public function usd() : CurrencyUSD
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyUSD::class,
            $this->currencies->getByID(CurrencyUSD::ISO_CODE)
        );
    }
}
