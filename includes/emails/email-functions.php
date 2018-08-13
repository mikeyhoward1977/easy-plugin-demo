<?php
/**
 * Email Functions
 *
 * @package     EPD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Email the site details to the user.
 *
 * @since	1.0
 * @param	int		$site_id		Site ID
 * @return	void
 */
function epd_email_registration_confirmation( $site_id, $args ) {

	$user_id = ! empty( $args['user_id'] ) ? (int) $args['user_id'] : false;

	if ( ! $user_id )	{
		return;
	}

	$user = get_userdata( $user_id );

	if ( ! $user )	{
		return;
	}

	$disable = epd_get_option( 'disable_email_confirmation' );
	$disable = apply_filters( 'epd_registration_confirmation_disable_email', $disable, $site_id );

	if ( ! empty( $disable ) )	{
		return;
	}

	$from_name    = epd_get_option( 'from_name' );
	$from_name    = apply_filters( 'epd_registration_from_name', $from_name, $site_id, $user_id );

	$from_email   = epd_get_option( 'from_email' );
	$from_email   = apply_filters( 'epd_registration_from_address', $from_email, $site_id, $user_id );

	$to_email     = $user->user_email;

	$subject      = epd_get_option( 'registration_subject' );
	$subject      = apply_filters( 'epd_registration_subject', wp_strip_all_tags( $subject ), $site_id, $user_id );
	$subject      = epd_do_email_tags( $subject, $site_id, $user_id  );

	$message      = epd_do_email_tags( epd_get_site_registered_email_body_content( $site_id, $user_id ), $site_id, $user_id );
    $attachments  = apply_filters( 'epd_registration_attachments', array(), $site_id, $user_id );

	$emails       = EPD()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );

	$headers = apply_filters( 'epd_registration_headers', $emails->get_headers(), $site_id, $user_id );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

} // epd_email_registration_confirmation
add_action( 'epd_create_demo_site', 'epd_email_registration_confirmation', 100, 2 );
