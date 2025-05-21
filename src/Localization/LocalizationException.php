<?php

declare(strict_types=1);

namespace AppLocalize\Localization;

use AppUtils\BaseException;

class LocalizationException extends BaseException
{
    public const ERROR_NO_STORAGE_FILE_SET = 39003;
    public const ERROR_CONFIGURE_NOT_CALLED = 39004;
    public const ERROR_NO_SOURCES_ADDED = 39005;
    public const ERROR_NO_LOCALE_SELECTED_IN_NS = 39006;
    public const ERROR_NO_LOCALES_IN_NAMESPACE = 39007;
    public const ERROR_UNKNOWN_NAMESPACE = 39008;
    public const ERROR_UNKNOWN_LOCALE_IN_NS = 39009;
    public const ERROR_UNKNOWN_EVENT_NAME = 39010;
    public const ERROR_LOCALE_NOT_FOUND = 39011;
    public const ERROR_COUNTRY_NOT_FOUND = 39012;
    public const ERROR_INCORRECTLY_TRANSLATED_STRING = 39013;
}
