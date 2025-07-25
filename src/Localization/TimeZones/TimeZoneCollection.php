<?php
/**
 * @package Localization
 * @subpackage TimeZones
 */

declare(strict_types=1);

namespace AppLocalize\Localization\TimeZones;

use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Collection of time zones.
 *
 * @package Localization
 * @subpackage TimeZones
 *
 * @method TimeZoneInterface[] getAll()
 * @method TimeZoneInterface getByID(string $id)
 * @method TimeZoneInterface getDefault()
 */
class TimeZoneCollection extends BaseClassLoaderCollection
{
    private static ?TimeZoneCollection $instance = null;

    public static function getInstance(): TimeZoneCollection
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            TimeZoneInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return TimeZoneInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../TimeZone');
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
