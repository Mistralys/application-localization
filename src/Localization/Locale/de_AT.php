<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class de_AT extends BaseLocale
{
    public const LOCALE_NAME = 'de_AT';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('German (Austria)');
    }

    public function getLabelInvariant() : string
    {
        return 'German (Austria)';
    }
}
