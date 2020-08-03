<?php
/**
 * Admin Pages
 *
 * @package     EPD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Creates the admin submenu settings page within the MU network admin menu
 *
 * @since	1.0
 * @return	void
 */
function epd_add_options_link() {

    if ( ! is_super_admin() )   {
        return;
    }

	global $epd_settings_page;

	$epd_settings_page = add_submenu_page(
        'settings.php',
        __( 'EPD Settings', 'easy-plugin-demo' ),
        __( 'Easy Plugin Demo', 'easy-plugin-demo' ),
        'manage_sites',
        'epd-settings',
        'epd_options_page'
    );

} // epd_add_options_link
add_action( 'network_admin_menu', 'epd_add_options_link', 20 );

/**
 * Adds a reset site option to the tools menu
 *
 * @since	1.3
 * @return	void
 */
function epd_add_menu_items() {
	global $epd_reset_site_page;

	$reset_role = epd_get_reset_site_cap_role();

	$epd_reset_site_page = add_submenu_page(
        'tools.php',
        __( 'Reset Site', 'easy-plugin-demo' ),
        __( 'Reset Site', 'easy-plugin-demo' ),
        $reset_role,
        'epd_reset',
        'epd_output_reset_screen'
    );
} // epd_add_menu_items
add_action( 'admin_menu', 'epd_add_menu_items' );

/**
 * The site reset page.
 *
 * @since   1.3
 * @return  void
 */
function epd_output_reset_screen()  {
    echo 'Hi';
} // epd_output_reset_screen
