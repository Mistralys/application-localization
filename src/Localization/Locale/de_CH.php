<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class de_CH extends BaseLocale
{
    public const LOCALE_NAME = 'de_CH';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('German')->add('('.t('Switzerland').')');
    }

    public function getLabelInvariant() : string
    {
        return 'German (Switzerland)';
    }
}
