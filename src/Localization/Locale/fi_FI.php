<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class fi_FI extends BaseLocale
{
    public const LOCALE_NAME = 'fi_FI';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Finnish')->add('('.t('Finland').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Finnish (Finland)';
    }
}
