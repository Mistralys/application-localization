<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization_Currency_CAD;
use AppLocalize\Localization_Currency_CHF;
use AppLocalize\Localization_Currency_EUR;
use AppLocalize\Localization_Currency_GBP;
use AppLocalize\Localization_Currency_MXN;
use AppLocalize\Localization_Currency_PLN;
use AppLocalize\Localization_Currency_RON;
use AppLocalize\Localization_Currency_USD;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\Collections\CollectionException;

/**
 * @package Localization
 * @subpackage Currencies
 *
 * @method CurrencyInterface getByID(string $id)
 * @method CurrencyInterface getDefault()
 * @method CurrencyInterface[] getAll()
 */
class CurrencyCollection extends BaseStringPrimaryCollection
{
    private static ?CurrencyCollection $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): CurrencyCollection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return Localization_Currency_USD::ISO_CODE;
    }

    protected function registerItems(): void
    {
        $this->registerItem(new Localization_Currency_CAD());
        $this->registerItem(new Localization_Currency_CHF());
        $this->registerItem(new Localization_Currency_EUR());
        $this->registerItem(new Localization_Currency_GBP());
        $this->registerItem(new Localization_Currency_MXN());
        $this->registerItem(new Localization_Currency_PLN());
        $this->registerItem(new Localization_Currency_RON());
        $this->registerItem(new Localization_Currency_USD());
    }

    /**
     * @param string $iso Three-letter ISO code of the currency, e.g. `EUR`. Case-insensitive.
     * @return CurrencyInterface
     * @throws CollectionException
     */
    public function getByISO(string $iso) : CurrencyInterface
    {
        return $this->getByID(strtoupper($iso));
    }

    /**
     * Checks whether the target ISO code is known.
     * @param string $iso Three-letter ISO code of the currency, e.g. `USD`. Case-insensitive.
     * @return bool
     */
    public function isoExists(string $iso) : bool
    {
        return $this->idExists(strtoupper($iso));
    }

    private ?CannedCurrencies $canned = null;

    public function choose() : CannedCurrencies
    {
        if(!isset($this->canned)) {
            $this->canned = new CannedCurrencies();
        }

        return $this->canned;
    }
}
