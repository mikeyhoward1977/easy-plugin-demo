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
            $_theme = wp_get_theme( epd_get_option( 'theme' ) );
            if ( ! $_theme->exists() || ! $_theme->is_allowed( 'site', $site->blog_id ) )	{
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

	$args = apply_filters( 'epd_set_new_site_defaults', $args );

	$site_options = epd_get_default_site_option_keys();

	foreach( $args as $key => $value )	{
		if ( in_array( $key, $site_options ) )	{
			update_network_option( $site->blog_id, $key, $value );
		} else	{
			update_blog_option( $site->blog_id, $key, $value );
		}
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

	require_once( ABSPATH . 'wp-admin/includes/admin.php' );

	$delete_users = array();
	$site_id      = absint( $_GET['site_id'] );
	$delete_site  = is_user_member_of_blog( 0, $site_id );
	$redirect     = remove_query_arg( array( 'epd-registered', 'epd-deleted', 'epd_action', 'epd_nonce' ) );
	$redirect     = add_query_arg( 'epd-message', 'deleted', $redirect );

	if ( $delete_site && function_exists( 'wpmu_delete_blog' ) )	{
		$blog_users = get_users( array( 'blog_id' => $site_id ) );

		if ( ! empty( $blog_users ) )	{
			foreach( $blog_users as $blog_user )	{
				if ( ! is_super_admin( $blog_user->ID ) )	{
					$delete_users[] = $blog_user->ID;
				}
			}
		}

		wpmu_delete_blog( $site_id, true );

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

		$result  = 1;
	} else	{
		$result  = 0;
	}

	wp_safe_redirect( add_query_arg( 'epd-result', $result, $redirect ) );
	exit;
} // epd_delete_site_action
add_action( 'init', 'epd_delete_site_action' );

/**
 * Remove default site option meta whena blog it deleted.
 *
 * @since   1.0
 * @param   object     $site    WP_Site The old site object
 * @return  void
 */
function epd_deleted_site_delete_default_meta( $site )    {
    global $wpdb;

    $site_options = epd_get_default_site_option_keys();
    $site_id      = $site->blog_id;

	if ( empty( $site_options ) )	{
		return;
	}

    $where = '(';
    $i     = false;

    foreach( $site_options as $site_option )    {
        if ( $i > 0 )   {
            $where .= " OR ";
        }

        $where .= "`meta_key` = '{$site_option}'";

        $i++;
    }

    $where .= ')';

    $wpdb->query(
        "
        DELETE FROM
        $wpdb->sitemeta
        WHERE site_id = '{$site_id}'
        AND {$where}
        "
    );
} // epd_deleted_site_delete_default_meta
add_action( 'wp_delete_site', 'epd_deleted_site_delete_default_meta', 10 );

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

	$delete_on = $now - $lifetime;

	$delete_sites_query = array(
		'site__not_in' => get_network()->blog_id,
		'date_query'   => array(
			array(
				'year'          => date( 'Y', $delete_on ),
				'month'         => date( 'n', $delete_on ),
				'day'           => date( 'j', $delete_on ),
				'hour'          => date( 'G', $delete_on ),
				'minute'        => intval( date( 'i', $delete_on ) ),
				'second'        => intval( date( 's', $delete_on ) ),
				'compare'       => '<=',
				'column'        => 'registered'
			)
		)
	);

	$delete_sites_query = apply_filters( 'epd_delete_sites_query', $delete_sites_query );

	$sites = get_sites( $delete_sites_query );

	require_once( ABSPATH . 'wp-admin/includes/admin.php' );

	if ( $sites )	{
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
				$user_blogs = get_blogs_of_user( $blog_user->ID );

				if ( empty( $user_blogs ) )	{
					wpmu_delete_user( $user_id );
				}
			}
		}

	}
} // epd_delete_expired_sites
add_action( 'epd_twicedaily_scheduled_events', 'epd_delete_expired_sites' );
