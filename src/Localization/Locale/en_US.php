<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class en_US extends BaseLocale
{
    public const LOCALE_NAME = 'en_US';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('English')->add('('.t('America').')');
    }

    public function getLabelInvariant() : string
    {
        return 'English (America)';
    }
}
