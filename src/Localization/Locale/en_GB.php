<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class en_GB extends BaseLocale
{
    public const LOCALE_NAME = 'en_GB';
    public const LOCALE_ALIAS_UK = 'en_UK';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getAliases(): array
    {
        return array(
            self::LOCALE_ALIAS_UK
        );
    }

    public function getLabel() : string
    {
        return (string)sb()->t('English')->add('('.t('Great Britain').')');
    }

    public function getLabelInvariant() : string
    {
        return 'English (Great Britain)';
    }
}
