<?php

declare(strict_types=1);

namespace AppLocalize;

class Localization_Currency_EUR extends Localization_Currency
{
    /**
     * @var string
     */
    protected $regex = '/\A([0-9%1$s]+)\z|([0-9%1$s]+),-\z|([0-9%1$s]+)[%2$s]([0-9]+)\z/s';

    public function getSingular() : string
    {
        return t('Euro');
    }

    public function getSymbol() : string
    {
        return '€';
    }

    public function getPlural() : string
    {
        return t('Euros');
    }

    public function isSymbolOnFront() : bool
    {
        return false;
    }
    
    public function getISO() : string
    {
        return 'EUR';
    }

    /**
     * Checks numeric notation, to allow these:
     *
     * 50
     * 50,25
     * 50,-
     * 1.500,25
     * 1500,25
     *
     * NOTE: empty values are considered valid. Check
     * for empty values before using this method.
     *
     * @see Localization_Currency::isNumberValid()
     */
    public function isNumberValid($number) : bool
    {
        if (empty($number)) {
            return true;
        }

        if (!is_numeric($number)) {
            return false;
        }

        return true;
    }

    public function getExamples(int $decimalPositions = 0) : array
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
