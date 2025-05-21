<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Translator;

use AppLocalize\Localization\LocalizationException;
use PHPUnit\Framework\TestCase;
use function AppLocalize\t;
use function AppLocalize\tex;

final class TranslationTests extends TestCase
{
    public function test_translateFunction() : void
    {
        $this->assertSame('English', t('English'));
    }

    public function test_translateWithPlaceholders() : void
    {
        $this->assertSame('Hello John', t('Hello %1$s', 'John'));
    }

    public function test_translateWithContext() : void
    {
        $this->assertSame('English', tex('English', 'Some context given here'));
    }

    public function test_missingPlaceholders() : void
    {
        $this->expectExceptionCode(LocalizationException::ERROR_INCORRECTLY_TRANSLATED_STRING);

        t('Missing %s placeholder');
    }
}
