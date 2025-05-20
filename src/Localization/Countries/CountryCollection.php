<?php
/**
 * @package Localization
 * @subpackage Countries
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Countries;

use AppLocalize\Localization;
use AppLocalize\Localization\Country\CountryUS;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\Collections\CollectionException;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Country collection that gives access to all available
 * countries and their data.
 *
 * @package Localization
 * @subpackage Countries
 *
 * @method BaseCountry getByID(string $id)
 * @method BaseCountry getDefault()
 * @method BaseCountry[] getAll()
 */
class CountryCollection extends BaseClassLoaderCollection
{
    private static ?CountryCollection $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): CountryCollection
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

    /**
     * @param string $iso Two-letter ISO code of the country, e.g. `de`. Case-insensitive.
     * @return CountryInterface
     * @throws CollectionException
     */
    public function getByISO(string $iso) : CountryInterface
    {
        return $this->getByID(strtolower($iso));
    }

    /**
     * Checks whether the target ISO code is known.
     * @param string $iso Two-letter ISO code of the country, e.g. `de`. Case-insensitive.
     * @return bool
     */
    public function isoExists(string $iso) : bool
    {
        return $this->idExists(strtolower($iso));
    }

    public function getDefaultID(): string
    {
        return CountryUS::ISO_CODE;
    }

    private ?CannedCountries $canned = null;

    public function choose() : CannedCountries
    {
        if(!isset($this->canned)) {
            $this->canned = new CannedCountries();
        }

        return $this->canned;
    }

    /**
     * @param string $class
     * @return CountryInterface
     * @throws BaseClassHelperException
     */
    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            CountryInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return CountryInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__ . '/../Country')->requireExists();
    }
}
