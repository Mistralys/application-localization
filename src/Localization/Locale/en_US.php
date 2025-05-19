<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class en_US extends BaseLocale
{
    public const LOCALE_NAME = 'en_US';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('English (US)');
    }
}
