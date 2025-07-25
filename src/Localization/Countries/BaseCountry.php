<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppLocalize\Localization\TimeZones\TimeZoneCollection;
use AppLocalize\Localization\TimeZones\TimeZoneInterface;

/**
 * Individual country representation for handling country-related
 * data like currencies and locales.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseCountry implements CountryInterface
{
    public function getAliases() : array
    {
        return array();
    }

    protected ?CountryCurrency $currency;

    public function getCurrency() : CountryCurrency
    {
        if(!isset($this->currency)) {
            $this->currency = new CountryCurrency(
                Localization::createCurrencies()->getByID($this->getCurrencyISO()),
                $this
            );
        }

        return $this->currency;
    }

    public function getID() : string
    {
        return $this->getCode();
    }

    public function getCurrencyID() : string
    {
        return $this->getCurrencyISO();
    }

    /**
     * Returns the human-readable locale label.
     * @return string
     * @see getLabel()
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    public function getMainLocale() : LocaleInterface
    {
        return LocalesCollection::getInstance()->getByID($this->getMainLocaleCode());
    }

    public function getTimeZone() : TimeZoneInterface
    {
        return TimeZoneCollection::getInstance()->getByID($this->getTimeZoneID());
    }
}
