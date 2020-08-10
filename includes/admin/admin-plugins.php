<?php
/**
 * Admin Plugins
 *
 * @package     EPD
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2207, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Plugin row meta links
 *
 * @since	1.1.4
 * @param	array	$input	Defined meta links
 * @param	string	$file	Plugin file path and name being processed
 * @return	array	Filtered meta links
 */
function epd_plugin_row_meta( $input, $file )	{

	if ( $file == 'easy-plugin-demo/easy-plugin-demo.php' && ! get_site_option( 'epd_premium_version' ) )    {
        $links = array(
            '<a href="' . esc_url( 'https://easy-plugin-demo.com/downloads/epd-premium-pack/' ) . '" target="_blank"><strong>' . esc_html__( 'Get Premium Pack', 'easy-plugin-demo' ) . '</strong></a>'
        );

        $input = array_merge( $input, $links );
    }

	return $input;

} // epd_plugin_row_meta
add_filter( 'plugin_row_meta', 'epd_plugin_row_meta', 10, 2 );
