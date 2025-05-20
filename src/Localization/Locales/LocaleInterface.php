<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppLocalize\Localization\Countries\BaseCountry;
use AppLocalize\Localization\Countries\CountryInterface;
use AppLocalize\Localization\Currencies\CurrencyInterface;
use AppLocalize\Localization\LocalizationException;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface LocaleInterface extends StringPrimaryRecordInterface
{
    /**
     * Retrieves the two-letter language code of the locale.
     *
     * @return string Language code, e.g. "en", "de"
     */
    public function getLanguageCode() : string;

    /**
     * Checks whether the specified locale name is known
     * (supported by the application).
     *
     * @param string $localeName
     * @return boolean
     */
    public static function isLocaleKnown(string $localeName) : bool;

    /**
     * Returns the locale name, e.g. "en_US"
     * @return string
     */
    public function getName() : string;

    /**
     * Retrieves the two-letter ISO country code of
     * the locale.
     *
     * @return string Lowercase code, e.g. "uk"
     */
    public function getCountryCode() : string;

    /**
     * Checks if this locale is the builtin application locale
     * (the one in which application strings are written).
     *
     * @return boolean
     * @see Localization::BUILTIN_LOCALE_NAME
     */
    public function isNative() : bool;

    /**
     * Returns the localized label for the locale, e.g.
     * "Deutsch" for German when the locale is "de_DE".
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Returns the invariant label for the locale, e.g. "German"
     *
     * @return string
     */
    public function getLabelInvariant() : string;

    /**
     * Retrieves the country object for this locale
     *
     * @return CountryInterface
     */
    public function getCountry() : CountryInterface;

    /**
     * Retrieves the currency object for this locale
     *
     * @return CurrencyInterface
     *
     * @throws LocalizationException
     * @see Localization::ERROR_COUNTRY_NOT_FOUND
     */
    public function getCurrency() : CurrencyInterface;

    /**
     * List of locale aliases that can be used to refer to this locale.
     * @return string[]
     */
    public function getAliases() : array;
}
