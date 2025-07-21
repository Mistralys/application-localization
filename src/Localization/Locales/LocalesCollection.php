<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Locales;

use AppLocalize\Localization;
use AppLocalize\Localization\Locales\CannedLocales;
use AppLocalize\Localization\Locale\en_US;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * @method LocaleInterface[] getAll()
 * @method LocaleInterface getDefault()
 */
class LocalesCollection extends BaseClassLoaderCollection
{
    private static ?self $instance = null;

    /**
     * @var array<string, string>
     */
    private array $aliases = array();

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
        $locale = ClassHelper::requireObjectInstanceOf(
            LocaleInterface::class,
            new $class()
        );

        foreach($locale->getAliases() as $alias) {
            $this->aliases[$alias] = $locale->getID();
        }

        return $locale;
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

    public function idExists(string $id): bool
    {
        $this->initItems();

        return isset($this->aliases[$id]) || parent::idExists($id);
    }

    public function getByName(string $name) : LocaleInterface
    {
        return $this->getByID($name);
    }

    public function nameExists(string $name): bool
    {
        return $this->idExists($name);
    }

    /**
     * @param string $id
     * @return LocaleInterface
     */
    public function getByID(string $id): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            LocaleInterface::class,
            parent::getByID($this->filterName($id))
        );
    }

    public function filterName(string $name) : string
    {
        $this->initItems();

        return $this->aliases[$name] ?? $name;
    }

    private ?CannedLocales $canned = null;

    /**
     * Choose a locale from a list of known locales.
     * @return CannedLocales
     */
    public function choose() : CannedLocales
    {
        if(!isset($this->canned)) {
            $this->canned = new CannedLocales();
        }

        return $this->canned;
    }
}
