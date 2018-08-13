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

	$epd_settings_page   = add_submenu_page(
        'settings.php',
        __( 'EPD Settings', 'easy-plugin-demo' ),
        __( 'Easy Plugin Demo', 'easy-plugin-demo' ),
        'manage_sites',
        'epd-settings',
        'epd_options_page'
    );

} // epd_add_options_link
add_action( 'network_admin_menu', 'epd_add_options_link', 20 );
