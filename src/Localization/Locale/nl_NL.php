<?php
/**
 * @package Localization
 * @subpackage Locales
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

/**
 * Locale for Dutch (Netherlands).
 *
 * @package Localization
 * @subpackage Locales
 */
class nl_NL extends BaseLocale
{
    public const LOCALE_NAME = 'nl_NL';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Dutch')->add('('.t('Netherlands').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Dutch (Netherlands)';
    }
}
