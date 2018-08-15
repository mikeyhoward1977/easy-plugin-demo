<?php
/**
 * Admin Pages
 *
 * @package     EPD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Register the 'Expires' column within the sites list table.
 *
 * @since	1.0
 * @param	array	$columns	Array of table columns
 * @return	array	Array of table columns
 */
function epd_sites_expires_column( $columns )	{
	$columns['expires'] = __( 'Expires', 'easy-plugin-demo' );

	return $columns;
} // epd_sites_expires_column
add_filter( 'wpmu_blogs_columns', 'epd_sites_expires_column' );

/**
 * Renders the site expiry date within the the sites list table 'Expires' column.
 *
 * @since	1.0
 * @param	string	$column_name	Current column name
 * @param	int		$blog_id		ID of the current blog/site
 * @return	string	Output for column
 */
function epd_render_sites_expires_column( $column_name, $blog_id )	{
	if ( 'expires' == $column_name )	{
		global $mode;

		$blog      = get_site( $blog_id );
		$lifetime  = epd_get_default_site_lifetime();
		$format    = 'Y/m/d ' . get_option( 'time_format' );
		$expires   = false;

		if ( get_network()->blog_id == $blog_id )	{
			$return = '&ndash;';
		} elseif ( ! $lifetime )	{
			$return = __( 'Never', 'easy-plugin-demo' );
		} else	{
			$expires = strtotime( '+' . $lifetime . ' seconds', strtotime( $blog->registered ) );
			$expires = date( 'Y-m-d, H:i:s', $expires );
			$return  = mysql2date( $format, $expires );
		}

		$return = apply_filters( 'epd_sites_expires_column', $return, $blog );

		echo $return;
	}
} // epd_render_sites_expires_column
add_action( 'manage_sites_custom_column', 'epd_render_sites_expires_column', 10, 2 );
