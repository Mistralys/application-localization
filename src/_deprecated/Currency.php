<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize;

use AppLocalize\Localization\Currencies\BaseCurrency;
use AppLocalize\Localization\Currencies\CurrencyInterface;

/**
 * @package Localization
 * @subpackage Currencies
 *
 * @deprecated Use {@see CurrencyInterface} or {@see BaseCurrency} instead.
 */
abstract class Localization_Currency extends BaseCurrency
{

}
