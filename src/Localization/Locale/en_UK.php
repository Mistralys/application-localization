<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class en_UK extends Localization_Locale
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
}
