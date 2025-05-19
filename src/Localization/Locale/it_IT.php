<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class it_IT extends BaseLocale
{
    public const LOCALE_NAME = 'it_IT';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('Italian');
    }

    public function getLabelInvariant() : string
    {
        return 'Italian';
    }
}
