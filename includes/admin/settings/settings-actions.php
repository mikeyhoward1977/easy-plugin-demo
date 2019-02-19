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

	if ( empty( $_POST['_wp_http_referer'] ) || empty( $_POST['epd_settings'] ) ) {
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

/**
 * Output the options for each supported post type.
 *
 * @since	1.2.9
 * @param	array	$settings	Array of post type setting options
 * @return	Array of post type setting options
 */
function epd_set_post_type_options( $settings )	{
	$post_types   = epd_get_supported_post_types();
	$post_options = array();

	foreach( $post_types as $post_type )	{
		$post_object  = get_post_type_object( $post_type );
		$key          = "replicate_$post_type";

		$post_options[ $key ] = array(
			'id'       => $key,
			'name'     => sprintf( __( 'Replicate %s', 'easy-plugin-demo' ), $post_object->label ),
			'type'     => 'select',
			'multiple' => true,
			'options'  => epd_get_primary_blog_posts( $post_type ),
			'std'      => array(),
			'chosen'   => true,
			'desc'     => sprintf(
				__( 'Select any <strong>%s</strong> that you would like created by default in each new demo site.', 'easy-plugin-demo' ),
				strtolower( $post_object->label )
			)
		);
	}

	return $post_options;
} // epd_set_post_type_options
add_filter( 'epd_posts_pages_options', 'epd_set_post_type_options' );
