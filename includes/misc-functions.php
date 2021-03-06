<?php
/**
 * Miscellaneous Functions
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve reCAPTCHA version.
 *
 * @since   1.3.13
 * @return  string  reCAPTCHA version
 */
function epd_get_recaptcha_version()    {
    return epd_get_option( 'recaptcha_version', 'v2' );
} // epd_get_recaptcha_version

/**
 * Retrieve the Google reCAPTCHA site key
 *
 * @since	1.0.1
 * @return	bool|string		reCAPTCHA site key or false
 */
function epd_get_google_recaptcha_site_key()	{
	return epd_get_option( 'site_key', false );
} // epd_get_google_recaptcha_site_key

/**
 * Retrieve the Google reCAPTCHA secret key
 *
 * @since	1.0.1
 * @return	bool|string		reCAPTCHA secret key or false
 */
function epd_get_google_recaptcha_secret_key()	{
	return epd_get_option( 'secret_key', false );
} // epd_get_google_recaptcha_secret_key

/**
 * Whether or not to use a Google reCAPTCHA
 *
 * @since	1.0.1
 * @return	bool|array	Array containing site and secret keys or false
 */
function epd_use_google_recaptcha()	{
	$site_key = epd_get_google_recaptcha_site_key();
	$secret   = epd_get_google_recaptcha_secret_key();
    $version  = epd_get_recaptcha_version();

	if ( $site_key && $secret )	{
		return array(
			'site_key' => esc_attr( $site_key ),
			'secret'   => esc_attr( $secret ),
            'version'  => esc_attr( $version )
		);
	}

	return false;
} // epd_use_google_recaptcha

/**
 * Validate reCAPTCHA.
 *
 * @since	1.0.1
 * @param	str		$response	reCAPTCHA response.
 * @return	bool    True if verified, otherwise false
 */
function epd_validate_recaptcha( $response )	{
    $version   = epd_get_recaptcha_version();
	$post_data = http_build_query( array(
        'secret'   => epd_get_google_recaptcha_secret_key(),
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ) );

    $options = array( 'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $post_data
    ) );

    $context  = stream_context_create( $options );
    $response = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
    $result   = json_decode( $response );

    if ( ! empty( $result ) && true == $result->success )	{
		$return = $result->success;

        if ( 'v3' === $version )    {
            $return = $result->action == 'submit_epd_form' && $result->score >= 0.5;
        }

        return $return;
    }

    return false;
} // epd_validate_recaptcha

/**
 * Get the current page URL
 *
 * @since	1.0
 * @return	string	$page_url	Current page URL
 */
function epd_get_current_page_url()	{

	global $wp;

	if ( get_site_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );

	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );

	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	}

	$uri = apply_filters( 'epd_get_current_page_url', $uri );

	return $uri;
} // epd_get_current_page_url

/**
 * Validate the form honeypot to protect against bots.
 *
 * @since	1.0
 * @param	arr		$data	Form post data
 * @return	void
 */
function epd_do_honeypot_check( $data )	{
	if ( ! empty( $data['epd_honeypot'] ) )	{
		wp_die( __( "Ha! I don't think so little honey bee. No bots allowed in this Honey Pot!", 'easy-plugin-demo' ) );
	}
	
	return;
} // epd_do_honeypot_check

/**
 * Display a notice on the front end.
 *
 * @since	1.0
 * @param	string	$m		The notice message key
 * @return	string	The HTML string for the notice
 */
function epd_display_notice( $m )	{	
	$notices = epd_get_notices( $m );

	if ( $notices )	{
		return '<div class="epd_alert epd_alert_' . $notices['class'] . '">' . $notices['notice'] . '</div>';
	}
} // epd_display_notice

/**
 * Front end notices.
 *
 * @since	1.0
 * @param	string		$notice			The message key to display
 * @param	bool	    $notice_only	True to only return the message string, false to return class/notice array
 * @return	arr|string	Notice.
 */
function epd_get_notices( $notice = '', $notice_only = false )	{
	$notices = array(
		'required_fields' => array(
			'class'  => 'error',
			'notice' => __( 'Please complete all required fields.', 'easy-plugin-demo' )
		),
        'no_register' => array(
			'class'  => 'error',
			'notice' => __( 'This user account cannot register any more demo sites.', 'easy-plugin-demo' )
		),
		'recaptcha' => array(
			'class'  => 'error',
			'notice' => __( 'reCAPTCHA validation error.', 'easy-plugin-demo' )
		)
	);

	$notices = apply_filters( 'epd_get_notices', $notices );

	if ( ! empty( $notice ) )	{
		if ( ! array_key_exists( $notice, $notices ) )	{
			return false;
		}

		if ( ! $notice_only )	{
			return $notices[ $notice ];
		} else	{
			return $notices[ $notice ]['notice'];
		}
	}

	return $notices;
} // epd_get_notices

/**
 * Retrieve all dismissed notices.
 *
 * @since	1.0
 * @return	array	Array of dismissed notices
 */
function epd_dismissed_notices() {

	global $current_user;

	$user_notices = (array) get_user_option( 'epd_dismissed_notices', $current_user->ID );

	return $user_notices;

} // epd_dismissed_notices

/**
 * Check if a specific notice has been dismissed.
 *
 * @since	1.0
 * @param	string	$notice	Notice to check
 * @return	bool	Whether or not the notice has been dismissed
 */
function epd_is_notice_dismissed( $notice ) {

	$dismissed = epd_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed ) ) {
		return true;
	} else {
		return false;
	}

} // epd_is_notice_dismissed

/**
 * Dismiss a notice.
 *
 * @since	1.0
 * @param	string		$notice	Notice to dismiss
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function epd_dismiss_notice( $notice ) {

	global $current_user;

	$dismissed_notices = $new = (array) epd_dismissed_notices();

	if ( ! array_key_exists( $notice, $dismissed_notices ) ) {
		$new[ $notice ] = 'true';
	}

	$update = update_user_option( $current_user->ID, 'epd_dismissed_notices', $new );

	return $update;

} // epd_dismiss_notice

/**
 * Restore a dismissed notice.
 *
 * @since	1.0
 * @param	string		$notice	Notice to restore
 * @return	bool|int	True on success, false on failure, meta ID if it didn't exist yet
 */
function epd_restore_notice( $notice ) {

	global $current_user;

	$dismissed_notices = (array) epd_dismissed_notices();

	if ( array_key_exists( $notice, $dismissed_notices ) ) {
		unset( $dismissed_notices[ $notice ] );
	}

	$update = update_user_option( $current_user->ID, 'epd_dismissed_notices', $dismissed_notices );

	return $update;

} // epd_restore_notice

/**
 * Whether or not the Premium Pack is installed.
 *
 * @since   1.3.5
 * @return  bool    True if installed and active, otherwise false
 */
function epd_has_premium_pack() {
    if ( ! function_exists( 'is_plugin_active_for_network' ) )  {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    return is_plugin_active_for_network( 'epd-premium/epd-premium.php' );
} // epd_has_premium_pack

/**
 * Premium extensions data.
 *
 * @since	1.3.10
 * @return	array	Array of premium extension data
 */
function epd_get_premium_extension_data()	{
	$extensions = array(
		'premium_pack' => array(
			'name'         => 'Premium Pack',
			'desc'         => __( 'Power up Easy Plugin Demo with site cloning, demo templates and much more!', 'easy-plugin-demo' ),
			'plugin_url'   => 'epd-premium/epd-premium.php',
			'demo_url'     => 'https://demos.easy-plugin-demo.com',
			'purchase_url' => add_query_arg( array(
                'utm_source'   => 'settings',
                'utm_medium'   => 'wp-admin',
                'utm_campaign' => 'license_notice',
                'utm_content'  => 'licensing'
            ), 'https://easy-plugin-demo.com/downloads/epd-premium-pack/' )
		)
	);

	return $extensions;
} // epd_get_premium_extension_data

/**
 * Retrieve current promotions.
 *
 * @since   1.3.11
 * @param   bool    $active_only    True to retrieve only active promotions
 * @return  array   Array of promotion data, or an empty array
 */
function epd_get_current_promotions( $active_only = true )   {
    $promotions = array(
        'BF2020' => array(
            'name'        => __( 'Black Friday & Cyber Monday', 'easy-plugin-demo' ),
            'campaign'    => 'bfcm2021',
            'image'       => 'bfcm-header.svg',
            'product'     => __( 'EPD Premium Pack', 'easy-plugin-demo' ),
            'start'       => strtotime( '2020-11-29 00:00:00' ),
            'finish'      => strtotime( '2020-12-07 23:59:59' ),
            'timezone'    => 'GMT',
            'discount'    => '40%',
            'cta'         => __( 'Shop Now!', 'easy-plugin-demo' ),
            'cta_url'     => 'https://easy-plugin-demo.com/downloads/epd-premium-pack/'
        ),
        'FLASH2020' => array(
            'name'        => __( 'Flash Sale', 'easy-plugin-demo' ),
            'campaign'    => 'flash-sale',
            'image'       => 'flash-sale-header.svg',
            'product'     => __( 'EPD Premium Pack', 'easy-plugin-demo' ),
            'start'       => strtotime( '2020-12-21 00:00:00' ),
            'finish'      => strtotime( '2021-01-03 23:59:59' ),
            'timezone'    => 'GMT',
            'discount'    => '33%',
            'cta'         => __( 'Shop Now!', 'easy-plugin-demo' ),
            'cta_url'     => 'https://easy-plugin-demo.com/downloads/epd-premium-pack/',
            'description' => __( 'Save %7$s when purchasing the %3$s <strong>now</strong>.<br>Including renewals and upgrades!', 'easy-plugin-demo' )
        ),
        'STAYSAFE' => array(
            'name'        => __( 'Flash Sale', 'easy-plugin-demo' ),
            'campaign'    => 'flash-sale',
            'image'       => 'flash-sale-header.svg',
            'product'     => __( 'EPD Premium Pack', 'easy-plugin-demo' ),
            'start'       => strtotime( '2021-01-14 00:00:00' ),
            'finish'      => strtotime( '2021-02-28 23:59:59' ),
            'timezone'    => 'GMT',
            'discount'    => '33%',
            'cta'         => __( 'Shop Now!', 'easy-plugin-demo' ),
            'cta_url'     => 'https://easy-plugin-demo.com/downloads/epd-premium-pack/',
            'description' => __( 'We are supporting small businesses during the pandemic. Save <strong>%7$s</strong> when purchasing the %3$s <strong>now</strong>. Including renewals and upgrades!', 'easy-plugin-demo' )
        )
    );

    foreach( $promotions as $promotion => $data )  {
        if ( ! empty( $data['description'] ) )  {
            $promotions[ $promotion ]['description'] = sprintf(
                $data['description'],
                $data['name'],
                $data['image'],
                $data['product'],
                $data['start'],
                $data['finish'],
                $data['timezone'],
                $data['discount'],
                $data['cta'],
                $data['cta_url'],
                $data['description']
            );
        }

        if ( $active_only ) {
            $now    = time();
            $start  = $data['start'];
            $finish = $data['finish'];

            if ( ( $now > $start ) && ( $now < $finish ) ) {
                continue;
            }

            unset( $promotions[ $promotion ] );
        }
    }

    return $promotions;
} // epd_get_current_promotions
