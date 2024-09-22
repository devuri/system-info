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

class Info
{
    // Properties site information
    private $home_url;
    private $admin_url;
    private $timezone;
    private $gmt_offset;
    private $local_datetime;
    private $blog_public;
    private $user_count;
    private $php_uname;
    private $permalink_structure;
    private $admin_email;
    private $site_url;
    private $server_software;
    private $https;
    private $disk_total_space;
    private $disk_free_space;
    private $plugins = [];
    private $themes  = [];

    // Additional properties system info
    private $wp_version;
    private $php_version;
    private $active_theme_name;
    private $active_plugins_info = [];
    private $memory_limit;
    private $max_execution_time;
    private $post_max_size;
    private $upload_max_filesize;
    private $mysql_version;
    private $multisite;
    private $wp_table_prefix;
    private $language;

    // Constants and standalone functions
    private $wp_memory_limit;
    private $wp_max_memory_limit;
    private $abspath;
    private $wp_debug;
    private $wp_debug_log;
    private $savequeries;
    private $script_debug;
    private $disable_wp_cron;
    private $wp_cron_lock_timeout;
    private $empty_trash_days;

    public function __construct( ?string $url = null )
    {
        $this->home_url            = home_url();
        $this->admin_url           = admin_url( $url );
        $this->timezone            = wp_timezone_string();
        $this->gmt_offset          = get_option( 'gmt_offset' );
        $this->local_datetime      = current_time( 'mysql' );
        $this->blog_public         = get_option( 'blog_public' );
        $this->user_count          = count_users();
        $this->php_uname           = php_uname();
        $this->permalink_structure = esc_html( get_option( 'permalink_structure' ) );
        $this->admin_email         = get_option( 'admin_email' );
        $this->site_url            = get_site_url();

        $this->server_software = $this->get_server_software();
        $this->https           = $this->get_server_https();

        $this->disk_total_space = \function_exists( 'disk_total_space' ) ? disk_total_space( ABSPATH ) : 'not available';
        $this->disk_free_space  = \function_exists( 'disk_free_space' ) ? disk_free_space( ABSPATH ) : 'not available';

        $this->wp_version          = get_bloginfo( 'version' );
        $this->php_version         = PHP_VERSION;
        $this->active_theme_name   = $this->get_active_theme_name();
        $this->active_plugins_info = $this->get_active_plugins_info();

        $this->memory_limit        = \ini_get( 'memory_limit' );
        $this->max_execution_time  = \ini_get( 'max_execution_time' );
        $this->post_max_size       = \ini_get( 'post_max_size' );
        $this->upload_max_filesize = \ini_get( 'upload_max_filesize' );
        $this->mysql_version       = $GLOBALS['wpdb']->db_version();

        $this->multisite       = is_multisite();
        $this->wp_table_prefix = $GLOBALS['wpdb']->prefix;
        $this->language        = get_locale();

        // Constants and standalone functions
        $this->wp_memory_limit      = \defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'Not set';
        $this->wp_max_memory_limit  = \defined( 'WP_MAX_MEMORY_LIMIT' ) ? WP_MAX_MEMORY_LIMIT : 'Not set';
        $this->abspath              = \defined( 'ABSPATH' ) ? ABSPATH : 'Not set';
        $this->wp_debug             = \defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
        $this->wp_debug_log         = \defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false;
        $this->savequeries          = \defined( 'SAVEQUERIES' ) ? SAVEQUERIES : false;
        $this->script_debug         = \defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false;
        $this->disable_wp_cron      = \defined( 'DISABLE_WP_CRON' ) ? DISABLE_WP_CRON : false;
        $this->wp_cron_lock_timeout = \defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 'Not set';
        $this->empty_trash_days     = \defined( 'EMPTY_TRASH_DAYS' ) ? EMPTY_TRASH_DAYS : 'Not set';

        $this->set_plugins();
        $this->set_themes();
    }

    public function get_data()
    {
        return [
            'home_url'             => $this->home_url,
            'admin_url'            => $this->admin_url,
            'timezone'             => $this->timezone,
            'gmt_offset'           => $this->gmt_offset,
            'local_datetime'       => $this->local_datetime,
            'blog_public'          => $this->blog_public,
            'user_count'           => $this->user_count,
            'php_uname'            => $this->php_uname,
            'permalink_structure'  => $this->permalink_structure,
            'admin_email'          => $this->admin_email,
            'site_url'             => $this->site_url,
            'server_software'      => $this->server_software,
            'https'                => $this->https,
            'disk_total_space'     => $this->disk_total_space,
            'disk_free_space'      => $this->disk_free_space,
            'plugins'              => $this->plugins,
            'themes'               => $this->themes,
            'wp_version'           => $this->wp_version,
            'php_version'          => $this->php_version,
            'active_theme_name'    => $this->active_theme_name,
            'active_plugins_info'  => $this->active_plugins_info,
            'memory_limit'         => $this->memory_limit,
            'max_execution_time'   => $this->max_execution_time,
            'post_max_size'        => $this->post_max_size,
            'upload_max_filesize'  => $this->upload_max_filesize,
            'mysql_version'        => $this->mysql_version,
            'multisite'            => $this->multisite,
            'wp_table_prefix'      => $this->wp_table_prefix,
            'language'             => $this->language,
            // Constants and standalone functions
            'wp_memory_limit'      => $this->wp_memory_limit,
            'wp_max_memory_limit'  => $this->wp_max_memory_limit,
            'ABSPATH'              => $this->abspath,
            'wp_debug'             => $this->wp_debug ? 'Enabled' : 'Disabled',
            'wp_debug_log'         => $this->wp_debug_log ? 'Enabled' : 'Disabled',
            'savequeries'          => $this->savequeries ? 'Enabled' : 'Disabled',
            'script_debug'         => $this->script_debug ? 'Enabled' : 'Disabled',
            'disable_wp_cron'      => $this->disable_wp_cron ? 'Enabled' : 'Disabled',
            'wp_cron_lock_timeout' => $this->wp_cron_lock_timeout,
            'empty_trash_days'     => $this->empty_trash_days,
        ];
    }

    public function get_system_info()
    {
        $system_info = '### Begin System Info (Generated at ' . gmdate( 'Y-m-d H:i:s' ) . ") ###\n\n";

        $system_info .= "-- Site Info --\n\n";
        $system_info .= 'Site URL:                 ' . $this->site_url . "\n";
        $system_info .= 'Home URL:                 ' . $this->home_url . "\n";
        $system_info .= 'Multisite:                ' . ( $this->multisite ? 'Yes' : 'No' ) . "\n";
        $system_info .= 'Active Theme:             ' . $this->active_theme_name . "\n\n";

        $system_info .= "-- WordPress Configuration --\n\n";
        $system_info .= 'Version:                  ' . $this->wp_version . "\n";
        $system_info .= 'Language:                 ' . $this->language . "\n";
        $system_info .= 'Permalink Structure:      ' . $this->permalink_structure . "\n";
        $system_info .= 'WP Table Prefix:          ' . $this->wp_table_prefix . "\n";
        $system_info .= 'GMT Offset:               ' . $this->gmt_offset . "\n";
        $system_info .= 'Memory Limit:             ' . $this->wp_memory_limit . "\n";
        $system_info .= 'Memory Max Limit:         ' . $this->wp_max_memory_limit . "\n";
        $system_info .= 'ABSPATH:                  ' . $this->abspath . "\n";
        $system_info .= 'WP_DEBUG:                 ' . $this->wp_debug . "\n";
        $system_info .= 'WP_DEBUG_LOG:             ' . $this->wp_debug_log . "\n";
        $system_info .= 'SAVEQUERIES:              ' . $this->savequeries . "\n";
        $system_info .= 'WP_SCRIPT_DEBUG:          ' . $this->script_debug . "\n";
        $system_info .= 'DISABLE_WP_CRON:          ' . $this->disable_wp_cron . "\n";
        $system_info .= 'WP_CRON_LOCK_TIMEOUT:     ' . $this->wp_cron_lock_timeout . "\n";
        $system_info .= 'EMPTY_TRASH_DAYS:         ' . $this->empty_trash_days . "\n\n";

        $system_info .= "-- WordPress Active Plugins --\n\n";
        foreach ( $this->active_plugins_info as $plugin ) {
            $system_info .= $plugin . "\n";
        }

        $system_info .= "\n-- Webserver Configuration --\n\n";
        $system_info .= 'PHP Version:              ' . $this->php_version . "\n";
        $system_info .= 'MySQL Version:            ' . $this->mysql_version . "\n";
        $system_info .= 'Server Software:          ' . $this->server_software . "\n\n";

        $system_info .= "-- PHP Configuration --\n\n";
        $system_info .= 'PHP Memory Limit:         ' . $this->memory_limit . "\n";
        $system_info .= 'PHP Max Execution Time:   ' . $this->max_execution_time . "\n";
        $system_info .= 'PHP Post Max Size:        ' . $this->post_max_size . "\n";
        $system_info .= 'PHP Upload Max Filesize:  ' . $this->upload_max_filesize . "\n";
        $system_info .= 'PHP Allow URL File Open:  ' . ( \ini_get( 'allow_url_fopen' ) ? 'Yes' : 'No' ) . "\n";
        $system_info .= 'PHP Display Errors:       ' . ( \ini_get( 'display_errors' ) ? 'Enabled' : 'Disabled' ) . "\n";

        $system_info .= "\n### End System Info ###\n";

        return $system_info;
    }

    protected static function get_server_software()
    {
        if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
            return sanitize_text_field(
                wp_unslash( $_SERVER['SERVER_SOFTWARE'] )
            );
        }

        return 'Not Available';
    }

    protected static function get_server_https(): string
    {
        $https = null;

        if ( isset( $_SERVER['HTTPS'] ) ) {
            $https = sanitize_key( wp_unslash( $_SERVER['HTTPS'] ) );
        }

        if ( 'on' === $https || '1' === $https ) {
            return 'on';
        }

        return 'off';
    }

    private function get_active_theme_name()
    {
        $active_theme = wp_get_theme();

        return $active_theme->get( 'Name' ) . ' ' . $active_theme->get( 'Version' );
    }

    private function get_active_plugins_info()
    {
        $active_plugins      = get_option( 'active_plugins' );
        $active_plugins_info = [];

        foreach ( $active_plugins as $plugin_path ) {
            $plugin_info           = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
            $plugin_name           = $plugin_info['Name'] . ' ' . ( $plugin_info['Version'] ?? '' );
            $active_plugins_info[] = $plugin_name;
        }

        return $active_plugins_info;
    }

    private function set_plugins(): void
    {
        foreach ( get_plugins() as $path => $plugin ) {
            $key = ! empty( $plugin['TextDomain'] ) ? $plugin['TextDomain'] : $path;

            $this->plugins[ $key ] = [
                'active'     => is_plugin_active( $path ),
                'name'       => $plugin['Name'],
                'uri'        => $plugin['PluginURI'],
                'version'    => $plugin['Version'],
                'textdomain' => $plugin['TextDomain'],
            ];
        }
    }

    private function set_themes(): void
    {
        foreach ( wp_get_themes() as $path => $theme ) {
            // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
            $key = $theme->get( 'TextDomain' ) ?: $path;

            $this->themes[ $key ] = [
                'active'         => ( wp_get_theme()->get( 'TextDomain' ) === $key ) ? 1 : 0,
                'name'           => $theme->get( 'Name' ),
                'uri'            => $theme->get( 'ThemeURI' ),
                'version'        => $theme->get( 'Version' ),
                'textdomain'     => $theme->get( 'TextDomain' ),
                'theme_template' => $theme->get_template(),
                'theme_parent'   => $theme->parent() ? $theme->parent()->get_template() : null,
            ];
        }
    }
}
