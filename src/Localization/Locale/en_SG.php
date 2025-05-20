<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class en_SG extends BaseLocale
{
    public const LOCALE_NAME = 'en_SG';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('English (Singapore)');
    }

    public function getLabelInvariant() : string
    {
        return 'English (Singapore)';
    }
}
