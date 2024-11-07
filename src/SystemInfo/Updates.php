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

use WP_Admin_Bar;

class Updates
{
    private const CACHE_TRANSIENT_NAME = 'admin_bar_updates_cache';
    private const CACHE_EXPIRY         = 3600;
    private const PAGE_SLUG            = 'available-updates';
    private const USER_PERMISSION      = 'manage_options';

    public function hooks(): void
    {
        add_action( 'admin_bar_menu', [ $this, 'addAdminBarIndicator' ], 100 );
        add_action( 'init', [ $this, 'handleManualRefresh' ] );
        add_action( 'admin_bar_updates_indicator_cron', [ $this, 'fetchAndCacheUpdateData' ] );
        add_action( 'admin_menu', [ $this, 'addHiddenUpdatesPage' ] );
    }

    /**
     * Registers a hidden admin page to display available updates.
     */
    public function addHiddenUpdatesPage(): void
    {
        add_submenu_page(
            null,
            __( 'Available Updates Reference', 'system-info' ),
            __( 'Available Updates', 'system-info' ),
            'manage_options',
            self::PAGE_SLUG,
            [ $this, 'renderUpdatesPage' ]
        );
    }

    /**
     * Adds the main updates indicator to the admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar
     *
     * @return void
     */
    public function addAdminBarIndicator( WP_Admin_Bar $wp_admin_bar ): void
    {
        if ( ! current_user_can( self::USER_PERMISSION ) ) {
            return;
        }

        $updateData = $this->getCachedUpdateData();
        // dd($updateData);
        $updateCount = $updateData['total'] ?? 0;

        if ( 0 === $updateCount ) {
            return;
        }

        $refreshLink  = wp_nonce_url( add_query_arg( 'refresh_updates_cache', 'true' ), 'refresh_updates_cache' );
        $moreInfoLink = admin_url( 'admin.php?page=' . self::PAGE_SLUG );

        $wp_admin_bar->add_node(
            [
                'id'    => 'updates_indicator',
                'title' => ":: Available Updates [$updateCount] ::",
                'href'  => $refreshLink,
                'meta'  => [ 'title' => 'Click to refresh update cache' ],
            ]
        );

        $this->addUpdateDetails( $wp_admin_bar, $updateData );

        $wp_admin_bar->add_node(
            [
				'parent' => 'updates_indicator',
				'id'     => 'more_info',
				'title'  => 'More Info',
				'href'   => $moreInfoLink,
			]
        );
    }

    public function renderUpdatesPage(): void
    {
        if ( ! current_user_can( self::USER_PERMISSION ) ) {
            wp_die( 'Access denied' );
        }

        $updateData = $this->getCachedUpdateData();

        $coreUpdates   = $updateData['core'];
        $pluginUpdates = $updateData['plugins'];
        $themeUpdates  = $updateData['themes'];
        $pluginList    = ! empty( $updateData['plugin_list'] ) ? $updateData['plugin_list'] : '';
        $themeList     = ! empty( $updateData['theme_list'] ) ? $updateData['theme_list'] : '';

        ?><div class="wrap">
            <h1>Available Updates</h1>
            <div class="update-summary">
                <p class="notice update-nag inline">Core Updates: <strong><?php echo $coreUpdates; ?></strong></p>
                <p class="notice update-nag inline">Plugin Updates: <strong><?php echo $pluginUpdates; ?></strong></p>
                <p class="notice update-nag inline">Theme Updates: <strong><?php echo $themeUpdates; ?></strong></p>
            </div>

            <div class="update-details">
                <h2>Plugins to Update</h2>
				<p class="plugin-updates-list">
				    <?php
                    foreach ( $pluginList as $pluginFile => $pluginData ) {
				        // Get plugin data from WordPress
				        $pluginInfo     = get_plugin_data( WP_PLUGIN_DIR . '/' . $pluginFile );
				        $pluginName     = $pluginInfo['Name'];
				        $currentVersion = $pluginInfo['Version'];
				        $description    = $pluginInfo['Description'];
				        $author         = $pluginInfo['Author'];

				        // Update data fields
				        $newVersion   = $pluginData->new_version ?? 'N/A';
				        $pluginUrl    = $pluginData->url ?? '#';
				        $downloadLink = $pluginData->package ?? '#';
				        $requiresWP   = $pluginData->requires ?? 'N/A';
				        $testedUpTo   = $pluginData->tested ?? 'N/A';
				        $requiresPHP  = $pluginData->requires_php ?? 'N/A';
				        $iconUrl      = $pluginData->icons['2x'] ?? $pluginData->icons['1x'] ?? '';

				        ?>
				        <div class="notice notice-warning update-nag inline" style="margin-bottom: 20px;">
				            <div style="display: flex; align-items: center;">
				                <?php if ( $iconUrl ) { ?>
				                    <img src="<?php echo esc_url( $iconUrl ); ?>" alt="<?php echo esc_attr( $pluginName ); ?>" width="50" height="50" style="margin-right: 15px;">
				                <?php } ?>
				                <div>
				                    <h3 style="margin: 0;"><?php echo esc_html( $pluginName ); ?> (Current Version: <?php echo esc_html( $currentVersion ); ?>)</h3>
				                    <p><strong>Author:</strong> <?php echo wp_kses_post( $author ); ?></p>
				                </div>
				            </div>

							<p><strong>New Version:</strong> <?php echo esc_html( $newVersion ); ?></p>
				            <p><strong>Plugin URL:</strong> <a href="<?php echo esc_url( $pluginUrl ); ?>" target="_blank">View Plugin Details</a></p>
				            <p><strong>Download Package:</strong> <a href="<?php echo esc_url( $downloadLink ); ?>" target="_blank">Download v<?php echo esc_html( $newVersion ); ?></a></p>
				            <p><strong>Requires WordPress Version:</strong> <?php echo esc_html( $requiresWP ); ?></p>
				            <p><strong>Tested up to WordPress Version:</strong> <?php echo esc_html( $testedUpTo ); ?></p>
				            <p><strong>Requires PHP Version:</strong> <?php echo esc_html( $requiresPHP ? $requiresPHP : 'N/A' ); ?></p>
					</div>
						<?php
                    }//end foreach
					?>
				</div>


                <h2 class="">Themes to Update</h2>
				<?php
                foreach ( $themeList as $key => $themeInfo ) {
				    $theme = wp_get_theme( $themeInfo['theme'] );

				    $newVersion   = $themeInfo['new_version'] ?? 'N/A';
				    $themeUrl     = $themeInfo['url'] ?? '#';
				    $downloadLink = $themeInfo['package'] ?? '#';
				    $requiresWP   = $themeInfo['requires'] ?? 'N/A';
				    $requiresPHP  = $themeInfo['requires_php'] ?? 'N/A';

				    ?>
						<div class="notice notice-warning update-nag inline">
						<h4><?php echo esc_html( $theme->get( 'Name' ) ); ?> (Current Version: <?php echo esc_html( $theme->get( 'Version' ) ); ?>)</h4>
						   <p><strong>Author:</strong> <a href="<?php echo esc_url( $theme->get( 'AuthorURI' ) ); ?>" target="_blank">
							   <?php echo esc_html( $theme->get( 'Author' ) ); ?>
						   </a></p>
							   <p><strong>New Version:</strong> <?php echo esc_html( $newVersion ); ?></p>
							   <p><strong>Theme URL:</strong> <a href="<?php echo esc_url( $themeUrl ); ?>" target="_blank">View Theme Details</a></p>
							   <p><strong>Download Package:</strong> <a href="<?php echo esc_url( $downloadLink ); ?>" target="_blank">Download v<?php echo esc_html( $newVersion ); ?></a></p>
							   <p><strong>Requires WordPress Version:</strong> <?php echo esc_html( $requiresWP ); ?></p>
							   <p><strong>Requires PHP Version:</strong> <?php echo esc_html( $requiresPHP ); ?></p>
						</div>
				    <?php
				}//end foreach
				?>
				</div>

            </div>
        </div>
        <?php
        exit;
    }

    /**
     * Fetches and caches update data.
     *
     * @return array
     */
    public function fetchAndCacheUpdateData(): array
    {
        $coreUpdates   = \count( $this->getCoreUpdates() );
        $pluginUpdates = $this->getPluginUpdates();
        $themeUpdates  = $this->getThemeUpdates();

        $updateData = [
            'total'       => $coreUpdates + \count( $pluginUpdates ) + \count( $themeUpdates ),
            'core'        => $coreUpdates,
            'plugins'     => \count( $pluginUpdates ),
            'themes'      => \count( $themeUpdates ),
            'plugin_list' => $pluginUpdates,
            'theme_list'  => $themeUpdates,
        ];

        set_transient( self::CACHE_TRANSIENT_NAME, $updateData, self::CACHE_EXPIRY );

        return $updateData;
    }

    /**
     * Handles manual refresh if the 'refresh_updates_cache' parameter is present in the URL.
     *
     * @return void
     */
    public function handleManualRefresh(): void
    {
        if ( isset( $_GET['refresh_updates_cache'] ) && check_admin_referer( 'refresh_updates_cache' ) ) {
            $this->fetchAndCacheUpdateData();
            wp_redirect( remove_query_arg( 'refresh_updates_cache' ) );
            exit;
        }
    }

    /**
     * Adds detailed update categories to the admin bar item.
     *
     * @param WP_Admin_Bar $wp_admin_bar
     * @param array        $updateData
     *
     * @return void
     */
    private function addUpdateDetails( WP_Admin_Bar $wp_admin_bar, array $updateData ): void
    {
        // dd($updateData);
        if ( $updateData['core'] > 0 ) {
            $wp_admin_bar->add_node(
                [
                    'parent' => 'updates_indicator',
                    'id'     => 'core_updates',
                    'title'  => 'Core Updates: ' . $updateData['core'],
                ]
            );
        }

        if ( $updateData['plugins'] > 0 ) {
            $wp_admin_bar->add_node(
                [
                    'parent' => 'updates_indicator',
                    'id'     => 'plugin_updates',
                    'title'  => 'Plugin Updates: ' . $updateData['plugins'],
                ]
            );
        }

        if ( $updateData['themes'] > 0 ) {
            $wp_admin_bar->add_node(
                [
                    'parent' => 'updates_indicator',
                    'id'     => 'theme_updates',
                    'title'  => 'Theme Updates: ' . $updateData['themes'],
                ]
            );
        }
    }

    /**
     * Retrieves cached update data or fetches new data if cache is expired.
     *
     * @return array
     */
    private function getCachedUpdateData(): array
    {
        $cachedData = get_transient( self::CACHE_TRANSIENT_NAME );

        if ( $cachedData ) {
            return $cachedData;
        }

        return $this->fetchAndCacheUpdateData();
    }

    /**
     * Gets available core updates.
     *
     * @return array
     */
    private function getCoreUpdates(): array
    {
        require_once ABSPATH . 'wp-admin/includes/update.php';

        return get_core_updates( [ 'dismissed' => false ] );
    }

    /**
     * Gets available plugin updates.
     *
     * @return array
     */
    private function getPluginUpdates(): array
    {
        require_once ABSPATH . 'wp-admin/includes/update.php';
        wp_update_plugins();
        $pluginUpdates = get_site_transient( 'update_plugins' );

        return $pluginUpdates->response ?? [];
    }

    /**
     * Gets available theme updates.
     *
     * @return array
     */
    private function getThemeUpdates(): array
    {
        require_once ABSPATH . 'wp-admin/includes/update.php';
        wp_update_themes();
        $themeUpdates = get_site_transient( 'update_themes' );

        return $themeUpdates->response ?? [];
    }
}
