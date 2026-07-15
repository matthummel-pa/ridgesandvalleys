<?php

/**
 * WP-CLI commands for developers working on this theme.
 *
 *   wp pressroot settings export [--file=<path>]
 *   wp pressroot settings import <file>
 *   wp pressroot kit list
 *   wp pressroot kit apply <slug>
 *   wp pressroot views clear
 *   wp pressroot hooks [--type=<filters|actions>]
 *
 * Reuses the same data sources as Appearance -> Theme Tools
 * (prt_owned_mods() / prt_style_kits() in app/settings-io.php) so the CLI
 * and the admin UI can never drift apart.
 */

namespace App;

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

class Prt_Cli_Command
{
    /**
     * Export this theme's settings (prt_* theme mods) as JSON.
     *
     * ## OPTIONS
     *
     * [--file=<path>]
     * : File to write to. Defaults to ./pressroot-settings-<date>.json in the current directory.
     *
     * ## EXAMPLES
     *
     *     wp pressroot settings export
     *     wp pressroot settings export --file=/tmp/settings.json
     */
    public function export($args, $assoc_args)
    {
        $payload = [
            'theme'   => 'pressroot',
            'version' => wp_get_theme()->get('Version'),
            'date'    => gmdate('c'),
            'mods'    => prt_owned_mods(),
        ];

        $file = $assoc_args['file'] ?? ('./pressroot-settings-' . gmdate('Ymd-His') . '.json');
        $ok   = file_put_contents($file, wp_json_encode($payload, JSON_PRETTY_PRINT));

        if ($ok === false) {
            \WP_CLI::error("Could not write to {$file}.");
        }

        \WP_CLI::success(sprintf('Exported %d setting(s) to %s', count($payload['mods']), $file));
    }

    /**
     * Import theme settings from a JSON export.
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to a JSON file produced by `wp pressroot settings export` (or the Theme Tools export button).
     *
     * ## EXAMPLES
     *
     *     wp pressroot settings import ./pressroot-settings-20260702.json
     */
    public function import($args, $assoc_args)
    {
        if (empty($args[0])) {
            \WP_CLI::error('Usage: wp pressroot settings import <file>');
        }
        $file = $args[0];

        if (! is_readable($file)) {
            \WP_CLI::error("Cannot read {$file}.");
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (! is_array($data) || ! isset($data['mods']) || ! is_array($data['mods'])) {
            \WP_CLI::error('That file does not look like a pressroot settings export (missing "mods").');
        }

        $count = 0;
        foreach ($data['mods'] as $k => $v) {
            if (is_string($k) && strpos($k, 'prt_') === 0 && (is_scalar($v) || is_array($v))) {
                set_theme_mod($k, $v);
                $count++;
            }
        }

        \WP_CLI::success("Imported {$count} setting(s) from {$file}.");
    }

    /**
     * Remove every prt_* theme mod, returning to defaults.
     *
     * ## EXAMPLES
     *
     *     wp pressroot settings reset --yes
     */
    public function reset($args, $assoc_args)
    {
        \WP_CLI::confirm('This will remove every pressroot theme setting. Export first if you want a backup. Continue?', $assoc_args);

        $keys = array_keys(prt_owned_mods());
        foreach ($keys as $k) {
            remove_theme_mod($k);
        }

        \WP_CLI::success(sprintf('Removed %d setting(s).', count($keys)));
    }
}

class Prt_Kit_Command
{
    /**
     * List the available Style Kit presets.
     *
     * ## EXAMPLES
     *
     *     wp pressroot kit list
     */
    public function list($args, $assoc_args)
    {
        $rows = [];
        foreach (prt_style_kits() as $slug => $kit) {
            $rows[] = [
                'slug'  => $slug,
                'label' => $kit['label'],
                'desc'  => $kit['desc'],
            ];
        }
        \WP_CLI\Utils\format_items('table', $rows, ['slug', 'label', 'desc']);
    }

    /**
     * Apply a Style Kit preset (palette + fonts + radius).
     *
     * ## OPTIONS
     *
     * <slug>
     * : The kit slug, from `wp pressroot kit list`.
     *
     * ## EXAMPLES
     *
     *     wp pressroot kit apply midnight
     */
    public function apply($args, $assoc_args)
    {
        if (empty($args[0])) {
            \WP_CLI::error('Usage: wp pressroot kit apply <slug> (see `wp pressroot kit list`)');
        }
        $slug = $args[0];

        // Shared with the "Apply" buttons on Appearance -> Theme Tools
        // (prt_apply_style_kit() in app/settings-io.php, near prt_style_kits()).
        if (! prt_apply_style_kit($slug)) {
            \WP_CLI::error("Unknown kit '{$slug}'. Run `wp pressroot kit list` to see available slugs.");
        }

        \WP_CLI::success("Applied style kit '{$slug}'.");
    }
}

/**
 * List every registered filter/action from the theme's hook registry
 * (app/hooks-registry.php), so a developer extending this theme doesn't
 * have to grep the codebase to find an extension point.
 *
 * ## OPTIONS
 *
 * [--type=<type>]
 * : Limit to "filters" or "actions". Defaults to both.
 *
 * ## EXAMPLES
 *
 *     wp pressroot hooks
 *     wp pressroot hooks --type=filters
 */
function prt_cli_hooks($args, $assoc_args)
{
    $registry = prt_hook_registry();
    $type     = $assoc_args['type'] ?? null;
    $rows     = [];

    foreach ($registry as $kind => $hooks) {
        if ($type && $type !== $kind) {
            continue;
        }
        foreach ($hooks as $name => $meta) {
            $rows[] = [
                'type' => rtrim($kind, 's'), // filter | action
                'hook' => $name,
                'file' => $meta['file'],
                'description' => $meta['desc'],
            ];
        }
    }

    \WP_CLI\Utils\format_items('table', $rows, ['type', 'hook', 'file', 'description']);
}

/**
 * Clear compiled Acorn/Blade views.
 * Same underlying logic as visiting any admin URL with ?prt_view_clear=1
 * (see prt_clear_compiled_views() in app/setup.php), just scriptable.
 *
 * ## EXAMPLES
 *
 *     wp pressroot views clear
 */
function prt_cli_views_clear($args, $assoc_args)
{
    $count = prt_clear_compiled_views();
    \WP_CLI::success("Cleared {$count} compiled view file(s).");
}

\WP_CLI::add_command('pressroot settings', __NAMESPACE__ . '\\Prt_Cli_Command');
\WP_CLI::add_command('pressroot kit', __NAMESPACE__ . '\\Prt_Kit_Command');
\WP_CLI::add_command('pressroot hooks', __NAMESPACE__ . '\\prt_cli_hooks');
\WP_CLI::add_command('pressroot views clear', __NAMESPACE__ . '\\prt_cli_views_clear');
