<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locale;

use AppLocalize\Localization\Locales\BaseLocale;
use function AppLocalize\t;
use function AppUtils\sb;

class es_MX extends BaseLocale
{
    public const LOCALE_NAME = 'es_MX';

    public function getName() : string
    {
        return self::LOCALE_NAME;
    }

    public function getLabel() : string
    {
        return (string)sb()->t('Spanish')->add('('.t('Mexico').')');
    }

    public function getLabelInvariant() : string
    {
        return 'Spanish (Mexico)';
    }
}
