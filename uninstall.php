<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

/**
 * Uninstall Easy Plugin Demo.
 *
 * Removes all settings.
 *
 * @package     Easy Plugin Demo
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 *
 */

// Remove Plugin Options
$options = array();

foreach( $options as $option )	{
	delete_option( $option );
}
