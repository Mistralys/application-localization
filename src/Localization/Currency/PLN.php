<?php

namespace AppLocalize;

class Localization_Currency_PLN extends Localization_Currency
{
    public function getSingular()
    {
        return t('złoty');
    }

    public function getSymbol()
    {
        return 'zł';
    }

    public function getPlural()
    {
        return t('złotys');
    }
    
    public function getISO()
    {
        return 'PLN';
    }

    public function isSymbolOnFront()
    {
        return false;
    }

    /**
     * NOTE: empty values are considered valid. Check
     * for empty values before using this method.
     *
     * @see Localization_Currency::isNumberValid()
     */
    public function isNumberValid($number)
    {
        if (empty($number)) {
            return true;
        }

        if (!is_numeric($number)) {
            return false;
        }

        return true;
    }

    public function getExamples($decimalPositions = 0)
    {
        $decimals = '25874125486589953255847851252585';
        $examples = array();
        $examples[] = '50';
        $examples[] = sprintf('50%1$s-', $this->decimalsSep);
        $examples[] = '1500';
        $examples[] = sprintf('1%1$s500', $this->thousandsSep);

        if ($decimalPositions > 0) {
            $examples[] = sprintf(
                '50%1$s%2$s',
                $this->decimalsSep,
                substr($decimals, 0, $decimalPositions)
            );
            $examples[] = sprintf(
                '1%1$s500%2$s%3$s',
                $this->thousandsSep,
                $this->decimalsSep,
                substr($decimals, 0, $decimalPositions)
            );
        }

        return $examples;
    }
}