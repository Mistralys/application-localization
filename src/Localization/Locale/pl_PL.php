<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class pl_PL extends Localization_Locale
{
    public function getLabel() : string
    {
        return t('Polish');
    }
}
