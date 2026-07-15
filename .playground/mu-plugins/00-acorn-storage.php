<?php
/** Playground/WASM fixes for Acorn (Sage themes):
 *  1. WASM PHP umask is broken -> Laravel writes mode-000 cache files. Normalize it.
 *  2. Keep Acorn storage on MEMFS (/tmp), NODEFS host mounts mangle perms.
 *  3. Playground's PHP reports SAPI "cli", so Laravel's runningInConsole()
 *     returns true and Acorn never runs bootHttp() -> no providers, fatals on
 *     any view() call. Force console detection off for web requests. */
umask(0022);

if (PHP_SAPI === 'cli' && isset($_SERVER['REQUEST_URI'])) {
    $_ENV['APP_RUNNING_IN_CONSOLE'] = 'false';
    $_SERVER['APP_RUNNING_IN_CONSOLE'] = 'false';
    putenv('APP_RUNNING_IN_CONSOLE=false');
}

if (! defined('ACORN_STORAGE_PATH')) {
    define('ACORN_STORAGE_PATH', '/tmp/acorn-storage');
    foreach (['framework/cache/data', 'framework/views', 'framework/sessions', 'logs'] as $dir) {
        @mkdir(ACORN_STORAGE_PATH . '/' . $dir, 0777, true);
    }
}
