<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class it_IT extends BaseLocale
{
    public const LOCALE_NAME = 'it_IT';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Italian')->add('('.t('Italy').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Italian (Italy)';
    }
}
