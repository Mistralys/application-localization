<?php
/**
 * Composer script used to invalidate the class cache
 * used to dynamically load country classes and the like.
 * Uses the class helper to clear the cache.
 *
 * @package AppUtils
 * @subpackage Localization
 */

declare(strict_types=1);

use AppLocalize\Localization;

require_once __DIR__.'/../vendor/autoload.php';

echo 'Clearing class cache...';

Localization::clearClassCache();

echo 'DONE.' . PHP_EOL;
