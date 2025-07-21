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
 * Locale for German (Germany).
 *
 * @package Localization
 * @subpackage Locales
 */
class de_DE extends BaseLocale
{
    public const LOCALE_NAME = 'de_DE';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('German')->add('('.t('Germany').')');
    }

    public function getLabelInvariant() : string
    {
        return 'German (Germany)';
    }
}
