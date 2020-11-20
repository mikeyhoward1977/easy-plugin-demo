<?php
/**
 * Plugin Name: Easy Plugin Demo Builder
 * Plugin URI: https://easy-plugin-demo.com/
 * Description: A WordPress demo builder plugin that fully automates the creation of sandbox sites for you to showcase your plugins, themes and content to customers.
 * Version: 1.3.9
 * Date: 20th November 2020
 * Author: Mike Howard
 * Author URI: https://easy-plugin-demo.com/
 * Text Domain: easy-plugin-demo
 * Domain Path: /languages
 * Network: true
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: true
 * GitHub Plugin URI: https://github.com/mikeyhoward1977/easy-plugin-demo
 * Tags: demo, plugin, theme, multisite, wpmu
 *
 *
 * Easy Plugin Demo is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * Easy Plugin Demo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Plugin Demo; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package		EPD
 * @category	Core
 * @author		Mike Howard
 * @version		1.3.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Easy_Plugin_Demo' ) ) :
/**
 * Main Easy_Plugin_Demo Class.
 *
 * @since 1.0
 */
final class Easy_Plugin_Demo {
	/** Singleton *************************************************************/

	/**
	 * @var		Easy_Plugin_Demo The one true Easy_Plugin_Demo
	 * @since	1.0
	 */
	private static $instance;

	/**
	 * EPD Emails.
	 *
	 * @var		object	EPD_Emails
	 * @since	1.0
	 */
	public $emails;

	/**
	 * EPD Email Tags.
	 *
	 * @var		object	EPD_Email_Template_Tags
	 * @since	1.0
	 */
	public $email_tags;

	/**
	 * Main Easy_Plugin_Demo Instance.
	 *
	 * Insures that only one instance of Easy_Plugin_Demo exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since	1.0
	 * @static
	 * @static	var		arr		$instance
	 * @uses	Easy_Plugin_Demo::setup_constants()	Setup the constants needed.
	 * @uses	Easy_Plugin_Demo::includes()			Include the required files.
	 * @uses	Easy_Plugin_Demo::load_textdomain()	Load the language files.
	 * @see EPD()
	 * @return	obj	Easy_Plugin_Demo	The one true Easy_Plugin_Demo
	 */
	public static function instance() {

		if ( ! is_multisite() )	{
			return;
		}

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Easy_Plugin_Demo ) )	{
			do_action( 'before_epd_init' );

			self::$instance = new Easy_Plugin_Demo;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->hooks();
			self::$instance->emails     = new EPD_Emails();
			self::$instance->email_tags = new EPD_Email_Template_Tags();

			do_action( 'epd_init' );
		}

		return self::$instance;

	}
	
	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since	1.0
	 * @access	protected
	 * @return	void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-plugin-demo' ), '1.0' );
	} // __clone

	/**
	 * Disable unserializing of the class.
	 *
	 * @since	1.0
	 * @access	protected
	 * @return	void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-plugin-demo' ), '1.0' );
	} // __wakeup
	
	/**
	 * Setup plugin constants.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function setup_constants()	{

		if ( ! defined( 'EPD_VERSION' ) )	{
			define( 'EPD_VERSION', '1.3.9' );
		}

		if ( ! defined( 'EPD_PLUGIN_DIR' ) )	{
			define( 'EPD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'EPD_PLUGIN_URL' ) )	{
			define( 'EPD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		
		if ( ! defined( 'EPD_PLUGIN_FILE' ) )	{
			define( 'EPD_PLUGIN_FILE', __FILE__ );
		}

	} // setup_constants
			
	/**
	 * Include required files.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function includes()	{

        global $epd_options;

        require_once EPD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';

        $epd_options = epd_get_settings();

		require_once EPD_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/plugin-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/register/register-actions.php';
		require_once EPD_PLUGIN_DIR . 'includes/register/register-functions.php';
        require_once EPD_PLUGIN_DIR . 'includes/shortcodes.php';
        require_once EPD_PLUGIN_DIR . 'includes/sites/site-actions.php';
		require_once EPD_PLUGIN_DIR . 'includes/sites/site-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/sites/class-epd-reset-site.php';
		require_once EPD_PLUGIN_DIR . 'includes/posts/post-actions.php';
		require_once EPD_PLUGIN_DIR . 'includes/posts/post-functions.php';
        require_once EPD_PLUGIN_DIR . 'includes/template-functions.php';
        require_once EPD_PLUGIN_DIR . 'includes/users/user-actions.php';
        require_once EPD_PLUGIN_DIR . 'includes/users/user-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/emails/email-functions.php';
		require_once EPD_PLUGIN_DIR . 'includes/emails/email-template.php';
		require_once EPD_PLUGIN_DIR . 'includes/emails/class-epd-emails.php';
		require_once EPD_PLUGIN_DIR . 'includes/emails/class-epd-email-tags.php';

        if ( is_admin() )   {
            require_once EPD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once EPD_PLUGIN_DIR . 'includes/admin/settings/settings-actions.php';
            require_once EPD_PLUGIN_DIR . 'includes/admin/admin-plugins.php';
            require_once EPD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
			require_once EPD_PLUGIN_DIR . 'includes/admin/admin-sites.php';
			require_once EPD_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-functions.php';
			require_once EPD_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-actions.php';
            require_once EPD_PLUGIN_DIR . 'includes/admin/class-epd-admin-notices.php';
        }

		require_once EPD_PLUGIN_DIR . 'includes/install.php';
		
	} // includes

	/**
	 * Hooks.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function hooks()	{
		// Scripts
		add_action( 'admin_enqueue_scripts', array( self::$instance, 'load_admin_scripts' ) );

		// Upgrades
        add_action( 'admin_init', array( self::$instance, 'upgrades' ) );
	} // hooks


	/**
	 * Load the text domain for translations.
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	public function load_textdomain()	{

        // Set filter for plugin's languages directory.
		$epd_lang_dir  = dirname( plugin_basename( EPD_PLUGIN_FILE ) ) . '/languages/';
		$epd_lang_dir  = apply_filters( 'epd_languages_directory', $epd_lang_dir );

		// Traditional WordPress plugin locale filter.
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'easy-plugin-demo' );

        load_textdomain( 'easy-plugin-demo', WP_LANG_DIR . '/easy-plugin-demo/easy-plugin-demo-' . $locale . '.mo' );
        load_plugin_textdomain( 'easy-plugin-demo', false, $epd_lang_dir );

	} // load_textdomain

/*****************************************
 -- SCRIPTS
*****************************************/
	public function load_admin_scripts( $hook )	{

		$load_page_hook = array(
			'options-reading.php',
			'settings_page_epd-settings',
            'tools_page_epd_reset'
		);

        if ( epd_can_reset_sites() )   {
            echo "<style>#wpadminbar #wp-admin-bar-epd-reset-demo-site .ab-icon:before {
                content: '\f531';
                top: 1px;
                font-size: smaller;
            }</style>";
        }

		if ( ! in_array( $hook, $load_page_hook ) )	{
			return;
		}

		$js_dir  = EPD_PLUGIN_URL . 'assets/js/';
		$css_dir = EPD_PLUGIN_URL . 'assets/css/';
		$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style( 'jquery-chosen-css', $css_dir . 'chosen' . $suffix . '.css', array(), EPD_VERSION );
		wp_enqueue_style( 'jquery-chosen-css' );
		wp_register_style( 'epd-admin', $css_dir . 'epd-admin' . $suffix . '.css', array(), EPD_VERSION );
		wp_enqueue_style( 'epd-admin' );

		wp_register_script(
			'jquery-chosen',
			$js_dir . 'chosen.jquery' . $suffix . '.js',
			array( 'jquery' ),
			EPD_VERSION
		);

		wp_enqueue_script( 'jquery-chosen' );

		wp_register_script(
			'epd-admin-scripts',
			$js_dir . 'admin-scripts' . $suffix . '.js',
			array( 'jquery' ),
			EPD_VERSION
		);

		wp_localize_script(
			'epd-admin-scripts',
			'epd_admin_vars',
			apply_filters( 'epd_admin_scripts_vars',
				array(
					'hide_blog_public'   => epd_get_option( 'disable_search', false ),
					'one_option'         => __( 'Choose an option', 'easy-plugin-demo' ),
					'one_or_more_option' => __( 'Choose one or more options', 'easy-plugin-demo' ),
					'primary_site'       => get_current_blog_id() == get_network()->blog_id,
					'super_admin'        => current_user_can( 'setup_network' ),
					'type_to_search'     => __( 'Type to search', 'easy-plugin-demo' )
				)
			)
		);

		wp_enqueue_script( 'epd-admin-scripts' );
	} // load_admin_scripts

/*****************************************
 -- UPGRADE PROCEDURES
*****************************************/
    /**
     * Perform automatic database upgrades when necessary
     *
     * @since	1.0.1
     * @return	void
    */
    public function upgrades() {

        $did_upgrade = false;
        $epd_version = preg_replace( '/[^0-9.].*/', '', get_site_option( 'epd_version' ) );

		if ( version_compare( $epd_version, '1.2', '<' ) ) {
			epd_update_option( 'registration_page', false );
		}

        if ( version_compare( $epd_version, EPD_VERSION, '<' ) )	{
            // Let us know that an upgrade has happened
            $did_upgrade = true;
        }

        if ( $did_upgrade )	{
            update_site_option( 'epd_version_upgraded_from', $epd_version );
            update_site_option( 'epd_version', preg_replace( '/[^0-9.].*/', '', EPD_VERSION ) );
        }

    } // upgrades

} // class Easy_Plugin_Demo
endif;

/**
 * The main function for that returns Easy_Plugin_Demo
 *
 * The main function responsible for returning the one true Easy_Plugin_Demo
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $epd = EPD(); ?>
 *
 * @since	1.0
 * @return	obj		Easy_Plugin_Demo	The one true Easy_Plugin_Demo Instance.
 */
function EPD()	{
	return Easy_Plugin_Demo::instance();
} // EPD

// Get EPD Running
EPD();
