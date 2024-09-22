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

class Generator
{
    public static function generate_composer_info( bool $exact_version = false )
    {
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
            $plugin_version = self::constraint( $plugin_info['Version'] ?? '*', $exact_version );
            $composer_data['require'][ "wpackagist-plugin/{$plugin_dir}" ] = $plugin_version;
        }

        // Get active theme
        $active_theme  = wp_get_theme();
        $theme_version = $active_theme->get( 'Version' );
        $composer_data['require'][ "wpackagist-theme/{$active_theme->get_stylesheet()}" ] = $theme_version ? $theme_version : '*';

        return self::composer_output( $composer_data );
    }

    public static function get_system_info()
    {
        $system_info = new Info();

        return $system_info->get_system_info();
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
        }// end foreach

        return $sql_dump;
    }

    private static function composer_output( array $composer_data )
    {
        return wp_json_encode( $composer_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    private static function constraint( string $version, bool $exact )
    {
        if ( '*' === $version || $exact ) {
            return $version;
        }

        $parts = explode( '.', $version );
        if ( 3 !== \count( $parts ) ) {
            return $version;
        }

        return '^' . $parts[0] . '.' . $parts[1];
    }
}
