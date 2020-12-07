<?php
/**
 * License handler for Easy Plugin Demo
 *
 * This class should simplify the process of adding license information
 * to new EPD extensions.
 *
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'EPD_License' ) )	{

	/**
	 * EPD_License Class
	 */
	class EPD_License {
		private $file;
		private $license;
		private $item_name;
		private $item_id;
		private $item_shortname;
		private $version;
		private $author;
		private $api_url = 'https://easy-plugin-demo.com/edd-sl-api/';

		/**
		 * Class constructor
		 *
		 * @param	str		$_file
		 * @param	str		$_item
		 * @param	str		$_version
		 * @param	str		$_author
		 * @param	str		$_optname
		 * @param	str		$_api_url
		 */
		function __construct( $_file, $_item, $_version, $_author, $_optname = null, $_api_url = null ) {
            $this->file           = $_file;

            if( is_numeric( $_item ) )	{
                $this->item_id    = absint( $_item );
            } else {
                $this->item_name  = $_item;
            }

            $this->item_shortname = 'epd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
            $this->version        = $_version;
            $this->license        = trim( epd_get_option( $this->item_shortname . '_license_key', '' ) );
            $this->author         = $_author;
            $this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

            /**
             * Allows for backwards compatibility with old license options,
             * i.e. if the plugins had license key fields previously, the license
             * handler will automatically pick these up and use those in lieu of the
             * user having to reactive their license.
             */
            if ( ! empty( $_optname ) ) {
                $opt = epd_get_option( $_optname, false );

                if( isset( $opt ) && empty( $this->license ) ) {
                    $this->license = trim( $opt );
                }
            }

            // Setup hooks
            $this->includes();
            $this->hooks();
		} // __construct
	
		/**
		 * Include the updater class
		 *
		 * @access  private
		 * @since	1.0
		 * @return  void
		 */
		private function includes() {
            if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  {
                require_once 'EDD_SL_Plugin_Updater.php';
            }
		} // includes
	
		/**
		 * Setup hooks
		 *
		 * @access  private
		 * @since	1.0
		 * @return  void
		 */
        private function hooks() {
            // Register settings
            add_filter( 'epd_settings_licenses', array( $this, 'settings' ), 1 );

            // Remove installed premium extensions from plugin upsells
            add_filter( 'epd_upsell_extensions_settings', array( $this, 'filter_upsells' ) );

            // Display help text at the top of the Licenses tab
            add_action( 'epd_settings_tab_top', array( $this, 'license_help_text' ) );

            // Activate license key on settings save
            add_action( 'epd_saved_settings', array( $this, 'activate_license' ) );

            // Deactivate license key
            add_action( 'epd_saved_settings', array( $this, 'deactivate_license' ) );

            // Check that license is valid once per week
            add_action( 'epd_twicedaily_scheduled_events', array( $this, 'license_check' ) );

            // For testing license notices, uncomment this line to force checks on every page load
            //add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

            // Updater
            add_action( 'init', array( $this, 'auto_updater' ), 0 );

            // Display notices to admins
            add_action( 'admin_notices', array( $this, 'notices' ) );

            add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );
        } // hooks

		/**
		 * Auto updater
		 *
		 * @access  private
		 * @since	1.0
		 * @return  void
		 */
		public function auto_updater() {
            $doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;

			if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
				return;
			}

            $args = array(
                'version'   => $this->version,
                'license'   => $this->license,
                'author'    => $this->author
            );

            if ( ! empty( $this->item_id ) )	{
                $args['item_id']   = $this->item_id;
            } else {
                $args['item_name'] = $this->item_name;
            }

            // Setup the updater
            $epd_updater = new EDD_SL_Plugin_Updater(
                $this->api_url,
                $this->file,
                $args
            );
		} // auto_updater

		/**
		 * Add license field to settings
		 *
		 * @access	public
		 * @param	arr		$settings	Array of registered settings
		 * @return	arr		Filtered array of registered settings
		 */
		public function settings( $settings ) {
            $epd_license_settings = array(
                array(
                    'id'      => $this->item_shortname . '_license_key',
                    'name'    => sprintf( __( '%1$s', 'easy-plugin-demo' ), $this->item_name ),
                    'desc'    => '',
                    'type'    => 'license_key',
                    'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
                    'size'    => 'regular'
                )
            );

            return array_merge( $settings, $epd_license_settings );
		} // settings

        /**
         * If a premium extension is installed, remove it from the upsells array.
         *
         * @since   1.3.10
         * @param   array   $plugins    Array of available premium extensions
         * @return  array   Array of available premium extensions
         */
        public function filter_upsells( $plugins )  {
            $key = str_replace( 'epd_', '', $this->item_shortname );

            if ( array_key_exists( $key, $plugins ) )   {
                unset( $plugins[ $key ] );
            }

            return $plugins;
        } // filter_upsells

		/**
		 * Display help text at the top of the Licenses settings tab.
		 *
		 * @access	public
		 * @since   1.0
		 * @param	str		$active_tab		The currently active settings tab
		 * @return	void
		 */
		public function license_help_text( $active_tab = '' ) {
            static $has_ran;

            if ( 'licenses' !== $active_tab ) {
                return;
            }

            if ( ! empty( $has_ran ) ) {
                return;
            }

            echo '<h1 class="wp-heading-inline">' . __( 'Manage Licenses', 'easy-plugin-demo' ) . '</h1>';
            printf(
                '<a href="%s" target="_blank" class="page-title-action">%s</a>',
                'https://easy-plugin-demo.com/downloads/epd-premium-pack/',
                __( 'Visit Extension Store', 'easy-plugin-demo' )
            );

            printf(
                '<p>' . __( 'Enter your <a href="%s" target="_blank">license keys</a> here to receive updates for extensions you have purchased. If your license key has expired, please renew your license.', 'easy-plugin-demo' ) . '</p>',
                'https://easy-plugin-demo.com/your-account/'
            );

            printf(
                '<p>' . __( '<a href="%1$s" target="_blank">Visit our store</a> and receive a %2$s discount on all purchases.', 'easy-plugin-demo' ) . '</p>',
                'https://easy-plugin-demo.com/downloads/epd-premium-pack/?discount=15offnow',
                '15%'
            );

            $has_ran = true;
		} // license_help_text

		/**
		 * Activate the license key
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function activate_license( $input ) {
            if ( ! isset( $_POST['epd_settings'] ) ) {
                return;
            }

            if ( ! isset( $_REQUEST[ $this->item_shortname . '_license_key-nonce'] ) || ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {
                return;
            }

            if ( ! current_user_can( 'manage_sites' ) ) {
                return;
            }

            if ( empty( $_POST['epd_settings'][ $this->item_shortname . '_license_key'] ) ) {
                delete_site_option( $this->item_shortname . '_license_active' );
                return;
            }

            foreach ( $_POST as $key => $value ) {
                if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
                    // Don't activate a key when deactivating a different key
                    return;
                }
            }

            $details = get_site_option( $this->item_shortname . '_license_active' );

            if ( is_object( $details ) && 'valid' === $details->license ) {
                return;
            }

            $license = sanitize_text_field( $_POST['epd_settings'][ $this->item_shortname . '_license_key'] );

            if ( empty( $license ) ) {
                return;
            }

            // Data to send to the API
            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $license,
                'item_name'  => urlencode( $this->item_name ),
                'url'        => home_url()
            );

            // Call the API
            $response = wp_remote_post(
                $this->api_url,
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params
                )
            );

            // Make sure there are no errors
            if ( is_wp_error( $response ) ) {
                return;
            }

            // Tell WordPress to look for updates
            set_site_transient( 'update_plugins', null );

            // Decode license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            update_site_option( $this->item_shortname . '_license_active', $license_data );
		} // activate_license

		/**
		 * Deactivate the license key
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function deactivate_license() {
            if ( ! isset( $_POST['epd_settings'] ) )
                return;

            if ( ! isset( $_POST['epd_settings'][ $this->item_shortname . '_license_key'] ) )
                return;

            if ( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {

                wp_die( __( 'Nonce verification failed', 'easy-plugin-demo' ), __( 'Error', 'easy-plugin-demo' ), array( 'response' => 403 ) );

            }

            if ( ! current_user_can( 'manage_sites' ) ) {
                return;
            }

            // Run on deactivate button press
            if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {

                // Data to send to the API
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license'    => $this->license,
                    'item_name'  => urlencode( $this->item_name ),
                    'url'        => home_url()
                );

                // Call the API
                $response = wp_remote_post(
                    $this->api_url,
                    array(
                        'timeout'   => 15,
                        'sslverify' => false,
                        'body'      => $api_params
                    )
                );

                // Make sure there are no errors
                if ( is_wp_error( $response ) ) {
                    return;
                }

                // Decode the license data
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );

                delete_site_option( $this->item_shortname . '_license_active' );

            }
		} // deactivate_license

		/**
		 * Check if license key is valid
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function license_check()	{
            if ( ! empty( $_POST['epd_settings'] ) ) {
                return; // Don't fire when saving settings
            }

            if ( empty( $this->license ) )	{
                return;
            }

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'check_license',
                'license' 	=> $this->license,
                'item_name'  => urlencode( $this->item_name ),
                'url'        => home_url()
            );

            // Call the API
            $response = wp_remote_post(
                $this->api_url,
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params
                )
            );

            // make sure the response came back okay
            if ( is_wp_error( $response ) ) {
                return false;
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            update_site_option( $this->item_shortname . '_license_active', $license_data );
		} // license_check

		/**
		 * Admin notices for errors
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function notices() {
            static $showed_invalid_message;

            if ( empty( $this->license ) ) {
                return;
            }

            if ( ! current_user_can( 'manage_sites' ) ) {
                return;
            }

            $messages = array();
            $license  = get_site_option( $this->item_shortname . '_license_active' );

            if ( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {
                if ( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
                    $messages[] = sprintf(
                        __( 'You have invalid or expired license keys for Easy Plugin Demo. Please go to the <a href="%s">Licenses page</a> to correct this issue.', 'easy-plugin-demo' ),
                        admin_url( 'edit.php?post_type=epd_ticket&page=epd-settings&tab=licenses' )
                    );

                    $showed_invalid_message = true;
                }
            }

            if ( ! empty( $messages ) ) {
                foreach( $messages as $message ) {
                    echo '<div class="error">';
                        echo '<p>' . $message . '</p>';
                    echo '</div>';
                }
            }
		} // notices

		/**
		 * Displays message inline on plugin row that the license key is missing
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function plugin_row_license_missing( $plugin_data, $version_info ) {
            static $showed_imissing_key_message;

            $license = get_site_option( $this->item_shortname . '_license_active' );

            if ( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
                echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=epd_ticket&page=epd-settings&tab=licenses' ) ) . '">' . __( 'Enter a valid license key for automatic updates.', 'easy-plugin-demo' ) . '</a></strong>';
                $showed_imissing_key_message[ $this->item_shortname ] = true;
            }
		} // plugin_row_license_missing
	} // EPD_License
} // end class_exists check
