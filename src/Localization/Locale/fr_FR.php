<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class fr_FR extends BaseLocale
{
    public const LOCALE_NAME = 'fr_FR';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('French (France)');
    }

    public function getLabelInvariant() : string
    {
        return 'French (France)';
    }
}
