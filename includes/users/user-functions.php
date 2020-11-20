<?php
/**
 * User Functions
 *
 * @package     EPD
 * @subpackage  Functions/Users
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not a user can register.
 *
 * @since   1.0
 * @since	int|string	$user	WP User ID or an email address
 * @return  bool     	True if a user can register, otherwise false
 */
function epd_can_user_register( $user_id )    {
	$can_register = true;

	if ( is_email( $user_id ) )	{
		$user = get_user_by( 'email', $user_id );

		if ( $user )	{
			$user_id = $user->ID;
		}
	}

	if ( $user_id )	{
        if ( user_can( $user_id, 'super_admin' ) )    {
            $can_register = false;
        }

        if ( $can_register )    {
            $current = count( get_blogs_of_user( $user_id, true ) );
            $allowed = epd_get_option( 'max_user_sites' );

            if ( $current >= $allowed ) {
                $can_register = false;
            }
        }
	}

    $can_register = apply_filters( 'epd_can_user_register', $can_register, $user_id );

    return $can_register;
} // epd_can_user_register

/**
 * Creates a new user within the Multisite installation.
 *
 * @since	1.0
 * @param	array		$data	Array of data for the new user
 * @return	int|bool	The new user ID on success, false on failure
 */
function epd_create_demo_user( $data = array() )	{
	$defaults = array(
        'user_name'    => '',
        'email'        => '',
        'first_name'   => '',
        'last_name'    => '',
        'display_name' => ''
    );

    $data = wp_parse_args( $data, $defaults );

    if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
        return false;
    }

    $email = sanitize_email( $data['email'] );

    if ( ! $email ) {
        return false;
    }

    $user_name    = ! empty( $data['user_name'] ) ? sanitize_text_field( $data['user_name'] ) : $email;
    $user_name    = apply_filters( 'epd_demo_user_username', $user_name );
    $first_name   = '';
    $last_name    = '';
    $display_name = '';

    if ( ! empty( $data['first_name'] ) )   {
        $first_name = ucwords( trim( sanitize_text_field( $data['first_name'] ) ) );
    }

    if ( ! empty( $data['last_name'] ) )   {
        $last_name = ucwords( trim( sanitize_text_field( $data['last_name'] ) ) );
    }

    if ( empty( $data['display_name'] ) )   {
        $display_name = $first_name . ' ' . $last_name;
    } else  {
        $display_name = ucwords( trim( sanitize_text_field( $data['display_name'] ) ) );
    }

    $display_name = trim( $display_name );
    $password     = wp_generate_password( 12, false );
    $password     = apply_filters( 'epd_create_demo_uder_password', $password );

    do_action( 'epd_pre_create_demo_user', $user_name, $email, $data );

    $user_id = wpmu_create_user( $user_name, $password, $email );

    if ( ! $user_id )   {
        return false;
    }

	update_user_option( $user_id, 'epd_mu_pw', $password, true );

    $user_data = array();

    if ( ! empty( $first_name ) )   {
        $user_data['first_name'] = $first_name;
    }

    if ( ! empty( $last_name ) )   {
        $user_data['last_name'] = $last_name;
    }

    if ( ! empty( $display_name ) )   {
        $user_data['display_name'] = $display_name;
    }

    if ( ! empty( $user_data ) )    {
        $user_data['ID'] = $user_id;
        wp_update_user( $user_data );
    }

    do_action( 'epd_create_demo_user', $user_id );

    return $user_id;
} // epd_create_demo_user

/**
 * Retrieve the primary user ID for a site.
 *
 * @since	1.3
 * @param	int		$site_id	The ID of the site to retrieve the user for
 * @return	int		Primary user ID
 */
function epd_get_site_primary_user_id( $site_id )	{
	$user_id = get_site_meta( $site_id, 'epd_demo_customer', true );
    $user_id = ! empty( $user_id ) ? absint( $user_id ) : 0;

	return $user_id;
} // epd_get_site_primary_user_id

/**
 * Defines the role required to reset a site.
 *
 * @since   1.3
 * @return  string
 */
function epd_get_reset_site_cap_role()  {
    $role = 'manage_options';
    $role = apply_filters( 'epd_reset_site_cap_role', $role );

    return $role;
} // epd_get_reset_site_cap_role
