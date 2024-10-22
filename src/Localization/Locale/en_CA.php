<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class en_CA extends Localization_Locale
{
    public const LOCALE_NAME = 'en_CA';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('English (Canada)');
    }
}
