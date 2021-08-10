<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class de_CH extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('German (Switzerland)');
    }
}
