<?php
/**
 * Shortcode Functions
 *
 * @package     EPD
 * @subpackage  Functions/Shortcodes
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Register the EPD Register shortcode.
 *
 * @since   1.0
 * @param	array	$atts	Shortcode attributes
 * @return  string  The registration form output
 */
function epd_add_epd_register_shortcode( $atts )   {
	$supported_atts = apply_filters(
		'epd_register_shortcode_atts',
		array( 'redirect' => '' )
	);

    $args = shortcode_atts( $supported_atts, $atts, 'epd_register' );

    do_action( 'epd_registration_form' );

    return epd_registration_form( $args['redirect'] );
} // epd_add_epd_register_shortcode
add_shortcode( 'epd_register', 'epd_add_epd_register_shortcode' );
