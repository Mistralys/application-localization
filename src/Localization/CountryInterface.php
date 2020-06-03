<?php

namespace AppLocalize;

/**
 * Interface for application country classes.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface Localization_CountryInterface
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
