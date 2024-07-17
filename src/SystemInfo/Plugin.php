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

use Urisoft\AbstractPlugin;

class Plugin extends AbstractPlugin
{
    public function hooks(): void
    {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
    }

    public function add_admin_menu(): void
    {
        add_management_page(
            'System Info Generator',
            'System Info',
            'manage_options',
            'system-info-generator',
            [ $this, 'create_admin_page' ]
        );
    }

    public function create_admin_page(): void
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'system-info' ) );
        }

        // Generate composer file data
        $composer_json = InfoGenerator::generate_composer_info();
        $system_info   = InfoGenerator::get_system_info();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'System Info Generator', 'system-info' ); ?></h1>
            <h2><?php esc_html_e( 'Generated composer.json', 'system-info' ); ?></h2>
            <textarea rows="20" cols="100" readonly><?php echo esc_textarea( $composer_json ); ?></textarea>
            <h2><?php esc_html_e( 'System Info', 'system-info' ); ?></h2>
			<textarea rows="20" cols="100" readonly><?php echo esc_textarea( $system_info ); ?></textarea>
        </div>
        <?php
    }
}
