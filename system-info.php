<?php

/**
 * Plugin Name:       System Info
 * Plugin URI:        https://github.com/devuri/system-info
 * Description:       Generates system info and composer.json info based on the current WordPress setup.
 * Version:           0.1.3
 * Requires at least: 5.3.0
 * Requires PHP:      7.3.5
 * Author:            uriel
 * Author URI:        https://github.com/devuri
 * Text Domain:       system-info
 * License:           GPLv2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: true.
 */

if ( ! \defined( 'ABSPATH' ) ) {
    exit;
}

// Load composer.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// The plugin.
SystemInfo\Plugin::init()->hooks();
