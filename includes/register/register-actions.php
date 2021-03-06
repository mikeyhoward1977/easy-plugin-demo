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
 * Process registration action.
 *
 * Prepares the data and initiates the registration process.
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

    epd_process_registration( $data );
} // epd_process_registration_action
add_action( 'init', 'epd_process_registration_action' );

/**
 * Filter new site args to apply activation settings if needed.
 *
 * @since	1.3.4
 * @param	array	$args	New site arguments
 * @return	array	New site arguments
 */
function epd_set_registration_activation_args_action( $args )	{
	if ( epd_new_sites_need_activating() )	{
		$args['meta']['public']         = 0;
		$args['meta']['archived']       = 1;

		/**
		 * Hook in to define the activation key.
		 *
		 * @since	1.3.4
		 */
		add_action( 'epd_create_demo_site', 'epd_set_site_activation_key_action', 10, 2 );
	}

	return $args;
} // epd_set_registration_activation_args_action
add_filter( 'epd_site_registration_args', 'epd_set_registration_activation_args_action' );

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
    epd_process_auto_user_login( $user_id, $user_id );

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
    epd_process_auto_user_login( $user_id, $user_id );

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
    epd_process_auto_user_login( $user_id, $user_id );

	$message = is_archived( $blog_id ) ? 'pending' : 'created';

	$redirect_url = remove_query_arg( array( 'epd-registered', 'site_id', 'epd-message', 'epd-result' ) );

	$redirect_url = add_query_arg( array(
		'epd-registered' => $blog_id,
		'epd-message'    => $message
	) );

    $redirect_url = apply_filters( 'epd_after_registration_confirm_redirect_url', $redirect_url );

    wp_safe_redirect( $redirect_url );
    exit;
} // epd_confirm_after_registration
add_action( 'epd_after_registration_confirm_action', 'epd_confirm_after_registration', 100, 2 );

/**
 * Adds an additional paramater to the URL following redirection when a site is activated.
 *
 * This function is hooked from the  epd_activate_site_action() function.
 *
 * @since   1.3.4
 * @param   string  $redirect_url   Redirect URL
 * @return  string  Redirect URL
 */
function epd_add_url_param_after_activation_action( $redirect_url )    {
    $redirect_url = remove_query_arg( 'epd-activation', $redirect_url );
    $redirect_url = add_query_arg( 'epd-activated', 1, $redirect_url );
    $redirect_url = apply_filters( 'epd_after_activation_redirect_url', $redirect_url );

    return $redirect_url;
} // epd_add_url_param_after_activation

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

    $redirect_url = apply_filters( 'epd_after_registration_redirect_url', $redirect_url, $blog_id );

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
    $use_recaptcha = epd_use_google_recaptcha();

    $script_args = array(
        'ajax_loader'             => EPD_PLUGIN_URL . 'assets/images/loading.gif',
		'ajaxurl'                 => epd_get_ajax_url(),
        'submit_register'         => epd_get_register_form_submit_label(),
		'submit_register_loading' => __( 'Please Wait...', 'easy-plugin-demo' )
	);

    if ( $use_recaptcha )   {
        $script_args['recaptcha_version'] = $use_recaptcha['version'];

        if ( 'v3' === $use_recaptcha['version'] )   {
            $script_args['recaptcha_site_key'] = $use_recaptcha['site_key'];
        }
    }

	wp_register_script(
        'epd-ajax',
        $js_dir . 'epd-ajax' . $suffix . '.js',
        array( 'jquery' ),
        EPD_VERSION
    );
	wp_enqueue_script( 'epd-ajax' );

	wp_localize_script( 'epd-ajax', 'epd_vars', apply_filters( 'epd_ajax_vars', $script_args ) );

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

	if ( $use_recaptcha )	{
        $script = 'https://www.google.com/recaptcha/api.js';

        if ( 'v3' === $use_recaptcha['version'] )    {
            $script = add_query_arg( 'render', $use_recaptcha['site_key'], $script );
        }

		wp_register_script( 'google-recaptcha', $script );
		wp_enqueue_script( 'google-recaptcha' );
	}
} // epd_load_front_styles_scripts
add_action( 'epd_pre_registration_form', 'epd_load_front_styles_scripts' );

function epd_insert_recaptcha_script_for_registration_form()	{
	$recaptcha = epd_use_google_recaptcha();

	if ( ! $recaptcha )	{
		return;
	}

	ob_start(); ?>

    <?php if ( 'v3' === $recaptcha['version'] ) : ?>
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="" />
        <input type="hidden" name="epd_recaptcha_action" id="epd-recaptcha-action" value="" />
    <?php else : ?>
        <div id="epd-recaptcha">
            <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha['site_key']; ?>"></div>
        </div>
    <?php endif; ?>

	<?php echo ob_get_clean();
} // epd_insert_recaptcha_script_for_registration_form
add_action( 'epd_register_form_fields_before_submit', 'epd_insert_recaptcha_script_for_registration_form', 900 );

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
