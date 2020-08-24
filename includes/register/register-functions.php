<?php
/**
 * Register Functions
 *
 * @package     EPD
 * @subpackage  Functions/Register
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get the registration page URL.
 *
 * @since   1.3
 * @return  string  URL of registration page
 */
function epd_get_registration_page_url()    {
    switch_to_blog( get_network()->blog_id );
    $registration_url = get_permalink( epd_get_option( 'registration_page' ) );
    restore_current_blog();

    return $registration_url;
} // epd_get_registration_page_url

/**
 * Determine if the current page is the registration page.
 *
 * @since	1.2
 * @return	bool	True if registration page, or false.
 */
function epd_is_register_page()	{
	$is_register = is_page( epd_get_option( 'registration_page' ) );

	if ( ! $is_register )	{
		global $post;

		if ( ! empty( $post ) )	{
			$is_register = has_shortcode( $post->post_content, 'epd_register' );
		}
	}

	return apply_filters( 'epd_is_register_page', $is_register );
} // epd_is_register_page

/**
 * Define the registration submit button label
 *
 * @since	1.0
 * @return	string	Label for registration form submit button
 */
function epd_get_register_form_submit_label()	{
	$label = __( 'Launch my Demo', 'easy-plugin-demo' );
	$label = apply_filters( 'epd_register_form_submit_label', $label );

	return $label;
} // epd_get_register_form_submit_label

/**
 * Declare required registration form fields.
 *
 * @since	1.0
 * @return	array	Array of required field names
 */
function epd_get_required_registration_fields()	{
	$required = array(
		'epd_first_name',
		'epd_last_name',
		'epd_email'
	);

	$required = apply_filters( 'epd_required_registration_fields', $required );

	return $required;
} // epd_get_required_registration_fields

/**
 * Display the registration Form
 *
 * @since	1.0
 * @param	string	$redirect	Redirect page URL
 * @return	string	Login form
 */
function epd_registration_form( $redirect = '' ) {
	global $epd_register_redirect;

	if ( empty( $redirect ) ) {
		if ( ! empty( $_GET['epd_redirect'] ) )	{
			$redirect = $_GET['epd_redirect'];
		} else	{
			$redirect = epd_get_current_page_url();
		}
	}

	$epd_register_redirect = $redirect;

	ob_start();

    do_action( 'epd_pre_registration_form' );

	epd_get_template_part( 'shortcode', 'register' );

	return apply_filters( 'epd_registration_form', ob_get_clean() );
} // epd_registration_form

/**
 * Redirect user after registration.
 *
 * @since	1.3
 * @param	int		$site_id	The site ID registered
 * @param	int		$user_ud	User ID for the site
 * @return	void
 */
function epd_redirect_after_register( $site_id, $user_id )	{
	$action = epd_get_option( 'registration_action' );
	$action = apply_filters( 'epd_after_user_registration_action', $action );

	do_action( "epd_after_registration_{$action}_action", $site_id, $user_id );
} // epd_redirect_after_register
