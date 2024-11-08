<?php

/**
 * Plugin Name:       System Info
 * Plugin URI:        https://github.com/devuri/system-info
 * Description:       Generates system info and composer.json info based on the current WordPress setup.
 * Version:           0.1.12
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

define( 'SYSTEMINFO_INDICATOR_CRON', 'systemi_updates_indicator_cron' );

// Load composer.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled(SYSTEMINFO_INDICATOR_CRON)) {
        wp_schedule_event(time(), 'hourly', SYSTEMINFO_INDICATOR_CRON);
    }
});

register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook(SYSTEMINFO_INDICATOR_CRON);
});


/**
 * Initializes the SystemInfo plugin and sets up hooks with the 'systemi_dump_database' option.
 *
 * The 'systemi_dump_database' option determines whether the plugin should include a database dump
 * when generating system info. By default, this option returns false, meaning the database dump
 * will only be included if the option is explicitly set to a truthy value.
 */
SystemInfo\Plugin::init()->hooks( get_option( 'systemi_dump_database' ) );
