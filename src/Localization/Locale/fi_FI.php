<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class fi_FI extends BaseLocale
{
    public const LOCALE_NAME = 'fi_FI';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('Finnish (Finland)');
    }

    public function getLabelInvariant() : string
    {
        return 'Finnish (Finland)';
    }
}
