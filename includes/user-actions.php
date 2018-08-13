<?php
/**
 * User Actions
 *
 * @package     EPD
 * @subpackage  Functions/Actions/Users
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Validate a new sites user ID.
 *
 * @since	1.0
 * @param	int           $user_id		User ID for new site
 * @return	string|bool   A validated user_id or false
 */
function epd_validate_site_user_id( $user_id )	{
	return ! empty( $user_id ) ? (int) $user_id : false;
} // epd_validate_site_user_id
add_filter( 'epd_validate_new_site_user_id', 'epd_validate_site_user_id' ); 
