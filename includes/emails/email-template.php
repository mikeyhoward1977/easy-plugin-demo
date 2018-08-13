<?php
/**
 * Email Template
 *
 * @package     EPD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the current email template.
 *
 * This is simply a wrapper to EPD_Email_Templates->get_template()
 *
 * @since	1.0
 * return	string
 */
function epd_get_email_template()	{
	$template = new EPD_Emails;
	return $template->get_template();
} // epd_get_email_template

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to EPD_Email_Templates->get_templates()
 *
 * @since	1.0
 * @return	array	All the registered email templates
 */
function epd_get_email_templates()   {
	$templates = new EPD_Emails;
	return $templates->get_templates();
} // epd_get_email_templates

/**
 * Email Template Tags
 *
 * @since	1.0
 *
 * @param	string	$message		Message with the template tags
 * @param	int		$blod_id		Blog ID
 * @param	int		$user_id		User ID
 * @return	string	$message		Fully formatted message
 */
function epd_email_template_tags( $message, $blog_id, $user_id )     {
	return epd_do_email_tags( $message, $blog_id, $user_id );
} // epd_email_template_tags

/**
 * Site Registered Email Template Body.
 *
 * This is the default content sent to the user when a new site is registered.
 *
 * @since	1.0
 * @param	int 	$blog_id		Blog ID
 * @param	array	$user_id		User ID
 * @param	array	$data			Registration Data
 * @return	string	$email_body		Body of the email
 */
function epd_get_site_registered_email_body_content( $blog_id = 0, $user_id = 0 ) 	{

	$registered_email_body = sprintf( __( 'Hey %s!', 'easy-plugin-demo' ), '{demo_name}' ) . "\n\n";
	$registered_email_body .= sprintf( __( 'Your %s demo site is ready and waiting for you. You can use the following information to get started...', 'easy-plugin-demo' ), '{demo_product_name}' ) . "\n\n";
	$registered_email_body .= '<ul>' . "\n\n";
	$registered_email_body .= '<li>' . sprintf( __( 'URL: %s', 'easy-plugin-demo' ), '{demo_site_url}' ) . '</li>' . "\n\n";
	$registered_email_body .= '<li>' . sprintf( __( 'Username: %s', 'easy-plugin-demo' ), '{demo_site_user_login}' ) . '</li>' . "\n\n";
	$registered_email_body .= '<li>' . sprintf( __( 'Password: %s', 'easy-plugin-demo' ), '{demo_site_password}' ) . '</li>' . "\n\n";
	$registered_email_body .= '</ul>' . "\n\n";
	$registered_email_body .= sprintf( __( 'Your demo site will be deleted shortly after %s.', 'easy-plugin-demo' ), '{demo_site_expiration}' ) . "\n\n";
	$registered_email_body .= __( 'Regards', 'easy-plugin-demo' ) . "\n\n";
	$registered_email_body .= wp_specialchars_decode( get_network()->site_name, ENT_QUOTES ) . "\n\n";

	$email = epd_get_option( 'registration_content', false );
	$email = $email ? stripslashes( $email ) : $registered_email_body;

	$email_body = apply_filters( 'epd_site_registered_email_template_wpautop', true ) ? wpautop( $email ) : $email;

	$email_body = apply_filters( 'epd_site_registered_email_content_' . EPD()->emails->get_template(), $email_body, $blog_id, $user_id );

	return apply_filters( 'epd_site_registered_email_body_content', $email_body, $user_id );

} // epd_get_site_registered_email_body_content
