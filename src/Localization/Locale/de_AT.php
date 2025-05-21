<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class de_AT extends BaseLocale
{
    public const LOCALE_NAME = 'de_AT';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('German')->add('('.t('Austria').')');
    }

    public function getLabelInvariant() : string
    {
        return 'German (Austria)';
    }
}
