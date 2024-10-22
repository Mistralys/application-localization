<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class es_ES extends Localization_Locale
{
    public const LOCALE_NAME = 'es_ES';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('Spanish');
    }
}
