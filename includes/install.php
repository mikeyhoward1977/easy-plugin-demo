<?php
/**
 * Install Function
 *
 * @package     EPD
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Install
 *
 * Runs on plugin install.
 *
 * @since	1.0
 * @global	$wpdb
 * @global	$wp_version
 * @param 	bool	$network_side	If the plugin is being network-activated
 * @return	void
 */
function epd_install( $network_wide = false ) {
	global $wpdb;

	if ( ! is_multisite() )	{
		exit( __( 'Easy Plugin Demo requires WordPress multisite.', 'easy-plugin-demo' ) );
	}

	if ( ! $network_wide ) {
        exit( __( 'Easy Plugin Demo must be activated network wide.', 'easy-plugin-demo' ) );
	}

	epd_run_install();

	// Cron
	wp_schedule_event( time(), 'twicedaily', 'epd_twicedaily_scheduled_events' );
} // epd_install
register_activation_hook( EPD_PLUGIN_FILE, 'epd_install' );

/**
 * Run the EPD Install process
 *
 * @since	1.0
 * @return	void
 */
function epd_run_install() {
	global $wpdb, $epd_options, $wp_version;

	// Bail if already installed
	$already_installed = get_site_option( 'epd_installed' );
	if ( $already_installed )	{
		return;
	}

    // Add Upgraded From Option
	$current_version = get_site_option( 'epd_version' );
	if ( $current_version ) {
		update_site_option( 'epd_version_upgraded_from', $current_version );
	}

    // Setup some default options
	$options = array();

	// Pull options from WP, not EPD's global
	$current_options = get_site_option( 'epd_settings', array() );
    $settings        = epd_get_registered_settings();

	// Populate some default values
	foreach( $settings as $setting ) {
        if ( ! empty( $setting['std'] ) ) {
            if ( 'checkbox' == $setting['type'] )	{
                $options[ $setting['id'] ] = '1';
            } else	{
                $options[ $setting['id'] ] = $setting['std'];
            }

        }
	}

    $merged_options = array_merge( $epd_options, $options );
	$epd_options    = $merged_options;

    update_site_option( 'epd_settings', $merged_options );
	update_site_option( 'epd_version', EPD_VERSION );
	add_site_option( 'epd_install_version', EPD_VERSION );
	add_site_option( 'epd_installed', current_time( 'mysql' ) );
	add_site_option( 'epd_registered_demo_sites', 0 );

    // Non EPD options
    update_site_option( 'registration', 'none' );
} // epd_run_install

/**
 * Deactivate
 *
 * Runs on plugin deactivation to remove scheduled tasks.
 *
 * @since	1.0
 * @return	void
 */
function epd_deactivate_plugin()	{
	wp_clear_scheduled_hook( 'epd_twicedaily_scheduled_events' );
} // epd_deactivate_plugin
register_deactivation_hook( EPD_PLUGIN_FILE, 'epd_deactivate_plugin' );
