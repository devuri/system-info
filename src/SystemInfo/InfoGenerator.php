<?php
/**
 * This file is part of the System Info WordPress PLugin.
 *
 * (c) Uriel Wilson <hello@urielwilson.com>
 *
 * Please see the LICENSE file that was distributed with this source code
 * for full copyright and license information.
 */

namespace SystemInfo;

class InfoGenerator
{
    public static function generate_composer_info()
    {
        // Base composer data
        $composer_data = [
            'name'        => 'yourname/wordpress-site',
            'description' => 'A WordPress site managed with Composer',
            'require'     => [
                'php'                  => '>=7.4',
                'johnpbloch/wordpress' => get_bloginfo( 'version' ),
            ],
        ];

        // Get active plugins
        $active_plugins = get_option( 'active_plugins' );

        foreach ( $active_plugins as $plugin_path ) {
            $plugin_dir     = \dirname( $plugin_path );
            $plugin_info    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
            $plugin_version = $plugin_info['Version'] ?? '*';
            $composer_data['require'][ "wpackagist-plugin/{$plugin_dir}" ] = $plugin_version;
        }

        // Get active theme
        $active_theme  = wp_get_theme();
        $theme_version = $active_theme->get( 'Version' );
        $composer_data['require'][ "wpackagist-theme/{$active_theme->get_stylesheet()}" ] = $theme_version ? $theme_version : '*';

        // Encode composer data as JSON
        return json_encode( $composer_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    public static function get_system_info()
    {
        // Get WordPress version
        $wp_version = get_bloginfo( 'version' );

        // Get PHP version
        $php_version = PHP_VERSION;

        // Get active theme
        $active_theme      = wp_get_theme();
        $active_theme_name = $active_theme->get( 'Name' ) . ' ' . $active_theme->get( 'Version' );

        // Get active plugins
        $active_plugins      = get_option( 'active_plugins' );
        $active_plugins_info = [];

        foreach ( $active_plugins as $plugin_path ) {
            $plugin_info           = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
            $plugin_name           = $plugin_info['Name'] . ' ' . ( $plugin_info['Version'] ?? '' );
            $active_plugins_info[] = $plugin_name;
        }

        // Get other server and PHP details
        $memory_limit        = \ini_get( 'memory_limit' );
        $max_execution_time  = \ini_get( 'max_execution_time' );
        $post_max_size       = \ini_get( 'post_max_size' );
        $upload_max_filesize = \ini_get( 'upload_max_filesize' );

        // Generate technical system info
        $system_info = '### Begin System Info (Generated at ' . gmdate( 'Y-m-d H:i:s' ) . ") ###\n\n";

        $system_info .= "-- Site Info --\n\n";
        $system_info .= 'Site URL:                 ' . site_url() . "\n";
        $system_info .= 'Home URL:                 ' . home_url() . "\n";
        $system_info .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";
        $system_info .= 'Active Theme:             ' . $active_theme_name . "\n\n";

        $system_info .= "-- WordPress Configuration --\n\n";
        $system_info .= 'Version:                  ' . $wp_version . "\n";
        $system_info .= 'Language:                 ' . get_locale() . "\n";
        $system_info .= 'Permalink Structure:      ' . get_option( 'permalink_structure' ) . "\n";
        $system_info .= 'WP Table Prefix:          ' . $GLOBALS['wpdb']->prefix . "\n";
        $system_info .= 'GMT Offset:               ' . get_option( 'gmt_offset' ) . "\n";
        $system_info .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
        $system_info .= 'Memory Max Limit:         ' . WP_MAX_MEMORY_LIMIT . "\n";
        $system_info .= 'ABSPATH:                  ' . ABSPATH . "\n";
        $system_info .= 'WP_DEBUG:                 ' . ( WP_DEBUG ? 'Enabled' : 'Disabled' ) . "\n";
        $system_info .= 'WP_DEBUG_LOG:             ' . ( WP_DEBUG_LOG ? 'Enabled' : 'Disabled' ) . "\n";
        $system_info .= 'SAVEQUERIES:              ' . ( \defined( 'SAVEQUERIES' ) && SAVEQUERIES ? 'Enabled' : 'Disabled' ) . "\n";
        $system_info .= 'WP_SCRIPT_DEBUG:          ' . ( \defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'Enabled' : 'Disabled' ) . "\n";
        $system_info .= 'DISABLE_WP_CRON:          ' . ( \defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'Enabled' : 'Disabled' ) . "\n";
        $system_info .= 'WP_CRON_LOCK_TIMEOUT:     ' . ( \defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 'Not set' ) . "\n";
        $system_info .= 'EMPTY_TRASH_DAYS:         ' . ( \defined( 'EMPTY_TRASH_DAYS' ) ? EMPTY_TRASH_DAYS : 'Not set' ) . "\n\n";

        $system_info .= "-- WordPress Active Plugins --\n\n";
        foreach ( $active_plugins_info as $plugin ) {
            $system_info .= $plugin . "\n";
        }

        $system_info .= "\n-- Webserver Configuration --\n\n";
        $system_info .= 'PHP Version:              ' . $php_version . "\n";
        $system_info .= 'MySQL Version:            ' . $GLOBALS['wpdb']->db_version() . "\n";
        $system_info .= 'Web Server Info:          ' . self::get_server_software() . "\n\n";

        $system_info .= "-- PHP Configuration --\n\n";
        $system_info .= 'PHP Memory Limit:         ' . $memory_limit . "\n";
        $system_info .= 'PHP Max Execution Time:   ' . $max_execution_time . "\n";
        $system_info .= 'PHP Post Max Size:        ' . $post_max_size . "\n";
        $system_info .= 'PHP Upload Max Filesize:  ' . $upload_max_filesize . "\n";
        $system_info .= 'PHP Allow URL File Open:  ' . ( \ini_get( 'allow_url_fopen' ) ? 'Yes' : 'No' ) . "\n";
        $system_info .= 'PHP Display Errors:       ' . ( \ini_get( 'display_errors' ) ? 'Enabled' : 'Disabled' ) . "\n";

        $system_info .= "\n### End System Info ###\n";

        return $system_info;
    }

    public static function generate_sql_dump( $dump_database = null ): ?string
    {
        if ( ! $dump_database ) {
            return null;
        }

        global $wpdb;

        $tables   = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
        $sql_dump = "-- SQL Dump\n-- Generated at " . gmdate( 'Y-m-d H:i:s' ) . "\n\n";

        foreach ( $tables as $table ) {
            $table_name = $table[0];
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $create_table = $wpdb->get_row( "SHOW CREATE TABLE `{$table_name}`", ARRAY_N );
            $sql_dump    .= $create_table[1] . ";\n\n";
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $rows = $wpdb->get_results( "SELECT * FROM `{$table_name}`", ARRAY_A );

            foreach ( $rows as $row ) {
                $sql_dump .= "INSERT INTO `{$table_name}` VALUES (";

                $values = [];
                foreach ( $row as $value ) {
                    $values[] = $wpdb->prepare( '%s', $value );
                }

                $sql_dump .= implode( ', ', $values );
                $sql_dump .= ");\n";
            }

            $sql_dump .= "\n";
        }//end foreach

        return $sql_dump;
    }

    protected static function get_server_software()
    {
        if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $server_software = wp_unslash( $_SERVER['SERVER_SOFTWARE'] );

            return sanitize_text_field( $server_software );
        }

        return null;
    }
}
