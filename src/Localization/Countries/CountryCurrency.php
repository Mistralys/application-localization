<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization\Currencies\CurrencyInterface;
use AppLocalize\Localization\Currencies\CurrencyNumberInfo;
use AppLocalize\Localization\LocalizationException;

/**
 * @package Localization
 * @subpackage Currencies
 */
class CountryCurrency implements CurrencyInterface
{
    private CurrencyInterface $currency;
    private CountryInterface $country;

    public function __construct(CurrencyInterface $currency, CountryInterface $country)
    {
        $this->currency = $currency;
        $this->country = $country;
    }

    public function getCountry(): CountryInterface
    {
        return $this->country;
    }

    public function getID(): string
    {
        return $this->currency->getID();
    }

    public function getCountries(): array
    {
        return $this->currency->getCountries();
    }

    public function getSingular(): string
    {
        return $this->currency->getSingular();
    }

    public function getPlural(): string
    {
        return $this->currency->getPlural();
    }

    public function getSymbol(): string
    {
        return $this->currency->getSymbol();
    }

    public function getPreferredSymbol() : string
    {
        return $this->currency->getPreferredSymbol();
    }

    public function isNamePreferred(): bool
    {
        return $this->currency->isNamePreferred();
    }

    public function getStructuralTemplate(?CountryInterface $country=null): string
    {
        return $this->currency->getStructuralTemplate($this->country);
    }

    /**
     * Checks if the specified number string is a valid
     * numeric notation for this currency.
     *
     * @param string|int|float $number
     * @return bool
     * @throws LocalizationException
     */
    public function isNumberValid($number) : bool
    {
        if (empty($number)) {
            return true;
        }

        return preg_match($this->getRegex(), (string)$number) !== false;
    }

    protected string $regex = '/\A([0-9%1$s]+)\z|([0-9%1$s]+),-\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';
    protected string $regexGBP = '/\A([0-9%1$s]+)\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';
    protected string $regexUSD = '/\A([0-9%1$s]+)\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';
    private ?string $cachedRegex = null;

    /**
     * @return string
     * @throws \AppLocalize\Localization\LocalizationException
     */
    protected function getRegex() : string
    {
        if (!isset($this->regex)) {
            throw new LocalizationException(
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
                $this->getThousandsSeparator(),
                $this->getDecimalsSeparator()
            );
        }

        return $this->cachedRegex;
    }

    public function getISO(): string
    {
        return $this->currency->getISO();
    }

    public function getFormatHint(): ?string
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
     * @return string[]
     */
    public function getExamples(int $decimalPositions = 0) : array
    {
        $dSep = $this->getDecimalsSeparator();
        $tSep = $this->getThousandsSeparator();

        $decimals = '25874125486589953255847851252585';
        $examples = array();
        $examples[] = '50';
        $examples[] = sprintf('50%1$s-', $dSep);
        $examples[] = '1500';
        $examples[] = sprintf('1%1$s500', $tSep);

        if ($decimalPositions > 0) {
            $examples[] = sprintf(
                '50%1$s%2$s',
                $dSep,
                substr($decimals, 0, $decimalPositions)
            );
            $examples[] = sprintf(
                '1%1$s500%2$s%3$s',
                $tSep,
                $dSep,
                substr($decimals, 0, $decimalPositions)
            );
        }

        return $examples;
    }

    public function tryParseNumber($number)
    {
        $parts = explode('.', $this->normalizeNumber($number));

        if (count($parts) > 1)
        {
            $decimals = array_pop($parts);
            $thousands = implode('', $parts);
            if ($decimals === '-') {
                $decimals = 0;
            }
        }
        else
        {
            $decimals = 0;
            $thousands = implode('', $parts);
        }

        return new CurrencyNumberInfo((int)$thousands, (int)$decimals);
    }

    /**
     * @param int|float|string|NULL $number
     * @return string
     */
    public function normalizeNumber($number) : string
    {
        if($number === '' || $number === null) {
            return '';
        }

        $normalized = str_replace(' ', '', (string)$number);
        $dSep = $this->getDecimalsSeparator();
        $tSep = $this->getThousandsSeparator();

        // Handle the case where both classical separators are used,
        // independently of the country's specific separators.
        if(strpos($normalized, '.') !== false && strpos($normalized, ',') !== false) {
            $normalized = str_replace(array('.', ','), '.', $normalized);
            $parts = explode('.', $normalized);
            $decimals = array_pop($parts);
            $normalized = implode('', $parts).$dSep.$decimals;
        }

        // number uses the full notation
        if (strpos($normalized, $tSep) !== false && strpos($normalized, $dSep) !== false) {
            return str_replace(array($tSep, $dSep), array('', '.'), $normalized);
        }

        return str_replace(',', '.', $normalized);
    }

    public function parseNumber($number) : CurrencyNumberInfo
    {
        $parsed = $this->tryParseNumber($number);

        if ($parsed instanceof CurrencyNumberInfo) {
            return $parsed;
        }

        throw new LocalizationException(
            'Could not parse number',
            sprintf(
                'The number [%1$s] did not yield a currency number object.',
                $number
            )
        );
    }

    public function isSymbolOnFront(): bool
    {
        return $this->currency->isSymbolOnFront();
    }

    public function formatNumber($number, int $decimalPositions = 2) : string
    {
        return number_format(
            (float)$number,
            $decimalPositions,
            $this->getDecimalsSeparator(),
            $this->getThousandsSeparator()
        );
    }

    public function getThousandsSeparator(): string
    {
        return $this->country->getNumberThousandsSeparator();
    }

    public function getDecimalsSeparator(): string
    {
        return $this->country->getNumberDecimalsSeparator();
    }

    public function makeReadable($number, int $decimalPositions = 2, bool $addSymbol=true) : string
    {
        if ($number === null || $number === '') {
            return '';
        }

        $parsed = $this->parseNumber($number);

        $number = $this->formatNumber(
            str_replace('-', '', $parsed->getString()),
            $decimalPositions
        );

        if(!$addSymbol) {
            return $number;
        }

        $sign = '';
        if($parsed->isNegative()) {
            $sign = '-';
        }

        $replace = array(
            '{symbol}' => $this->getPreferredSymbol(),
            '{amount}' => $number,
            '-' => $sign
        );

        return trim(str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStructuralTemplate()
        ));
    }

    public function __toString()
    {
        return (string)$this->currency;
    }
}
