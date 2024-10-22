<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for application country classes.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface CountryInterface extends StringPrimaryRecordInterface
{
    public function getNumberThousandsSeparator() : string;
    
    public function getNumberDecimalsSeparator() : string;

    /**
     * @return string
     * @deprecated Use {@see self::getCurrencyISO()} instead.
     */
    public function getCurrencyID() : string;

    /**
     * Retrieves the ISO code of the currency used by this country,
     * e.g. {@see Localization_Currency_EUR::ISO_CODE}.
     *
     * @return string
     */
    public function getCurrencyISO() : string;
    
    /**
     * Retrieves the country's currency object, which
     * is different from a regular currency in that it
     * retains a reference to the country: {@see CountryCurrency::getCountry()}.
     *
     * @return CountryCurrency
     */
    public function getCurrency() : CountryCurrency;
    
    /**
     * Human-readable label for the locale, e.g. "United States"
     * @return string
     */
    public function getLabel() : string;
    
    /**
     * Returns the country's two-letter ISO code, e.g. "uk", "en", "de".
     * Always lowercase.
     *
     * @return string
     */
    public function getCode() : string;
}
