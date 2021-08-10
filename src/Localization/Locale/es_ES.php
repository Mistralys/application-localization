<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class es_ES extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('Spanish');
    }
}
