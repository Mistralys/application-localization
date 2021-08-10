<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class fr_FR extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('French');
    }
}
