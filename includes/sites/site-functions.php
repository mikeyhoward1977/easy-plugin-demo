<?php
/**
 * Site Functions
 *
 * @package     EPD
 * @subpackage  Functions/Sites
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defines the default lifetime of a site.
 *
 * @since	1.0
 * @return	int		Time (in seconds) that a site should exist for before being deleted.
 */
function epd_get_default_site_lifetime()	{
	$lifetime = epd_get_option( 'delete_after' );

	return $lifetime;
} // epd_get_default_site_lifetime

/**
 * Retrieve a sites expiration date.
 *
 * @since	1.0
 * @param	int		$site_id	The site ID
 * @return	string	Time (in seconds) that a site should exist for before being deleted.
 */
function epd_get_site_expiration_date( $site_id )	{
	$lifetime = epd_get_default_site_lifetime();

	if ( ! $lifetime )	{
		$return = __( 'Never', 'easy-plugin-demo' );
	} else	{
		$return = strtotime( get_blog_details( $site_id )->registered );
		$return = $return + $lifetime;
		$return = date_i18n( 'Y-m-d H:i:s', $return );
	}

	$return = apply_filters( 'epd_site_expiration_date', $return, $site_id );

	return $return;
} // epd_get_site_expiration_date

/**
 * Retrieve default site option keys.
 *
 * @since   1.0
 * @return  array   Array of EPD site option keys
 */
function epd_get_default_site_option_keys()	{
	$site_options = array(
		'epd_created_site' => current_time( 'mysql' )
	);

    $site_options = apply_filters( 'epd_default_site_options', $site_options );

    return $site_options;
} // epd_get_default_site_option_keys

/**
 * Retrieve the total number of sites registered via EPD.
 *
 * @since	1.0
 * @return	int		Total number of sites registered via EPD.
 */
function epd_get_registered_demo_sites_count()	{
	$total = get_network_option( get_current_network_id(), 'epd_registered_demo_sites', 0 );
	$total = apply_filters( 'epd_registered_demo_sites_count', $total );

	return (int)$total;
} // epd_get_registered_demo_sites_count

/**
 * Increase total number of sites registered via EPD.
 *
 * @since	1.0
 * @param	int		$count	The count of sites to increase by
 * @return	int		Total number of sites registered via EPD.
 */
function epd_increase_registered_demo_sites_count( $count = 0 )	{
	$count = absint( $count );

	$total = epd_get_registered_demo_sites_count();
	$total = $total + $count;

	update_network_option( get_current_network_id(), 'epd_registered_demo_sites', (int)$total );

	return $total;
} // epd_increase_registered_demo_sites_count

/**
 * Creates a new site within the Multisite installation.
 *
 * @since	1.0
 * @param	array		$args	Array of arguments for the new site
 * @return	int|bool	The new site (blog) ID on success, false on failure
 */
function epd_create_demo_site( $args = array() )	{
	$defaults = array(
		'domain'     => '',
		'path'       => '',
		'title'      => esc_attr( epd_get_option( 'title' ) ),
		'user_id'    => 0,
		'meta'       => array(),
		'network_id' => get_current_network_id()
	);

	$args = wp_parse_args( $args, $defaults );
	$args = epd_validate_new_site_args( $args );

	if ( ! $args )	{
		return false;
	}

	do_action( 'epd_pre_create_demo_site', $args );
	extract( $args );

	$site_id = wpmu_create_blog( $domain, $path, $title, $user_id, $meta, $network_id );

	if ( is_wp_error( $site_id ) )	{
		return false;
	}

	epd_increase_registered_demo_sites_count( 1 );

	do_action( 'epd_create_demo_site', $site_id, $args );

	delete_user_option( $user_id, 'epd_mu_pw', true );

	return $site_id;
} // epd_create_demo_site

/**
 * Get required site arguments.
 *
 * @since	1.0
 * @return	array	Array of site argument keys that are required.
 */
function epd_get_required_new_site_args()	{
	$required_args = array(
		'domain',
		'path',
		'title',
		'user_id'
	);

	return apply_filters( 'epd_required_new_site_args', $required_args );
} // epd_get_required_new_site_args

/**
 * Validate the arguments for a new site.
 *
 * @since	1.0
 * @param	array		$args	Array of arguments for the new site
 * @return	mixed		A validated value or false if validation fails
 */
function epd_validate_new_site_args( $args )	{
	foreach( $args as $key => $value )	{
		$args[ $key ] = apply_filters( "epd_validate_new_site_{$key}", $value, $key, $args );
	}

	$required_args = epd_get_required_new_site_args();

	foreach( $required_args as $required_key )	{
		if ( empty( $args[ $required_key ] ) )	{
			return false;
		}
	}

    return $args;
} // epd_validate_new_site_args
