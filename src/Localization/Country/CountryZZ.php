<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_ZZ;
use AppLocalize\Localization\Currency\CurrencyUSD;
use function AppLocalize\t;

/**
 * Country class with the definitions for Worldwide (Country-Independent).
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryZZ extends Localization_Country_ZZ
{
    public const ISO_CODE = 'zz';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getNumberThousandsSeparator() : string
    {
        return '.';
    }
    
    public function getNumberDecimalsSeparator() : string
    {
        return ',';
    }
    
    public function getLabel() : string
    {
        return t('Country-independent');
    }
    
    public function getCurrencyISO() : string
    {
        return CurrencyUSD::ISO_CODE;
    }
}
