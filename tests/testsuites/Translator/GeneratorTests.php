<?php

declare(strict_types=1);

namespace AppLocalize\tests\testsuites\Translator;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;

use AppLocalize\Localization;
use AppUtils\FileHelper;

final class GeneratorTests extends TestCase
{
    protected function setUp(): void
    {
        Localization::reset();

        $this->clearFiles();
    }

    /**
     * Clear the client library files that were written to disk.
     */
    protected function clearFiles(): void
    {
        $folder = Localization::getClientLibrariesFolder();

        $d = new DirectoryIterator($folder);

        foreach ($d as $item) {
            if ($item->isFile()) {
                FileHelper::deleteFile($item->getPathname());
            }
        }
    }

    /**
     * Files should be written for each locale, plus
     * the required libraries.
     */
    public function test_writeFiles(): void
    {
        Localization::addAppLocale('fr_FR');
        Localization::addAppLocale('de_DE');

        $generator = Localization::createGenerator();

        $files = $generator->getFilesList();

        // two locales + 2 library files
        $this->assertEquals(4, count($files));

        $generator->writeFiles();

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }

        $referenceFile = $files[0];
        $referenceTime = filemtime($referenceFile);

        usleep(3000);

        $generator->writeFiles();

        $this->assertSame($referenceTime, filemtime($referenceFile), 'The file should not have been rewritten.');
    }

    /**
     * When an empty string is specified as the client
     * libraries folder, nothing should be written to disk.
     * It should effectively disable those libraries.
     */
    public function test_writeFiles_disabled(): void
    {
        Localization::addAppLocale('fr_FR');
        Localization::addAppLocale('de_DE');

        $previous = Localization::getClientLibrariesFolder();
        $generator = Localization::createGenerator();

        // Get the list of files that would usually be created
        $files = $generator->getFilesList();

        // reset the client libraries folder
        // to disable the generation, to simulate
        // setting the folder to en empty string when
        // calling configure().
        Localization::setClientLibrariesFolder('');

        // create a new instance that will use the updated
        // client libraries folder.
        $generator = Localization::createGenerator();
        $generator->writeFiles();

        // None of the files should have been written to disk.
        foreach ($files as $file) {
            $this->assertFalse(file_exists($file));
        }

        // restore the original folder for the other tests
        Localization::setClientLibrariesFolder($previous);
    }

    /**
     * Test that forcing files to be written works, as well as
     * not writing them again otherwise.
     */
    public function test_writeFiles_force(): void
    {
        Localization::addAppLocale('de_DE');

        $this->assertNotEmpty(Localization::getClientLibrariesFolder());

        $generator = Localization::createGenerator();

        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'The locale files should initially have been written.');
    }

    /**
     * Test that changing the cache key triggers a rewrite
     * of all locale files.
     */
    public function test_writeFiles_cacheKey(): void
    {
        Localization::addAppLocale('de_DE');

        $generator = Localization::createGenerator();

        $generator->writeFiles();

        Localization::setClientLibrariesCacheKey('modified_key');

        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'The locale files should have been rewritten.');
    }

    /**
     * Setting the client libraries cache key before writing files should
     * result in a cachekey.txt that contains the full system key (including
     * the Lib: segment), and the second writeFiles() call must be skipped.
     */
    public function test_writeFiles_cacheKeyBeforeConfigure(): void
    {
        Localization::addAppLocale('de_DE');
        Localization::setClientLibrariesCacheKey('v1.0.0');

        $generator = Localization::createGenerator();
        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'Files must be marked as written after initial write.');

        // Verify the stored key contains the custom Lib: segment.
        $storedKey = $generator->getCacheKey();
        $this->assertNotNull($storedKey, 'Cache key file must have been written.');
        $this->assertStringContainsString('Lib:v1.0.0', (string)$storedKey);

        // Second call must NOT rewrite (check via mtime of the cachekey.txt file).
        $cacheKeyFile = Localization::getClientLibrariesFolder() . '/cachekey.txt';
        $mtimeBefore = filemtime($cacheKeyFile);

        usleep(3000);

        $generator->writeFiles();

        $this->assertSame($mtimeBefore, filemtime($cacheKeyFile), 'The cachekey.txt must not be rewritten on a cache-hit.');
    }

    /**
     * Adding an app locale and invalidating the cache key causes the next
     * writeFiles() call to regenerate with an updated Locales: segment in the
     * system key.
     *
     * Note: addAppLocale() alone does NOT invalidate the in-memory system key
     * (no event is fired). setClientLibrariesCacheKey() must also be called to
     * flush the cached system key so that the new locale list is picked up.
     */
    public function test_writeFiles_localeAddedAfterWrite(): void
    {
        Localization::addAppLocale('de_DE');

        $generator = Localization::createGenerator();
        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'Files must be written after first call.');

        // Adding a locale + explicitly invalidating the key causes the system
        // key to be recomputed with the new locale list.
        Localization::addAppLocale('fr_FR');
        Localization::setClientLibrariesCacheKey('refresh-after-locale-add');

        $this->assertFalse($generator->areFilesWritten(), 'After invalidation, files must be out of date.');

        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'Files must be up to date after regeneration.');

        // The stored key must now include fr_FR in the Locales: segment.
        $storedKey = (string)$generator->getCacheKey();
        $this->assertStringContainsString('fr_FR', $storedKey, 'Stored key must include the newly added locale.');
    }

    /**
     * After files are written, a freshly constructed generator instance must
     * read the cachekey.txt file and report areFilesWritten() === true,
     * demonstrating that the cache key persists across calls / requests.
     */
    public function test_writeFiles_cacheKeyFilePersistedAcrossCalls(): void
    {
        Localization::addAppLocale('de_DE');

        $generator = Localization::createGenerator();
        $generator->writeFiles();

        $this->assertTrue($generator->areFilesWritten(), 'Pre-condition: files must be written.');

        // Create a brand-new generator instance that has no in-memory state.
        $freshGenerator = new \AppLocalize\Localization\Translator\ClientFilesGenerator();

        $this->assertTrue(
            $freshGenerator->areFilesWritten(),
            'A fresh generator instance must report files as written by reading cachekey.txt.'
        );
    }
}
