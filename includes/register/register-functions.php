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
