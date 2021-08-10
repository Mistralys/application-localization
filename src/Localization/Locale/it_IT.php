<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class it_IT extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('Italian');
    }
}
