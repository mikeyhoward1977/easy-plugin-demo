<?php
/**
 * Site Actions
 *
 * @package     EPD
 * @subpackage  Functions/Actions/Sites
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove primary blog from front end sites listing.
 *
 * @since   1.2
 * @param   array   $sites  Array of user sites
 * @return  array   Array of user sites
 */
function epd_exclude_primary_site_from_front( $sites )  {
    $primary = get_network()->blog_id;

    if ( ! is_admin() && array_key_exists( $primary, $sites ) )    {
        unset( $sites[ $primary ] );
    }
    
    return $sites;
} // epd_exclude_primary_site_from_front
add_filter( 'get_blogs_of_user', 'epd_exclude_primary_site_from_front', 100 );

/**
 * Validate a new sites domain.
 *
 * @since	1.0
 * @param	string		$domain		Domain for new site
 * @return	string|bool	A validated domain or false
 */
function epd_validate_site_domain( $domain )	{
	if ( ! empty( $domain ) )	{
		if ( preg_match( '|^([a-zA-Z0-9-])+$|', $domain ) ) {
			$domain = strtolower( $domain );
		}

		// If not a subdomain installation, make sure the domain isn't a reserved word
		if ( ! is_subdomain_install() ) {
			$subdirectory_reserved_names = get_subdirectory_reserved_names();

			if ( in_array( $domain, $subdirectory_reserved_names ) ) {
				$domain = false;
			}
		} else	{
			$domain = get_network()->domain;
		}
	}

	return ! empty( $domain ) ? $domain : false;
} // epd_validate_site_domain
add_filter( 'epd_validate_new_site_domain', 'epd_validate_site_domain' );

/**
 * Validate a new sites path.
 *
 * @since	1.0
 * @param	string		$path		Path for new site
 * @param	string		$key		The array key being validated
 * @param	array		$args		Array of key => value pairs for the new site
 * @return	string|bool	A validated path or false
 */
function epd_validate_site_path( $path, $key, $args )	{
	if ( empty( $path ) )	{
		if ( is_subdomain_install() ) {
			$path = get_network()->path;
		} else {
			if ( ! empty( $args['domain'] ) )	{
				$path = get_network()->path . $args['domain'] . '/';
			}
		}
	}

	return ! empty( $path ) ? $path : '/';
} // epd_validate_site_path
add_filter( 'epd_validate_new_site_path', 'epd_validate_site_path', 10, 3 );

/**
 * Validate a new sites title.
 *
 * @since	1.0
 * @param	string		$title		Title for new site
 * @return	string|bool	A validated title or false
 */
function epd_validate_site_title( $title )	{
	return ! empty( $title ) ? sanitize_text_field( strip_tags( $title ) ) : false;
} // epd_validate_site_title
add_filter( 'epd_validate_new_site_title', 'epd_validate_site_title', 10, 3 );

/**
 * Set site defaults.
 *
 * @since	1.0
 * @param	object    $site   WP_Site New site object
 * @return	void
 */
function epd_set_new_site_defaults( $site )	{
	$default_delete = epd_get_default_site_lifetime();

	if ( ! empty( $default_delete ) )	{
		$default_delete = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + epd_get_default_site_lifetime() );
	}

    $allowed_themes = epd_get_option( 'allowed_themes', array() );
    $theme          = wp_get_theme( epd_get_option( 'theme' ) );
    $themes         = array();

    if ( ! empty( $allowed_themes ) )   {
        foreach( $allowed_themes as $allowed_theme )    {
            $_theme = wp_get_theme( $allowed_theme );
            if ( $_theme->exists() )	{
                $themes[ $_theme->stylesheet ] = true;
            }
        }
    }

	if ( ! $theme->exists() )	{
		$theme = wp_get_theme();
	}

    if ( ! array_key_exists( $theme->stylesheet, $themes ) )    {
        $themes[ $theme->stylesheet ] = true;
    }

	$args = array(
        'allowedthemes' => $themes,
		'template'      => $theme->template,
		'stylesheet'    => $theme->stylesheet,
		'blog_public'   => epd_get_option( 'discourage_search' ) ? 0 : 1
	);

	$args = apply_filters( 'epd_set_new_site_defaults', $args, $site );

	foreach( $args as $key => $value )	{
        update_blog_option( $site->blog_id, $key, $value );
	}

} // epd_set_new_site_defaults
add_action( 'wp_initialize_site', 'epd_set_new_site_defaults' );

/**
 * Activate plugins when a new site is registered.
 *
 * @since	1.0
 * @param	object    $site   WP_Site New site object
 * @return	void
 */
function epd_activate_new_blog_plugins( $site )	{
	$plugins = epd_plugins_to_activate();

	if ( ! function_exists( 'is_plugin_active' ) )	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	if ( ! empty( $plugins ) && is_array( $plugins ) )	{
		switch_to_blog( $site->blog_id );
		foreach( $plugins as $plugin )	{
			if ( ! is_plugin_active( $plugin ) )	{
				activate_plugin( $plugin );
			}
		}
		restore_current_blog();
	}
} // epd_activate_new_blog_plugins
add_action( 'wp_initialize_site', 'epd_activate_new_blog_plugins', 11 );

/**
 * Set the blog options for the new site.
 * 
 * @since   1.1.6
 * @param   int     $site_id    Site ID
 * @param   array   $args       Array of args parsed to created site
 * @return  void
 */
function epd_set_blog_meta( $site_id, $args = array() )  {
    $options = epd_get_default_blog_meta();

    if ( ! empty( $args['user_id'] ) )  {
        $options[ 'epd_demo_customer'] = $args['user_id'];
    }

    foreach( $options as $key => $value )   {
        update_site_meta( $site_id, $key, $value );
    }
} // epd_set_blog_meta
add_action( 'epd_create_demo_site', 'epd_set_blog_meta', 10, 2 );

/**
 * Sets the activation key for a site, if needed.
 *
 * This action is hooked via the epd_set_registration_activation_args_action() function.
 *
 * @since	1.4
 * @param	int      $site_id	Site ID
 * @param   array    $args      Array of arguments that were passed to wpmu_create_blog
 * @return	void
 */
function epd_set_site_activation_key_action( $site_id, $args )	{
    update_site_meta( $site_id, 'epd_activation_key', epd_create_site_activation_key( $args['domain'] ) );
} // epd_set_site_activation_key_action

/**
 * Activate a site.
 *
 * @since   1.4
 * @return  void
 */
function epd_activate_site_action() {
    if ( ! isset( $_GET['epd-activation'] ) || ! isset( $_GET['epd-registered'] ) )   {
        return;
    }

    $site_id = absint( $_GET['epd-registered'] );
    $key     = sanitize_text_field( $_GET['epd-activation'] );

    if ( ! epd_activate_site( $site_id, $key ) )  {
        return;
    }

    $user_id = epd_get_site_primary_user_id( $site_id );

    /**
     * Hook into the redirect filters to append the activation confirmed URL param.
     *
     * @since   1.4
     */
    add_filter( 'epd_after_registration_home_redirect_url',    'epd_add_url_param_after_activation_action' );
    add_filter( 'epd_after_registration_admin_redirect_url',   'epd_add_url_param_after_activation_action' );
    add_filter( 'epd_after_registration_confirm_redirect_url', 'epd_add_url_param_after_activation_action' );

    epd_redirect_after_register( $site_id, $user_id );
} // epd_activate_site_action
add_action( 'init', 'epd_activate_site_action' );

/**
 * Reset a site to its original state.
 *
 * @since   1.3
 * @param   int     $site_id    Site ID
 * @return  void
 */
function epd_reset_site_action( $site_id ) {
    if ( ! isset( $_REQUEST['epd_action'] ) || 'reset_site' != $_REQUEST['epd_action'] )	{
		return;
	}

	if ( ! isset( $_REQUEST['epd_nonce'] ) || ! wp_verify_nonce( $_REQUEST['epd_nonce'], 'reset_site' ) )	{
		return;
	}

	if ( ! isset( $_REQUEST['epd_confirm_reset'] ) || '1' != $_REQUEST['epd_confirm_reset'] )	{
		return;
	}

    remove_action( 'admin_init', 'epd_reset_site_action' );

	$redirect = remove_query_arg( array( 'epd_action', 'epd_nonce' ) );
    $redirect = add_query_arg( 'epd-message', 'reset', $redirect );
    $result   = 0;
    $site_id  = ! empty( $_REQUEST['site_id'] ) ? $_REQUEST['site_id'] : false;
    $site_id  = absint( $site_id );
	$site     = get_site( $site_id );

    if ( empty( $site_id ) || get_network()->blog_id == $site_id ) {
        return;
    }

    $reset = is_user_member_of_blog( 0, $site_id ) || is_super_admin();

    if ( $reset )	{
        epd_reset_site( $site_id );
    }
} // epd_reset_site_action
add_action( 'admin_init', 'epd_reset_site_action' );

/**
 * Deletes a site from the front end.
 *
 * @since	1.0
 * @return	void
 */
function epd_delete_site_action()	{
	if ( ! isset( $_GET['epd_action'] ) || 'delete_site' != $_GET['epd_action'] || ! isset( $_GET['site_id'] ) )	{
		return;
	}

	if ( ! isset( $_GET['epd_nonce'] ) || ! wp_verify_nonce( $_GET['epd_nonce'], 'delete_site' ) )	{
		return;
	}

	$site_id     = absint( $_GET['site_id'] );
	$delete_site = is_user_member_of_blog( 0, $site_id );
	$redirect    = remove_query_arg( array( 'epd-registered', 'epd-deleted', 'epd_action', 'epd_nonce' ) );
	$redirect    = add_query_arg( 'epd-message', 'deleted', $redirect );
	$result      = epd_delete_site( $site_id );

	wp_safe_redirect( add_query_arg( 'epd-result', $result, $redirect ) );
	exit;
} // epd_delete_site_action
add_action( 'init', 'epd_delete_site_action' );

/**
 * Delete expired sites.
 *
 * @since	1.0
 * @return	void
 */
function epd_delete_expired_sites()	{
	$now       = current_time( 'timestamp' );
	$lifetime  = epd_get_default_site_lifetime();

	if ( ! $lifetime )	{
		return;
	}

	$delete_on  = $now - $lifetime;
    $exclusions = epd_exclude_sites_from_delete();

	$delete_sites_query = array(
		'site__not_in' => $exclusions,
		'number'       => 250,
		'fields'       => 'ids', // Remove when $backwards_compat is removed
		'meta_query'   => array(
			'relation' => 'OR',
			array(
				'key'     => 'epd_site_expires',
				'value'   => $now,
				'compare' => '<=',
				'type'    => 'NUMERIC'
			),
			array(
				'key'     => 'epd_site_expires',
				'compare' => 'NOT EXISTS',
				'value'   => '#bug'
			)
		)
	);

    $delete_sites_query = apply_filters( 'epd_delete_sites_query', $delete_sites_query );
	$delete_site_ids    = get_sites( $delete_sites_query );
    $exclusions         = array_merge( $exclusions, $delete_site_ids );

	$backwards_compat_query = array(
		'site__not_in' => $exclusions,
		'number'       => 250,
		'fields'       => 'ids',
		'date_query'   => array(
			'year'          => date( 'Y', $delete_on ),
			'month'         => date( 'n', $delete_on ),
			'day'           => date( 'j', $delete_on ),
			'hour'          => date( 'G', $delete_on ),
			'minute'        => intval( date( 'i', $delete_on ) ),
			'second'        => intval( date( 's', $delete_on ) ),
			'compare'       => '<=',
			'column'        => 'registered'
		),
        'meta_query'   => array(
			array(
				'key'     => 'epd_site_expires',
				'compare' => 'NOT EXISTS',
				'value'   => '#bug'
			)
		)
	);

	$backwards_compat_site_ids = get_sites( $backwards_compat_query );
	$site_ids                  = array_unique( array_merge( $delete_site_ids, $backwards_compat_site_ids ) );

    if ( ! empty( $site_ids ) )   {
        $sites = get_sites( array( 'site__in' => $site_ids, 'number' => 250 ) );

        if ( $sites )	{
            require_once( ABSPATH . 'wp-admin/includes/admin.php' );

            $delete_users = array();

            foreach( $sites as $site )	{

                $delete_this_site = apply_filters( 'epd_delete_this_site', true, $site->blog_id );

                if ( $delete_this_site && function_exists( 'wpmu_delete_blog' ) )	{
                    $blog_users = get_users( array( 'blog_id' => $site->blog_id ) );

                    if ( ! empty( $blog_users ) )	{
                        foreach( $blog_users as $blog_user )	{
                            if ( ! is_super_admin( $blog_user->ID ) )	{
                                $delete_users[] = $blog_user->ID;
                            }
                        }
                    }

                    wpmu_delete_blog( $site->blog_id, true );
                }
            }

            if ( ! empty( $delete_users ) )	{
                $delete_users = array_unique( $delete_users );

                foreach( $delete_users as $user_id )	{
                    $user_blogs  = get_blogs_of_user( $blog_user->ID );
					$delete_user = apply_filters( 'epd_delete_site_delete_user', true, $user_id );

                    if ( $delete_user && ( $user_blogs ) )	{
                        wpmu_delete_user( $user_id );
                    }
                }
            }
        }
    }
} // epd_delete_expired_sites
add_action( 'epd_twicedaily_scheduled_events', 'epd_delete_expired_sites' );
