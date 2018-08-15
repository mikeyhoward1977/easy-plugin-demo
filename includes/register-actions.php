<?php
/**
 * Register Actions
 *
 * @package     EPD
 * @subpackage  Actions/Register
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process registration
 *
 * @since	1.0
 * @return	void
 */
function epd_process_registration_action()	{
	if ( ! isset( $_POST['epd_action'] ) || 'register_user' != $_POST['epd_action'] )	{
		return;
	}

	epd_do_honeypot_check( $_POST );

	$data = array();

	foreach( $_POST as $key => $value )	{
		if ( 'epd_' == substr( $key, 0, 4 ) )	{
			$data[ substr( $key, 4 ) ] = sanitize_text_field( $value );
		}
	}

	$data = apply_filters( 'epd_user_registration_data', $data );
    $user = get_user_by( 'email', $data['email'] );

    if ( $user )    {
        $user_id        = $user->ID;
		$reset_password = sprintf(
			'<a href="%s">%s</a>',
			wp_lostpassword_url(),
			apply_filters( 'epd_reset_password_string',
				__( 'Lost your password?', 'easy-plugin-demo' )
			)
		);

		update_user_option( $user_id, 'epd_mu_pw', $reset_password, true );
    } else  {
        $user_id = epd_create_demo_user( $data );
    }

	if ( $user_id )	{
        $network_id = get_current_network_id();
        $net_domain = get_network()->domain;
        $user       = get_userdata( $user_id );
		$blog       = preg_replace( "/[^A-Za-z0-9 ]/", '', $user->user_login );

		if ( is_subdomain_install() )	{
			$domain = $blog . $net_domain;
			$path   = '/';
            $i      = 1;

            while( domain_exists( $domain, $path, $network_id ) )   {
                $domain = $blog . "-{$i}" . $net_domain;
                $i++;
            }

		} else	{
			$domain = preg_replace( '|^www\.|', '', $net_domain );
			$path   = '/' . $blog . '/';
            $i      = 1;

            while( domain_exists( $domain, $path, $network_id ) )   {
                $path = '/' . $blog . "-{$i}" . '/';
                $i++;
            }
		}

		$args = array(
			'domain'     => $domain,
			'path'       => $path,
			'title'      => esc_attr( epd_get_option( 'title' ) ),
			'user_id'    => $user_id,
			'meta'       => array(),
			'network_id' => $network_id
		);

		$args = apply_filters( 'epd_site_registration_args', $args );

		$blog_id = epd_create_demo_site( $args );
	} else	{
		$blog_id = false;
	}

	if ( $blog_id )	{
        $action = epd_get_option( 'registration_action' );
        $action = apply_filters( 'epd_after_user_registration_action', $action );

        do_action( "epd_after_registration_{$action}_action", $blog_id, $user_id );
	}

} // epd_process_registration_action
add_action( 'init', 'epd_process_registration_action' );

/**
 * Direct a user to the new sites home page after registration.
 *
 * After login, redirect to the new sites home page.
 *
 * @since   1.0
 * @param   int     $blog_id    The blog ID
 * @param   int     $user_id    The user ID
 * @return  void
 */
function epd_redirect_home_after_registration( $blog_id, $user_id )    {
    switch_to_blog( $blog_id );
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

    $redirect_url = apply_filters( 'epd_after_registration_home_redirect_url', get_home_url( $blog_id ) );
    wp_safe_redirect( $redirect_url );
    exit;
} // epd_redirect_home_after_registration
add_action( 'epd_after_registration_home_action', 'epd_redirect_home_after_registration', 100, 2 );

/**
 * Direct a user to admin after registration.
 *
 * After login, redirect to the new sites admin.
 *
 * @since   1.0
 * @param   int     $blog_id    The blog ID
 * @param   int     $user_id    The user ID
 * @return  void
 */
function epd_redirect_admin_after_registration( $blog_id, $user_id )    {
    switch_to_blog( $blog_id );
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

    $redirect_url = apply_filters( 'epd_after_registration_admin_redirect_url', get_admin_url( $blog_id ) );
    wp_safe_redirect( $redirect_url );
    exit;
} // epd_auto_login_after_registration
add_action( 'epd_after_registration_admin_action', 'epd_redirect_admin_after_registration', 100, 2 );

/**
 * Confirmation after registration.
 *
 * After login, reload the registration page and show confirmation.
 *
 * @since   1.0
 * @param   int     $blog_id    The blog ID
 * @param   int     $user_id    The user ID
 * @return  void
 */
function epd_confirm_after_registration( $blog_id, $user_id )    {
    switch_to_blog( $blog_id );
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

	$redirect_url = remove_query_arg( array( 'epd-registered', 'site_id', 'epd-message', 'epd-result' ) );
	$redirect_url = add_query_arg( array(
		'epd-registered' => $blog_id,
		'epd-message'    => 'created'
	) );

    $redirect_url = apply_filters( 'epd_after_registration_confirm_redirect_url', $redirect_url );

    wp_safe_redirect( $redirect_url );
    exit;
} // epd_confirm_after_registration
add_action( 'epd_after_registration_confirm_action', 'epd_confirm_after_registration', 100, 2 );

/**
 * Redirect user to selected page after registration.
 *
 * After login, reload the registration page and show confirmation.
 *
 * @since   1.0
 * @param   int     $blog_id    The blog ID
 * @param   int     $user_id    The user ID
 * @return  void
 */
function epd_redirect_after_registration( $blog_id, $user_id )    {
	$page         = epd_get_option( 'redirect_page', false );
	$redirect_url = get_permalink( $page );

	if ( ! $page || ! $redirect_url )	{
		epd_redirect_home_after_registration( $blog_id, $user_id );
	}

    $redirect_url = apply_filters( 'epd_after_registration_redirect_url', $redirect_url );

    wp_safe_redirect( $redirect_url );
    exit;
} // epd_redirect_after_registration
add_action( 'epd_after_registration_redirect_action', 'epd_redirect_after_registration', 100, 2 );

/**
 * Adds required hidden fields to registration form.
 *
 * @since	1.0
 * @return	string	Hidden input fields
 */
function epd_render_registration_hidden_fields()	{
	global $epd_register_redirect;

	if ( empty( $epd_register_redirect ) ) {
		if ( ! empty( $_GET['epd_redirect'] ) )	{
			$epd_register_redirect = $_GET['epd_redirect'];
		} else	{
			$epd_register_redirect = epd_get_current_page_url();
		}
	}

	$hidden_fields = array(
		'epd_honeypot' => '',
		'epd_redirect' => esc_url( $epd_register_redirect ),
		'action'       => 'epd_validate_registration_form'
	);

	$hidden_fields = apply_filters( 'epd_registration_hidden_fields', $hidden_fields );

	ob_start();

	foreach( $hidden_fields as $field => $value ) : ?>
		<input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>" />
	<?php endforeach;

	echo ob_get_clean();
} // epd_render_registration_hidden_fields
add_action( 'epd_register_form_fields_before_submit', 'epd_render_registration_hidden_fields', 9999 );

/**
 * Load styles and scripts on the front end.
 *
 * @since	1.0
 * @return	void
 */
function epd_load_front_styles_scripts()    {
    $js_dir        = EPD_PLUGIN_URL . 'assets/js/';
	$suffix        = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    $css_file      = 'epd' . $suffix . '.css';
    $templates_dir = epd_get_theme_template_dir_name();

	wp_register_script( 'epd-ajax', $js_dir . 'epd-ajax' . $suffix . '.js', array( 'jquery' ), EPD_VERSION );
	wp_enqueue_script( 'epd-ajax' );

	wp_localize_script( 'epd-ajax', 'epd_vars', apply_filters( 'epd_ajax_vars', array(
        'ajax_loader'             => EPD_PLUGIN_URL . 'assets/images/loading.gif',
		'ajaxurl'                 => epd_get_ajax_url(),
        'submit_register'         => epd_get_register_form_submit_label(),
		'submit_register_loading' => __( 'Please Wait...', 'easy-plugin-demo' )
	) ) );

    $child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $css_file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'epd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $css_file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'epd.css';
	$epd_plugin_style_sheet     = trailingslashit( epd_get_templates_dir()    ) . $css_file;

	/**
     * Look in the child theme directory first, followed by the parent theme, followed by the EPD core templates DIR
	 * Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	 * This allows users to copy just epd.css to their theme
     */
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {

		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'epd.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $css_file;
		}

	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {

		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'epd.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $css_file;
		}

	} elseif ( file_exists( $epd_plugin_style_sheet ) || file_exists( $epd_plugin_style_sheet ) ) {
		$url = trailingslashit( epd_get_templates_url() ) . $css_file;
	}

	wp_register_style( 'epd-styles', $url, array(), EPD_VERSION, 'all' );
	wp_enqueue_style( 'epd-styles' );
} // epd_load_front_styles_scripts
add_action( 'epd_pre_registration_form', 'epd_load_front_styles_scripts' );

/**
 * Display EPD credits below the registration form.
 *
 * @since   1.0
 * @return  string
 */
function epd_display_credits()  {
    if ( epd_get_option( 'credits' ) ) : ?>
        <?php $credit = sprintf(
            __( 'This demo is powered by <a href="%s" target="_blank">Easy Plugin Demo</a> for WordPress', 'easy-plugin-demo' ),
            'https://wordpress.org/plugins/easy-plugin-demo/'
        ); ?>

        <p style="font-size: smaller; font-style: italic; text-align: right;"><?php echo $credit; ?></p>
    <?php endif;
} // epd_display_credits
add_action( 'epd_register_form_bottom', 'epd_display_credits' );
