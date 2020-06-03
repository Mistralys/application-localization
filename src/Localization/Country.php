<?php
/**
 * File containing the {@link Localization_Country} class.
 * @package Localization
 * @subpackage Countries
 * @see Localization_Country
 */

namespace AppLocalize;

/**
 * Individual country representation for handling country-
 * related data like currencies and locales.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Localization_Country implements Localization_CountryInterface
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
