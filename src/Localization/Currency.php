<?php
/**
 * File containing the {@link Localization_Currency} class.
 * @package Localization
 * @subpackage Currencies
 * @see Localization_Currency
 */

declare(strict_types=1);

namespace AppLocalize;

use function AppUtils\parseVariable;

/**
 * Individual currency representation.
 *
 * @package Localization
 * @subpackage Currencies
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Localization_Currency
{
    const ERROR_INVALID_COUNTRY_CLASS = 91201;

    /**
     * @var Localization_Country
     */
    protected $country;

    /**
     * @var string
     */
    protected $id;

    /**
     * List of supported currencies
     * @var array
     */
    protected static $knownCurrencies = array();

    /**
     * @var array<string,Localization_Currency>
     */
    protected static $instances = array();

    /**
     * @var string
     */
    protected $regex = '';

    /**
     * @var string|NULL
     */
    protected $cachedRegex;


    /**
     * Creates a new currency object for the specified country.
     * Note that there is no need to create currency objects
     * yourself, a country automatically creates one for itself
     * so the easiest way is to go through the country/locale
     * object.
     *
     * @param string $id
     * @param Localization_Country $country
     * @return Localization_Currency
     * @throws Localization_Exception
     */
    public static function create(string $id, Localization_Country $country) : Localization_Currency
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        $className = Localization_Currency::class.'_' . $id;

        $country = new $className($country);

        if($country instanceof Localization_Currency)
        {
            return $country;
        }

        throw new Localization_Exception(
            'Invalid currency class',
            sprintf(
                'Currency [%s] is not a currency class instance: [%s]',
                Localization_Currency::class,
                parseVariable($country)->enableType()->toString()
            ),
            self::ERROR_INVALID_COUNTRY_CLASS
        );
    }

    /**
     * @var string
     */
    protected $decimalsSep;

    /**
     * @var string
     */
    protected $thousandsSep;

    /**
     * @param Localization_Country $country
     * @throws Localization_Exception
     */
    protected function __construct(Localization_Country $country)
    {
        $this->country = $country;
        
        $this->id = str_replace('AppLocalize\Localization_Currency_', '', get_class($this));

        if ($this->country->getCurrencyID() != $this->id) {
            throw new Localization_Exception(
                'Country and currency mismatch',
                sprintf(
                    'Tried creating the currency %1$s for the country %2$s, but that country uses %3$s.',
                    $this->id,
                    $this->country->getCode(),
                    $this->country->getCurrencyID()
                )
            );
        }

        $this->decimalsSep = $this->country->getNumberDecimalsSeparator();
        $this->thousandsSep = $this->country->getNumberThousandsSeparator();
    }

    /**
     * Checks whether the specified currency name is known
     * (supported by the application)
     *
     * @param string $currencyName
     * @return boolean
     */
    public static function isCurrencyKnown(string $currencyName) : bool
    {
        return file_exists(__DIR__ . '/Currency/' . $currencyName . '.php');
    }

    /**
     * @return Localization_Country
     */
    public function getCountry() : Localization_Country
    {
        return $this->country;
    }

    /**
     * The currency name, e.g. "dollar", "euro"
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * The singular label of the currency, e.g. "Dollar", "Pound"
     * @return string
     */
    public abstract function getSingular() : string;

    /**
     * The plural label of the currency, e.g. "Dollars", "Pounds"
     * @return string
     */
    public abstract function getPlural() : string;

    /**
     * The currency symbol, e.g. "$", "€"
     * @return string
     */
    public abstract function getSymbol() : string;

    /**
     * Checks if the specified number string is a valid
     * numeric notation for this currency.
     * @param string|number $number
     * @return bool
     */
    public abstract function isNumberValid($number) : bool;
    
    public abstract function getISO() : string;

    /**
     * A hint that is used in forms to tell the user how the
     * numeric notation of the currency should be entered.
     * @return string|NULL
     */
    public function getFormatHint() : ?string
    {
        return null;
    }

    /**
     * Returns examples of the currency's numeric notation, as
     * an indexed array with examples which are used in forms
     * as input help for users.
     *
     * The optional parameter sets how many decimal positions
     * should be included in the examples.
     *
     * @param int $decimalPositions
     * @return array
     */
    public abstract function getExamples(int $decimalPositions = 0) : array;

    /**
     * Parses the specified number and returns a currency number
     * object which can be used to access the number and its parts
     * independently of the human-readable notation used.
     *
     * @param string|number|NULL $number
     * @return Localization_Currency_Number|FALSE
     *
     * @see Localization_Currency::parseNumber()
     */
    public function tryParseNumber($number)
    {
        if (!is_integer($number)) {
            if (!is_string($number) || strlen($number) < 1) {
                return false;
            }
        }

        $normalized = strval($number);

        // number uses the full notation
        if (strstr($normalized, $this->thousandsSep) && strstr($normalized, $this->decimalsSep)) {
            $normalized = str_replace($this->thousandsSep, '', $normalized);
            $normalized = str_replace($this->decimalsSep, '.', $normalized);
        } else {
            $normalized = str_replace(',', '.', $normalized);
        }

        $parts = explode('.', $normalized);

        if (count($parts) > 1) 
        {
            $decimals = array_pop($parts);
            $thousands = implode('', $parts);
            if ($decimals == '-') {
                $decimals = 0;
            }
        } 
        else 
        {
            $decimals = 0;
            $thousands = implode('', $parts);
        }

        return new Localization_Currency_Number(intval($thousands), intval($decimals));
    }

    /**
     * @param string|number $number
     * @return Localization_Currency_Number
     * @throws Localization_Exception
     *
     * @see Localization_Currency::tryParseNumber()
     */
    public function parseNumber($number) : Localization_Currency_Number
    {
        $parsed = $this->tryParseNumber($number);

        if ($parsed instanceof Localization_Currency_Number) {
            return $parsed;
        }

        throw new Localization_Exception(
            'Could not parse number',
            sprintf(
                'The number [%1$s] did not yield a currency number object.',
                $number
            )
        );
    }

    public abstract function isSymbolOnFront() : bool;

    /**
     * Returns the singular of the currency label.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSingular();
    }

    /**
     * @return string
     * @throws Localization_Exception
     */
    protected function getRegex() : string
    {
        if (!isset($this->regex)) {
            throw new Localization_Exception(
                'No regex defined',
                sprintf(
                    'To use this method, set the regex class property for currency %1$s.',
                    $this->getID()
                )
            );
        }

        if (!isset($this->cachedRegex)) {
            $this->cachedRegex = sprintf(
                $this->regex,
                $this->thousandsSep,
                $this->decimalsSep
            );
        }

        return $this->cachedRegex;
    }

    /**
     * @param number $number
     * @param int $decimalPositions
     * @return string
     */
    public function formatNumber($number, int $decimalPositions = 2) : string
    {
        return number_format(
            floatval($number),
            $decimalPositions,
            $this->decimalsSep,
            $this->thousandsSep
        );
    }

    public function getThousandsSeparator() : string
    {
        return $this->country->getNumberThousandsSeparator();
    }

    public function getDecimalsSeparator() : string
    {
        return $this->country->getNumberDecimalsSeparator();
    }

    /**
     * @param number|string|NULL $number
     * @param int $decimalPositions
     * @param bool $addSymbol
     * @return string
     * @throws Localization_Exception
     */
    public function makeReadable($number, int $decimalPositions = 2, bool $addSymbol=true) : string
    {
        if ($number === null || $number === '') {
            return '';
        }

        $parsed = $this->parseNumber($number);

        $number = $this->formatNumber($parsed->getFloat(), $decimalPositions);

        if(!$addSymbol) {
            return $number;
        }
        
        if ($this->isSymbolOnFront()) {
            return $this->getSymbol() . ' ' . $number;
        }

        return $number . ' ' . $this->getSymbol();
    }
}
