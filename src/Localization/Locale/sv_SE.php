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
 * Locale for Swedish (Sweden).
 *
 * @package Localization
 * @subpackage Locales
 */
class sv_SE extends BaseLocale
{
    public const LOCALE_NAME = 'sv_SE';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Swedish')->add('('.t('Sweden').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Swedish (Sweden)';
    }
}
