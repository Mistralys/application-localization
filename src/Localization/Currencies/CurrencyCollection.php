<?php
/**
 * @package Localization
 * @subpackage Currencies
 */

declare(strict_types=1);

namespace AppLocalize\Localization\Currencies;

use AppLocalize\Localization\Currency\CurrencyUSD;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\Collections\CollectionException;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Collection of all available currencies.
 *
 * @package Localization
 * @subpackage Currencies
 *
 * @method CurrencyInterface getByID(string $id)
 * @method CurrencyInterface getDefault()
 * @method CurrencyInterface[] getAll()
 */
class CurrencyCollection extends BaseClassLoaderCollection
{
    private static ?CurrencyCollection $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): CurrencyCollection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return CurrencyUSD::ISO_CODE;
    }

    /**
     * @param string $iso Three-letter ISO code of the currency, e.g. `EUR`. Case-insensitive.
     * @return CurrencyInterface
     * @throws CollectionException
     */
    public function getByISO(string $iso) : CurrencyInterface
    {
        return $this->getByID(strtoupper($iso));
    }

    /**
     * Checks whether the target ISO code is known.
     * @param string $iso Three-letter ISO code of the currency, e.g. `USD`. Case-insensitive.
     * @return bool
     */
    public function isoExists(string $iso) : bool
    {
        return $this->idExists(strtoupper($iso));
    }

    private ?CannedCurrencies $canned = null;

    public function choose() : CannedCurrencies
    {
        if(!isset($this->canned)) {
            $this->canned = new CannedCurrencies();
        }

        return $this->canned;
    }

    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            CurrencyInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return CurrencyInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../Currency');
    }
}
