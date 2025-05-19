<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;

class es_ES extends BaseLocale
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

    public function getLabelInvariant() : string
    {
        return 'Spanish';
    }
}
