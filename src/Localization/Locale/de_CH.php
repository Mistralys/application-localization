<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class de_CH extends Localization_Locale
{
    public const LOCALE_NAME = 'de_CH';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('German (Switzerland)');
    }
}
