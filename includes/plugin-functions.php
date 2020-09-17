<?php
/**
 * Plugin Functions
 *
 * @package     EPD
 * @subpackage  Plugins/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve a list of non-network activated plugins.
 *
 * @since	1.0
 * @return	array	Array of plugins that are not network activated
 */
function epd_get_non_network_enabled_plugins()	{
	$plugins        = get_plugins();
	$not_active     = array();

	foreach( $plugins as $file => $data )	{
		if ( ! is_plugin_active_for_network( $file ) )	{
			$not_active[ $file ] = esc_attr( $data['Name'] );
		}
	}

	return $not_active;
} // epd_get_non_network_enabled_plugins

/**
 * Retrieve plugins that need activating on new sites.
 *
 * @since	1.0
 * @return	array	Array of plugins to activate
 */
function epd_plugins_to_activate()	{
	$plugins = epd_get_option( 'enable_plugins', array() );
	$plugins = apply_filters( 'epd_plugins_to_activate', $plugins );

	return $plugins;
} // epd_plugins_to_activate
