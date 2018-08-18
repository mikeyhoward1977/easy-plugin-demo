<?php
/**
 * Admin Options Actions
 *
 * @package     EPD
 * @subpackage  Admin/Settings/Actions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Save options.
 *
 * @since	1.0
 * @return	void
 */
function epd_save_options_action() {
	if ( empty( $_POST['epd_action'] ) || 'epd_update_settings' != $_POST['epd_action'] )	{
		return;
	}

	if ( empty( $_POST['update_epd_options'] || ! wp_verify_nonce( $_POST['update_epd_options'], 'epd_options' ) ) )	{
		return;
	}

	if ( ! current_user_can( 'manage_network_options' ) )	{
		wp_die( __( 'You do not have permissions to save EPD options.', 'easy-plugin-demo' ) );
	}

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = epd_get_registered_settings();
	$tab      = isset( $referrer['tab'] )     ? $referrer['tab']     : 'sites';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	epd_save_settings( $_POST['epd_settings'] );

	wp_safe_redirect( add_query_arg( array(
		'page'    => 'epd-settings',
		'tab'     => $tab,
		'section' => $section,
		'updated' => true,
		), network_admin_url( 'settings.php' )
	) );
	exit;
} // epd_save_options_action
add_action( 'admin_init', 'epd_save_options_action' );
