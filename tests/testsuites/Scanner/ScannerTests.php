<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Scanner;

use AppLocalize\Localization;
use AppLocalize\Localization\Scanner\LocalizationScanner;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end tests for {@see LocalizationScanner}.
 *
 * The bootstrap already calls {@see Localization::configure()} and registers the
 * example/sources folder under the 'main' source, so {@see Localization::createScanner()}
 * is available without any additional setup here.
 */
class ScannerTests extends TestCase
{
    // -------------------------------------------------------------------------
    // Scan tests
    // -------------------------------------------------------------------------

    /**
     * After scanning the registered example sources, the collection must
     * contain at least one translatable string hash.
     */
    public function test_scan_findsExpectedHashes() : void
    {
        $scanner = Localization::createScanner();
        $scanner->scan();

        $collection = $scanner->getCollection();

        $this->assertGreaterThan(
            0,
            $collection->countHashes(),
            'The scanner must find at least one translatable string in the example sources.'
        );
    }

    /**
     * After scanning, the number of unique source files discovered must be > 0.
     */
    public function test_scan_fileCount() : void
    {
        $scanner = Localization::createScanner();
        $scanner->scan();

        $this->assertGreaterThan(
            0,
            $scanner->getCollection()->countFiles(),
            'The scanner must report at least one source file after scanning.'
        );
    }

    /**
     * After a successful scan, the execution time must be positive.
     */
    public function test_scan_executionTime() : void
    {
        $scanner = Localization::createScanner();
        $scanner->scan();

        $this->assertGreaterThan(
            0.0,
            $scanner->getExecutionTime(),
            'Execution time must be greater than zero after scanning.'
        );
    }

    // -------------------------------------------------------------------------
    // Load tests
    // -------------------------------------------------------------------------

    /**
     * After scanning (which saves the result to storage.json), a fresh scanner
     * loaded from that file must report the same number of hashes.
     */
    public function test_load_fromStorageJson() : void
    {
        // First scan to produce the storage file.
        $scanner = Localization::createScanner();
        $scanner->scan();
        $scannedCount = $scanner->getCollection()->countHashes();

        // Validate that at least something was found before asserting equality.
        $this->assertGreaterThan(0, $scannedCount, 'Pre-condition: scan must find at least one hash.');

        // Build a fresh scanner pointing to the same storage file and load it.
        $storageFile = TESTS_ROOT . '/storage/storage.json';
        $freshScanner = new LocalizationScanner($storageFile);
        $freshScanner->load();

        $this->assertSame(
            $scannedCount,
            $freshScanner->getCollection()->countHashes(),
            'Loaded collection must contain the same number of hashes as the scanned collection.'
        );
    }

    /**
     * Calling load() with a non-existent storage file must be a no-op
     * (no exception, empty collection).
     */
    public function test_load_missingFile() : void
    {
        $nonExistentPath = TESTS_ROOT . '/storage/does-not-exist-' . uniqid() . '.json';

        $scanner = new LocalizationScanner($nonExistentPath);

        // Must not throw.
        $scanner->load();

        $this->assertSame(
            0,
            $scanner->getCollection()->countHashes(),
            'Loading a missing file must produce an empty collection.'
        );
    }
}
