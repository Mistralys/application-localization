<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class pl_PL extends BaseLocale
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

    public function getLabelInvariant() : string
    {
        return 'Polish';
    }
}
