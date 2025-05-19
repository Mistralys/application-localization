<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class en_UK extends BaseLocale
{
    public const LOCALE_NAME = 'en_UK';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('English (UK)');
    }

    public function getLabelInvariant() : string
    {
        return 'English (UK)';
    }
}
