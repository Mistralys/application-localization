<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class de_CH extends BaseLocale
{
    public const LOCALE_NAME = 'de_CH';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('German (Switzerland)');
    }

    public function getLabelInvariant() : string
    {
        return 'German (Switzerland)';
    }
}
