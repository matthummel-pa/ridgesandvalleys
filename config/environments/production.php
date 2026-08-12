<?php

/**
 * Production configuration — applied when WP_ENV=production.
 */

use Roots\WPConfig\Config;

// Lock down the file editor + install/update UI on live.
Config::define("DISALLOW_FILE_EDIT", true);
Config::define("DISALLOW_FILE_MODS", true);
Config::define("WP_DEBUG_DISPLAY", false);
Config::define("SCRIPT_DEBUG", false);
ini_set("display_errors", "0");
