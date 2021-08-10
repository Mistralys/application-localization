<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class en_US extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('English (US)');
    }
}
