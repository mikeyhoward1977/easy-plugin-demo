<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     EPD
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get AJAX URL
 *
 * @since	1.0
 * @return	str		URL to the AJAX file to call during AJAX requests.
*/
function epd_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = epd_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'epd_ajax_url', $ajax_url );
} // epd_get_ajax_url

/**
 * Dismiss admin notices.
 *
 * @since	1.0
 * @return	void
 */
function epd_ajax_dismiss_admin_notice()	{

	$notice = sanitize_text_field( $_POST['notice'] );
    epd_dismiss_notice( $notice );

	wp_send_json_success();

} // epd_ajax_dismiss_admin_notice
add_action( 'wp_ajax_epd_dismiss_notice', 'epd_ajax_dismiss_admin_notice' );

/**
 * Validate a registration form.
 *
 * @since	1.0
 * @return	void
 */
function epd_ajax_validate_registration()	{

	$required_fields = epd_get_required_registration_fields();
    $error           = false;
    $field           = '';

	foreach ( $required_fields as $required_field )	{
		if ( empty( $_POST[ $required_field ] ) )	{
			$error = 'required_fields';
		} elseif ( is_email( $_POST[ $required_field ] ) )  {
            $email = sanitize_email( $_POST[ $required_field ] );

            if ( is_email_address_unsafe( $email ) || ! epd_can_user_register( $email ) )    {
                $error = 'no_register';
            }
        }

        if ( $error )	{
            wp_send_json_error( array(
                'error' => epd_get_notices( $error, true ),
                'field' => $required_field
            ) );
        }
	}

	/**
	 * Allow plugins to perform additional form validation.
	 *
	 * @since	1.0
	 */
	$error = apply_filters( 'epd_validate_registration', $error, $_POST );

	if ( $error )	{
        wp_send_json_error( array(
            'error' => epd_get_notices( $error, true ),
            'field' => $field
        ) );
	}

	wp_send_json_success();

} // epd_ajax_validate_registration
add_action( 'wp_ajax_epd_validate_registration_form', 'epd_ajax_validate_registration' );
add_action( 'wp_ajax_nopriv_epd_validate_registration_form', 'epd_ajax_validate_registration' );