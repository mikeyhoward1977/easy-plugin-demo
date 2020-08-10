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
 * Get the lifetime of a site.
 *
 * @since	1.2
 * @param	int		$site_id	Site ID
 * @return	int		Lifetime of site (in seconds)
 */
function epd_get_site_lifetime( $site_id )	{
	$lifetime = get_site_meta( $site_id, 'epd_site_lifetime', true );
	$lifetime = '' == $lifetime ? epd_get_default_site_lifetime() : $lifetime;
	$lifetime = apply_filters( 'epd_site_lifetime', $lifetime );

	return $lifetime;
} // epd_get_site_lifetime

/**
 * Defines the default lifetime of a site.
 *
 * @since	1.0
 * @return	int		Time (in seconds) that a site should exist for before being deleted.
 */
function epd_get_default_site_lifetime()	{
	$lifetime = epd_get_option( 'delete_after' );
    $lifetime = apply_filters( 'epd_site_lifetime', $lifetime );

	return $lifetime;
} // epd_get_default_site_lifetime

/**
 * Get the date/time the site was registered.
 *
 * @since	1.2
 * @param	int		$site_id	Site ID
 * @param	string	$format		Date format to be returned
 * @return	string	Date/Time the site was registered
 */
function epd_get_site_registered_time( $site_id, $format = '' )	{
	$registered = strtotime( get_blog_details( $site_id )->registered );
	$format     = empty( $format ) ? 'Y/m/d ' . get_option( 'time_format' ) : $format;

	return wp_date( $format, $registered );
} // epd_get_site_registered_time

/**
 * Retrieve a sites expiration timestamp.
 *
 * @since	1.0
 * @param	int		$site_id	The site ID
 * @return	string	Timestamp for when a site should be deleted.
 */
function epd_get_site_expiration_timestamp( $site_id )	{
	$expires = get_site_meta( $site_id, 'epd_site_expires', true );
	$expires = apply_filters( 'epd_site_expiration_timestamp', $expires );

	return $expires;
} // epd_get_site_expiration_timestamp

/**
 * Retrieve a sites expiration date.
 *
 * @since	1.0
 * @param	int		$site_id	The site ID
 * @param	string	$format		Date format to be returned
 * @return	string	Time (in seconds) that a site should exist for before being deleted.
 */
function epd_get_site_expiration_date( $site_id, $format = '' )	{
	$default_lifetime = epd_get_default_site_lifetime();
	$expiration       = epd_get_site_expiration_timestamp( $site_id );
	$format           = empty( $format ) ? 'Y/m/d ' . get_option( 'time_format' ) : $format;

	if ( '' == $expiration )	{
		$expiration = strtotime( get_blog_details( $site_id )->registered );
		$expiration = $expiration + $default_lifetime;
	}

	if ( empty( $expiration ) )	{
		$return = false;
	} else	{
		$return = date_i18n( $format, $expiration );
	}

	$return = apply_filters( 'epd_site_expiration_date', $return, $site_id );

	return $return;
} // epd_get_site_expiration_date

/**
 * Whether or not a site has expired.
 *
 * @since   1.2
 * @param   int     $site_id    Site ID
 * @return  bool    Whether or not the site has expired
 */
function epd_site_has_expired( $site_id )   {
    $expires = strtotime( epd_get_site_expiration_date( $site_id ) );
    $current = current_time( 'timestamp' );

    if  ( empty( $expires ) )   {
        return false;
    }

    return $current > $expires;
} // epd_site_has_expired

/**
 * Whether or not sites can be reset.
 *
 * @since   1.2
 * @return  bool    Whether or not sites can be reset
 */
function epd_can_reset_sites()   {
    $reset = epd_get_option( 'allow_reset', false );

    return $reset;
} // epd_can_reset_sites

/**
 * Retrieve default site option keys.
 *
 * @since   1.0
 * @return  array   Array of EPD site option keys
 */
function epd_get_default_blog_meta()	{
	$site_options = array(
        'epd_site_lifetime' => epd_get_default_site_lifetime(),
		'epd_site_expires'  => current_time( 'timestamp' ) + epd_get_default_site_lifetime()
	);

    $site_options = apply_filters( 'epd_default_blog_meta', $site_options );

    return $site_options;
} // epd_get_default_blog_meta

/**
 * Retrieve default site option keys.
 *
 * @since   1.0
 * @return  array   Array of EPD site option keys
 */
function epd_get_default_site_option_keys() {
    return epd_get_default_blog_meta();
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
 * Retrieve the total number of times a site has been reset.
 *
 * @since	1.3
 * @param   int     $site_id    Site ID
 * @return	int		Total number of times the site has been reset
 */
function epd_get_site_reset_count( $site_id = 0 )	{
    $site_id = ! empty( $site_id ) ? $site_id : get_current_blog_id();

	$total = get_site_meta( $site_id, 'epd_site_reset_count', true );
	$total = apply_filters( 'epd_site_reset_count', $total );

	return (int)$total;
} // epd_get_site_reset_count

/**
 * Increase total number of times a site has been reset.
 *
 * @since	1.0
 * @param   int     $site_id    Site ID
 * @param	int		$count	The count of resets to increase by
 * @return	int		Total number of times the site has been reset
 */
function epd_increase_demo_site_reset_count( $site_id = 0, $count = 0 )	{
    $site_id = ! empty( $site_id ) ? $site_id : get_current_blog_id();
	$count   = absint( $count );

	$total = epd_get_site_reset_count( $site_id );
	$total = $total + $count;

	update_site_meta( $site_id, 'epd_site_reset_count', $total );

	return $total;
} // epd_increase_demo_site_reset_count

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

	$increment_count = apply_filters( 'epd_increment_site_count', true );

	if ( $increment_count )	{
		epd_increase_registered_demo_sites_count( 1 );
	}

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

/**
 * Get sites excluded from deletion.
 *
 * @since   1.2
 * @return  array   Array of site IDs to exclude.
 */
function epd_exclude_sites_from_delete()    {
    global $wpdb;

    $excludes = array( get_network()->blog_id );
    $where    = "WHERE meta_key = 'epd_site_expires'";
    $where   .= "AND meta_value = '0'";
    $where    = apply_filters( 'epd_exclude_sites_from_delete_where', $where );

    $exclusions = $wpdb->get_results( 
        "
        SELECT blog_id as site_id
        FROM $wpdb->blogmeta
        $where
        "
    );

    foreach( $exclusions as $exclusion )    {
        $excludes[] = $exclusion->site_id;
    }

    /**
     * Allow filtering of the exclusions list.
     *
     * @since   1.2
     * @param   array   $exclusions Array of site ID's to exclude
     */
    $excludes = apply_filters( 'epd_delete_expired_sites_exclusions', $excludes );

    return $excludes;
} // epd_exclude_sites_from_delete

/**
 * Delete a site.
 *
 * @since	1.3
 * @param	int		$site_id	The ID of the site to be deleted
 * @return	bool	True if the site is deleted, otherwise false
 */
function epd_delete_site( $site_id )	{
	require_once( ABSPATH . 'wp-admin/includes/admin.php' );

	$delete_users = array();
	$result       = false;

	if ( function_exists( 'wpmu_delete_blog' ) )	{
		$blog_users = get_users( array( 'blog_id' => $site_id ) );

		if ( ! empty( $blog_users ) )	{
			foreach( $blog_users as $blog_user )	{
				if ( ! is_super_admin( $blog_user->ID ) )	{
					$delete_users[] = $blog_user->ID;
				}
			}
		}

		wpmu_delete_blog( $site_id, true );

		$delete_users = apply_filters( 'epd_delete_site_delete_users', $delete_users, $site_id );

		if ( ! empty( $delete_users ) )	{
			$delete_users = array_unique( $delete_users );

			foreach( $delete_users as $user_id )	{
				$user_blogs = get_blogs_of_user( $user_id );

				if ( empty( $user_blogs ) )	{
					if ( $user_id == get_current_user_id() )	{
						wp_logout();
					}

					wpmu_delete_user( $user_id );
				}
			}
		}

		$result = true;
	}

	return $result;
} // epd_delete_site

/**
 * Reset a site to its original state.
 *
 * @since	1.3
 * @param	int		$site_id	The ID of the site to reset
 * @return	bool	True if successful, false if the site was not reset
 */
function epd_reset_site( $site_id )	{
	$epd_reset = new EPD_Reset_Site( $site_id );

	$epd_reset->execute();
} // epd_reset_site
