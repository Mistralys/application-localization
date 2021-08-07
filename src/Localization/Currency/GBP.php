<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Currency_GBP extends Localization_Currency
{
    /**
     * @var string
     */
    protected $regex = '/\A([0-9%1$s]+)\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';

    public function getSingular() : string
    {
        return t('Pound');
    }

    public function getSymbol() : string
    {
        return '£';
    }

    public function getPlural() : string
    {
        return t('Pounds');
    }
    
    public function getISO() : string
    {
        return 'GBP';
    }

    public function isSymbolOnFront() : bool
    {
        return true;
    }

    public function isNumberValid($number) : bool
    {
        if (empty($number)) {
            return true;
        }

        return preg_match($this->getRegex(), strval($number)) !== false;
    }

    public function getExamples(int $decimalPositions = 0) : array
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
