<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppLocalize\Localization;
use AppLocalize\Localization\Locale\en_US;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * @method LocaleInterface[] getAll()
 * @method LocaleInterface getByID(string $id)
 * @method LocaleInterface getDefault()
 */
class LocalesCollection extends BaseClassLoaderCollection
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function getClassRepository() : ClassRepositoryManager
    {
        return Localization::getClassRepository();
    }

    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            LocaleInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return LocaleInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../Locale')->requireExists();
    }

    public function getDefaultID(): string
    {
        return en_US::LOCALE_NAME;
    }
}
