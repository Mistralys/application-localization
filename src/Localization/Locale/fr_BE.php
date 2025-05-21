<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class fr_BE extends BaseLocale
{
    public const LOCALE_NAME = 'fr_BE';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('French')->add('('.t('Belgium').')');
    }

    public function getLabelInvariant() : string
    {
        return 'French (Belgium)';
    }
}
