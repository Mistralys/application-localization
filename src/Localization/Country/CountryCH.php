<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Country;

use AppLocalize\Localization_Country_CH;
use AppLocalize\Localization_Currency_EUR;
use function AppLocalize\t;

/**
 * Country class with the definitions for Switzerland.
 *
 * @package Localization
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @link http://www.mistralys.com
 */
class CountryCH extends Localization_Country_CH
{
    public const ISO_CODE = 'ch';

    public function getCode(): string
    {
        return self::ISO_CODE;
    }

    public function getLabel() : string
    {
        return t('Switzerland');
    }

    public function getCurrencyISO() : string
    {
        return Localization_Currency_EUR::ISO_CODE;
    }

    public function getNumberThousandsSeparator(): string
    {
        return "'";
    }
}
