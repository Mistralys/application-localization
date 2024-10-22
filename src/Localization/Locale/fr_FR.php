<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class fr_FR extends Localization_Locale
{
    public const LOCALE_NAME = 'fr_FR';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('French');
    }
}
