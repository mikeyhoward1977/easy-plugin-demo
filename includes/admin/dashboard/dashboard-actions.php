<?php
/**
 * Dashboard Functions
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove the welcome panel.
 *
 * @since	1.0.1
 * @return	void
 */
function epd_manage_dashboard_welcome_panel()	{
	if ( get_current_blog_id() == get_network()->blog_id )	{
		return;
	}

	if ( epd_get_option( 'hide_welcome' ) )	{
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}

	if ( epd_get_option( 'custom_welcome' ) )	{
		add_action( 'welcome_panel', 'epd_render_custom_welcome_panel' );
	}
} // epd_manage_dashboard_welcome_panel
add_action( 'wp_dashboard_setup', 'epd_manage_dashboard_welcome_panel' );
