<?php
/**
 * File containing the {@link Localization_Currency} class.
 * @package Localization
 * @subpackage Currencies
 * @see Localization_Currency
 */

namespace AppLocalize;

/**
 * Individual currency representation.
 *
 * @package Localization
 * @subpackage Currencies
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Localization_Currency
{
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

    protected static $instances = array();

    /**
     * Creates a new currency object for the specified country.
     * Note that there is no need to create currency objects
     * youself, a country automatically creates one for itself
     * so the easiest way is to go through the country/locale
     * object.
     *
     * @param Localization_Country $country
     * @return Localization_Currency
     */
    public static function create($id, Localization_Country $country)
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        $className = '\AppLocalize\Localization_Currency_' . $id;

        return new $className($country);
    }

    protected $decimalsSep;

    protected $thousandsSep;

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
    public static function isCurrencyKnown($currencyName)
    {
        return file_exists(__DIR__ . '/Currency/' . $currencyName . '.php');
    }

    /**
     * @return Localization_Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * The currency name, e.g. "dollar", "euro"
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * The singular label of the currency, e.g. "Dollar", "Pound"
     * @return string
     */
    public abstract function getSingular();

    /**
     * The plural label of the currency, e.g. "Dollars", "Pounds"
     * @return string
     */
    public abstract function getPlural();

    /**
     * The currency symbol, e.g. "$", "€"
     * @return string
     */
    public abstract function getSymbol();

    /**
     * Checks if the specified number string is a valid
     * numeric notation for this currency.
     * @param string|number $number
     * @return bool
     */
    public abstract function isNumberValid($number);
    
    public abstract function getISO();

    /**
     * A hint that is used in forms to tell the user how the
     * numeric notation of the currency should be entered.
     * @return string|NULL
     */
    public function getFormatHint()
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
    public abstract function getExamples($decimalPositions = 0);

    /**
     * Parses the specified number and returns a currency number
     * object which can be used to access the number and its parts
     * independently of the human readable notation used.
     *
     * @param string|number $number
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

        $normalized = $number;

        // number uses the full notation
        if (strstr($number, $this->thousandsSep) && strstr($number, $this->decimalsSep)) {
            $normalized = str_replace($this->thousandsSep, '', $number);
            $normalized = str_replace($this->decimalsSep, '.', $normalized);
        } else {
            $normalized = str_replace(',', '.', $number);
        }

        $parts = explode('.', $normalized);

        if (count($parts) > 1) 
        {
            $decimals = array_pop($parts);
            $thousands = floatval(implode('', $parts));
            if ($decimals == '-') {
                $decimals = 0;
            }
        } 
        else 
        {
            $decimals = null;
            $thousands = floatval(implode('', $parts));
        }

        return new Localization_Currency_Number($thousands, $decimals);
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

    public abstract function isSymbolOnFront();

    /**
     * Returns the singular of the currency label.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSingular();
    }

    protected $regex;

    protected $cachedRegex;

    protected function getRegex()
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

    public function formatNumber($number, $decimalPositions = 2)
    {
        return number_format(
            $number,
            $decimalPositions,
            $this->decimalsSep,
            $this->thousandsSep
        );
    }

    public function getThousandsSeparator()
    {
        return $this->country->getNumberThousandsSeparator();
    }

    public function getDecimalsSeparator()
    {
        return $this->country->getNumberDecimalsSeparator();
    }

    public function makeReadable($number, $decimalPositions = 2, $addSymbol=true)
    {
        if ($number === null || $number === '') {
            return null;
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