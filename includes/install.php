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

    // Adds the registration completed page
    $registration_page = wp_insert_post( array(
        'post_content' => epd_get_default_registration_complete_page_text(),
        'post_title'   => __( 'Registration Completed', 'easy-plugin-demo' ),
        'post_status'  => 'publish',
        'post_type'    => 'page'
    ) );

    // Setup some default options
	$options = array();

	// Pull options from WP, not EPD's global
	$current_options = get_site_option( 'epd_settings', array() );
    $settings        = epd_get_registered_settings();

	// Populate some default values
	foreach( $settings as $setting ) {
        if ( 'registration_complete' == $setting['id'] && $registration_page )    {
            $options[ $setting['id'] ] = $registration_page;
        } elseif ( ! empty( $setting['std'] ) ) {
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
 * The registration completed page text template.
 *
 * @since   1.0
 * @return  string  Default text for registration completion page
 */
function epd_get_default_registration_complete_page_text()   {
    $default_text  = '<h2>' . __( "You're all set!", 'easy-plugin-demo' ) . '</h2>' . "\r\n";
    $default_text .= '<p>';
    $default_text .= __( 'Good news {demo_first_name}, your {demo_site_name} is ready for you.', 'easy-plugin-demo');
    $default_text .= '</p>';
    $default_text .= '<p>';
    $default_text .= __( "You can access your demo via {demo_site_url}. Your username and password have been sent to your email address (be sure to check your junk folders if you dont's see it).", 'easy-plugin-demo');
    $default_text .= '</p>';

    $default_text = apply_filters( 'epd_default_registration_complete_page_text', $default_text );

    return $default_text;
} // epd_get_default_registration_complete_page_text

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
