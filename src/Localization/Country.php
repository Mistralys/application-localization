<?php
/**
 * File containing the {@link Localization_Country} class.
 * @package Application
 * @subpackage Localization
 * @see Localization_Country
 */

namespace AppLocalize;

/**
 * Individual country representation for handling country-
 * related data like currencies and locales.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
abstract class Localization_Country implements Localization_Country_Interface
{
    /**
     * Two-letter ISO country code
     * @var string
     */
    protected $code;

    /**
     * @var Localization_Currency
     */
    protected $currency;

    /**
     * Instantiates a country object.
     */
    public function __construct()
    {
        $this->currency = Localization_Currency::create($this->getCurrencyID(), $this);
        $this->code = strtolower(str_replace('AppLocalize\Localization_Country_', '', get_class($this)));
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Localization_Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the human readable locale label.
     * @return string
     * @see getLabel()
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}

/**
 * Interface for application country classes.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
interface Localization_Country_Interface
{
    public function getNumberThousandsSeparator() : string;

    public function getNumberDecimalsSeparator() : string;

    /**
     * Retrieves the ID of the currency used by this country, e.g. "euro" or "dollar".
     * Always lowercase.
     *
     * @return string
     */
    public function getCurrencyID();

    /**
     * Retrieves the country's currency object.
     * @return Localization_Currency
     */
    public function getCurrency();

    /**
     * Human readable label for the locale, e.g. "United States"
     * @return string
     */
    public function getLabel();

    /**
     * Returns the country's two-letter ISO code, e.g. "uk", "en", "de".
     * Always lowercase.
     *
     * @return string
     */
    public function getCode();
}