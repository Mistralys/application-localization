<?php

namespace AppLocalize\Locale;

use AppLocalize\Localization_Locale;
use function AppLocalize\t;

class pl_PL extends Localization_Locale
{
    public const LOCALE_NAME = 'pl_PL';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return t('Polish');
    }
}
