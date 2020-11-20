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
 * Process a registration.
 *
 * @since   1.5
 * @param   array   $data   Array of data to process for the registration
 * @return  void
 */
function epd_process_registration( $data )  {
    do_action( 'epd_before_registration' );

    $data = apply_filters( 'epd_user_registration_data', $data );
    $user = get_user_by( 'email', $data['email'] );

    if ( $user )    {
        $user_id        = $user->ID;
		$reset_password = sprintf(
			'<a href="%s">%s</a>',
			wp_lostpassword_url(),
			apply_filters( 'epd_reset_password_string',
				__( 'Lost your password?', 'easy-plugin-demo' )
			)
		);

		update_user_option( $user_id, 'epd_mu_pw', $reset_password, true );
    } else  {
        $user_id = epd_create_demo_user( $data );
    }

	if ( $user_id )	{
        $network_id = get_current_network_id();
        $net_domain = get_network()->domain;
        $user       = get_userdata( $user_id );
		$blog       = preg_replace( "/[^A-Za-z0-9 ]/", '', $user->user_login );

		if ( is_subdomain_install() )	{
			$domain   = $blog . $net_domain;
			$path     = '/';
            $i        = 1;

            while( domain_exists( $domain, $path, $network_id ) )   {
                $domain = $blog . "-{$i}" . $net_domain;
                $i++;
            }
		} else	{
			$domain = preg_replace( '|^www\.|', '', $net_domain );
			$path   = '/' . $blog . '/';
            $i      = 1;

            while( domain_exists( $domain, $path, $network_id ) )   {
                $path = '/' . $blog . "-{$i}" . '/';
                $i++;
            }
		}

		$args = array(
			'domain'     => $domain,
			'path'       => untrailingslashit( get_network()->path ) . $path,
			'title'      => esc_attr( epd_get_option( 'title' ) ),
			'user_id'    => $user_id,
			'meta'       => array(),
			'network_id' => $network_id
		);

		$args = apply_filters( 'epd_site_registration_args', $args );

		$blog_id = epd_create_demo_site( $args );
	} else	{
		$blog_id = false;
	}

	if ( $blog_id )	{
		do_action( 'epd_registration', $blog_id, $user_id, $data );
        epd_redirect_after_register( $blog_id, $user_id );
	}

    return $blog_id;
} // epd_process_registration

/**
 * Retrieve registration action.
 *
 * @since   1.4
 * @param   int     $site_id    ID of new demo site
 * @param   int     $user_id    ID of new demo user
 * @return  string  Registration action
 */
function epd_get_registration_action( $site_id = 0, $user_id = 0 ) {
    $action = epd_get_option( 'registration_action' );
    $action = apply_filters( 'epd_after_user_registration_action', $action, $site_id, $user_id );

    return $action;
} // epd_get_registration_action

/**
 * Redirect user after registration.
 *
 * @since	1.3
 * @param	int		$site_id	The site ID registered
 * @param	int		$user_ud	User ID for the site
 * @return	void
 */
function epd_redirect_after_register( $site_id, $user_id )	{
    if ( ! defined( 'REST_REQUEST' ) )  {
        $action = epd_get_registration_action( $site_id, $user_id );

        do_action( "epd_after_registration_{$action}_action", $site_id, $user_id );
    }
} // epd_redirect_after_register
