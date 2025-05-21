<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class ro_RO extends BaseLocale
{
    public const LOCALE_NAME = 'ro_RO';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Romanian')->add('('.t('Romania').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Romanian (Romania)';
    }
}
