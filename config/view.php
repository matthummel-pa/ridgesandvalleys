<?php
/**
 * Acorn view configuration.
 * Overrides compiled views path to avoid Windows permission conflicts
 * when PHP server runs under a different user than the dev environment.
 */
return [
    'paths' => [
        resource_path('views'),
    ],
    // Prefer explicit Acorn storage (e.g. wp-now/Playground, where the uploads
    // NODEFS mount mangles file permissions); otherwise keep the uploads-dir
    // workaround for Windows permission conflicts.
    'compiled' => defined('ACORN_STORAGE_PATH')
        ? ACORN_STORAGE_PATH . '/framework/views'
        : wp_upload_dir()['basedir'] . '/acorn-views',
];
