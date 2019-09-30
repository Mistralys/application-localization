<?php

namespace AppLocalize;

class Localization_Currency_GBP extends Localization_Currency
{
    protected $regex = '/\A([0-9%1$s]+)\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';

    public function getSingular()
    {
        return t('Pound');
    }

    public function getSymbol()
    {
        return '£';
    }

    public function getPlural()
    {
        return t('Pounds');
    }
    
    public function getISO()
    {
        return 'GBP';
    }

    public function isSymbolOnFront()
    {
        return true;
    }

    public function isNumberValid($number)
    {
        if (empty($number)) {
            return true;
        }

        return preg_match($this->getRegex(), $number);
    }

    public function getExamples($decimalPositions = 0)
    {
        $decimals = '25874125486589953255847851252585';
        $examples = array();
        $examples[] = '50';
        $examples[] = '1500';
        $examples[] = '1.500';

        if ($decimalPositions > 0) {
            $examples[] = '50,' . substr($decimals, 0, $decimalPositions);
            $examples[] = '1.500,' . substr($decimals, 0, $decimalPositions);
        }

        return $examples;
    }
}