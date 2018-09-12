<?php
/**
 * Register Settings.
 *
 * @package     EPD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since	1.0
 * @return	mixed
 */
function epd_get_option( $key = '', $default = false ) {
	global $epd_options;

	$value = ! empty( $epd_options[ $key ] ) ? $epd_options[ $key ] : $default;
	$value = apply_filters( 'epd_get_option', $value, $key, $default );

	return apply_filters( 'epd_get_option_' . $key, $value, $key, $default );
} // epd_get_option

/**
 * Update an option
 *
 * Updates a EPD setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the epd_options array.
 *
 * @since	1.0
 * @param	str				$key	The Key to update
 * @param	str|bool|int	$value	The value to set the key to
 * @return	bool			True if updated, false if not.
 */
function epd_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = epd_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_site_option( 'epd_settings', array() );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'epd_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_site_option( 'epd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $epd_options;
		$epd_options[ $key ] = $value;

	}

	return $did_update;
} // epd_update_option

/**
 * Remove an option.
 *
 * Removes a epd setting value in both the db and the global variable.
 *
 * @since	1.0
 * @param	str		$key	The Key to delete.
 * @return	bool	True if updated, false if not.
 */
function epd_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_site_option( 'epd_settings', array() );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_site_option( 'epd_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $epd_options;
		$epd_options = $options;
	}

	return $did_update;
} // epd_delete_option

/**
 * Get Settings.
 *
 * Retrieves all plugin settings.
 *
 * @since	1.0
 * @return	arr		EPD settings.
 */
function epd_get_settings() {
	$settings = get_site_option( 'epd_settings', array() );

	if ( empty( $settings ) ) {
		update_site_option( 'epd_settings', $settings );
	}

	return apply_filters( 'epd_get_settings', $settings );
} // epd_get_settings

/**
 * Retrieve the array of plugin settings.
 *
 * @since	1.0
 * @return	arr
*/
function epd_get_registered_settings() {

    $current_theme   = wp_get_theme();
	$network         = get_network();
	$welcome_example = add_query_arg( 'epd_action', 'add_welcome_example', admin_url() );

	/**
	 * 'Whitelisted' EPD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings.
	 */
	$epd_settings = array(
		/** General Settings */
		'sites' => apply_filters( 'epd_settings_general',
			array(
				'main' => array(
					'product' => array(
						'id'       => 'product',
						'name'     => __( 'Product Name', 'easy-plugin-demo' ),
						'type'     => 'text',
						'desc'     => __( 'Enter the name of the product you are demonstrating to users.', 'easy-plugin-demo' )
					),
					'title' => array(
						'id'       => 'title',
						'name'     => __( 'Default Title', 'easy-plugin-demo' ),
						'type'     => 'text',
						'std'      => sprintf( '%s Demo', esc_attr( get_network()->site_name ) ),
						'desc'     => __( 'This will become the title of all new demo sites.', 'easy-plugin-demo' )
					),
					'max_user_sites' => array(
						'id'       => 'max_user_sites',
						'name'     => __( 'Maximum Sites of User', 'easy-plugin-demo' ),
						'type'     => 'number',
						'min'      => '1',
						'step'     => '1',
						'std'      => '1',
						'size'     => 'small',
						'desc'     => __( 'Enter the maximum number of sites a single user can have active at any time.' , 'easy-plugin-demo' )
					),
					'registration_action' => array(
						'id'       => 'registration_action',
						'name'     => __( 'Registration Action', 'easy-plugin-demo' ),
						'type'     => 'registration_actions',
						'std'      => 'login',
						'desc'     => __( 'Choose where to send the user once they have successfully registered their demo site.', 'easy-plugin-demo' )
					),
					'delete_after' => array(
						'id'       => 'delete_after',
						'name'     => __( 'Delete Site After', 'easy-plugin-demo' ),
						'type'     => 'select',
						'options'  => epd_get_lifetime_options(),
						'std'      => epd_get_default_site_lifetime(),
						'desc'     => __( 'Select the time period for which a demo site can remain active. After this time it will be deleted.' , 'easy-plugin-demo' )
					),
				),
				'config' => array(
					'discourage_search' => array(
						'id'       => 'discourage_search',
						'name'     => __( 'Discourage Search Engines', 'easy-plugin-demo' ),
						'type'     => 'checkbox',
						'desc'     => __( 'Discourage search engines from indexing new sites.' , 'easy-plugin-demo' )
					),
					'disable_search' => array(
						'id'       => 'disable_search',
						'name'     => __( 'Disable Visibility Changes', 'easy-plugin-demo' ),
						'type'     => 'checkbox',
						'std'      => 1,
						'desc'     => __( 'Select to disable users from changing search engine visibility settings. Does not apply to the primary site or if the current user can manage networks.' , 'easy-plugin-demo' )
					),
					'hide_welcome' => array(
						'id'       => 'hide_welcome',
						'name'     => __( 'Hide Default Welcome', 'easy-plugin-demo' ),
						'type'     => 'checkbox',
						'desc'     => __( 'If enabled, the default WordPress welcome panel will be removed.' , 'easy-plugin-demo' )
					),
					'custom_welcome' => array(
						'id'       => 'custom_welcome',
						'name'     => __( 'Custom Welcome Panel', 'easy-plugin-demo' ),
						'type'     => 'rich_editor',
						'desc'     => __( 'Optionally enter text to create your own welcome panel when a site is registered and a user logs in. <a href="#" id="epd-welcome-example">Load example</a>.' , 'easy-plugin-demo' )
					)
				),
				'themes' => array(
					'allowed_themes' => array(
						'id'       => 'allowed_themes',
						'name'     => __( 'Allowed Themes', 'easy-plugin-demo' ),
						'type'     => 'select',
						'multiple' => true,
						'options'  => epd_get_themes( false ),
						'std'      => array(),
						'desc'     => __( 'Select the themes you want to be available within a demo site for activation. Note that Network Active themes will always be available.' , 'easy-plugin-demo' ),
					),
					'theme' => array(
						'id'       => 'theme',
						'name'     => __( 'Demo Site Theme', 'easy-plugin-demo' ),
						'type'     => 'select',
						'options'  => epd_get_themes(),
						'std'      => esc_attr( $current_theme->stylesheet ),
						'desc'     => __( 'Select the theme you would like activated by default on a new demo site. If you select a theme that is not network enabled and not listed within <strong>Allowed Themes</strong> above, it will be added to the list of <strong>Allowed Themes</strong>.' , 'easy-plugin-demo' ),
					),
				),
				'plugins' => array(
					'enable_plugins' => array(
						'id'       => 'enable_plugins',
						'name'     => __( 'Enable Plugins', 'easy-plugin-demo' ),
						'type'     => 'select',
						'multiple' => true,
						'options'  => epd_get_non_network_enabled_plugins(),
						'std'      => array(),
						'desc'     => __( 'Select the non-Network Active plugins you would like enabled when a new demo site is registered.', 'easy-plugin-demo' )
					)
				)
			)
		),
		'email' => apply_filters( 'epd_settings_general',
			array(
				'main' => array(
					'disable_email_confirmation' => array(
						'id'       => 'disable_email_confirmation',
						'name'     => __( 'Disable Email Confirmation', 'easy-plugin-demo' ),
						'type'     => 'checkbox',
						'desc'     => __( 'Select to disable email confirmation to the user after successful registration.' , 'easy-plugin-demo' )
					),
					'from_name' => array(
						'id'       => 'from_name',
						'name'     => __( 'Email From Name', 'easy-plugin-demo' ),
						'type'     => 'text',
						'std'      => esc_attr( $network->site_name ), 
						'desc'     => __( 'The name you want displayed on registration emails.' , 'easy-plugin-demo' )
					),
					'from_email' => array(
						'id'       => 'from_email',
						'name'     => __( 'Email From Address', 'easy-plugin-demo' ),
						'type'     => 'text',
						'std'      => get_network_option( $network->blog_id, 'admin_email' ),
						'desc'     => __( 'The email address you want registration emails to be sent from.' , 'easy-plugin-demo' )
					),
					'registration_subject' => array(
						'id'       => 'registration_subject',
						'name'     => __( 'Registration Subject', 'easy-plugin-demo' ),
						'type'     => 'text',
						'std'      => sprintf( 'Your %s Demo is Ready', esc_attr( $network->site_name ) ),
						'desc'     => __( 'Enter the subject of the registration email. Email tags are accepted.' , 'easy-plugin-demo' )
					),
					'registration_content' => array(
						'id'       => 'registration_content',
						'name'     => __( 'Registration Content', 'easy-plugin-demo' ),
						'type'     => 'rich_editor',
						'std'      => epd_get_site_registered_email_body_content(),
						'desc'     => __( 'Enter the content of the registration email. Email tags are accepted.' , 'easy-plugin-demo' )
					),
					'email_tags_list' => array(
						'id'       => 'email_tags_list',
						'type'     => 'hook'
					)
				)
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters( 'epd_settings_extensions',
			array()
		),
		/** License Settings */
		'licenses' => apply_filters( 'epd_settings_licenses',
			array()
		),
		/** Misc Settings */
		'misc' => apply_filters( 'epd_settings_general',
			array(
				'main' => array(
					'credits' => array(
						'id'       => 'credits',
						'name'     => __( 'Give Credit', 'easy-plugin-demo' ),
						'type'     => 'checkbox',
						'std'      => 0,
						'desc'     => __( 'If enabled, credit to EPD will be displayed below the registration form. We appreciate it.' , 'easy-plugin-demo' )
					)
				),
				'recaptcha' => array(
					'id'       => 'recaptcha',
					'name'     => __( 'reCaptcha v2 Keys', 'easy-plugin-demo' ),
					'type'     => 'header'
				),
				'site_key' => array(
					'id'       => 'site_key',
					'name'     => __( 'Site Key', 'easy-plugin-demo' ),
					'type'     => 'text',
					'desc'     => sprintf(
						__( '<a href="%s" target="_blank">Register your site</a> to retrieve your site key .' , 'easy-plugin-demo' ),
						'https://www.google.com/recaptcha/admin'
					)
				),
				'secret' => array(
					'id'       => 'secret_key',
					'name'     => __( 'Secret Key', 'easy-plugin-demo' ),
					'type'     => 'text',
					'desc'     => sprintf(
						__( '<a href="%s" target="_blank">Register your site</a> to retrieve your secret key .' , 'easy-plugin-demo' ),
						'https://www.google.com/recaptcha/admin'
					)
				)
			)
		)
	);

	return apply_filters( 'epd_registered_settings', $epd_settings );
} // epd_get_registered_settings

/**
 * Add all settings sections and fields.
 *
 * @since	1.0
 * @return	void
*/
function epd_register_settings() {

	if ( false == get_site_option( 'epd_settings' ) ) {
		add_site_option( 'epd_settings', array() );
	}

	foreach ( epd_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings) {

			// Check for backwards compatibility
			$section_tabs = epd_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'epd_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'epd_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$args = wp_parse_args( $option, array(
				    'section'       => $section,
				    'id'            => null,
				    'desc'          => '',
				    'name'          => '',
				    'size'          => null,
				    'options'       => '',
				    'std'           => '',
				    'min'           => null,
				    'max'           => null,
				    'step'          => null,
				    'chosen'        => null,
				    'placeholder'   => null,
				    'allow_blank'   => true,
				    'readonly'      => false,
				    'faux'          => false,
				    'tooltip_title' => false,
				    'tooltip_desc'  => false,
				    'field_class'   => ''
				) );

				add_settings_field(
					'epd_settings[' . $args['id'] . ']',
					$args['name'],
					function_exists( 'epd_' . $args['type'] . '_callback' ) ? 'epd_' . $args['type'] . '_callback' : 'epd_missing_callback',
					'epd_settings_' . $tab . '_' . $section,
					'epd_settings_' . $tab . '_' . $section,
					$args
				);
			}
		}

	}

	// Creates our settings in the options table
	register_setting( 'epd_settings', 'epd_settings', 'epd_settings_sanitize' );

} // epd_register_settings
add_action( 'admin_init', 'epd_register_settings' );

/**
 * Save Settings.
 *
 * @since	1.0
 * @param	array	$input	Array of posted data
 * @return	void
 */
function epd_save_settings( $input = array() ) {

	global $epd_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = epd_get_registered_settings();
	$tab      = isset( $referrer['tab'] )     ? $referrer['tab']     : 'sites';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();

	$input = apply_filters( 'epd_settings_' . $tab . '-' . $section . '_sanitize', $input );
	if ( 'main' === $section )  {
		// Check for extensions that aren't using new sections
		$input = apply_filters( 'epd_settings_' . $tab . '_sanitize', $input );

		// Check for an override on the section for when main is empty
		if ( ! empty( $_POST['epd_section_override'] ) ) {
			$section = sanitize_text_field( $_POST['epd_section_override'] );
		}
	}

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'epd_settings_sanitize_' . $type, $value, $key );
		}

		// Specific key filter
		$input[ $key ] = apply_filters( 'epd_settings_sanitize_' . $key, $value );

		// General filter
		$input[ $key ] = apply_filters( 'epd_settings_sanitize', $input[ $key ], $key );

	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();

	$found_settings = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {

			// Settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[ $key ] ) && isset( $epd_options[ $key ] ) ) {
				unset( $epd_options[ $key ] );
			}

		}
	}

	if ( isset( $input['registration_action'] ) )	{
		$input['redirect_page'] = isset( $input['redirect_page'] ) ? sanitize_text_field( $input['redirect_page'] ) : false;
	}

	// Merge our new settings with the existing
	$output = array_merge( $epd_options, $input );

	update_site_option( 'epd_settings', $output );

} // epd_save_settings

/**
 * Retrieve settings tabs
 *
 * @since	1.0
 * @return	arr		$tabs
 */
function epd_get_settings_tabs() {

	$tabs            = array();
	$tabs['sites']   = __( 'Sites', 'easy-plugin-demo' );
	$tabs['email']   = __( 'Email', 'easy-plugin-demo' );

	if ( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'easy-plugin-demo' );
	}

	if ( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'easy-plugin-demo' );
	}
	
	$tabs['misc']   = __( 'Misc', 'easy-plugin-demo' );

	return apply_filters( 'epd_settings_tabs', $tabs );
} // epd_get_settings_tabs

/**
 * Retrieve settings tabs
 *
 * @since	1.0
 * @return	arr		$section
 */
function epd_get_settings_tab_sections( $tab = false ) {

	$tabs     = false;
	$sections = epd_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
} // epd_get_settings_tab_sections

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since	1.0
 * @return	arr		Array of tabs and sections
 */
function epd_get_registered_settings_sections() {

	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'sites'      => apply_filters( 'epd_settings_sections_general', array(
			'main'    => __( 'General', 'easy-plugin-demo' ),
			'config'  => __( 'Config', 'easy-plugin-demo' ),
			'themes'  => __( 'Themes', 'easy-plugin-demo' ),
			'plugins' => __( 'Plugins', 'easy-plugin-demo' )
		) ),
		'email'      => apply_filters( 'epd_settings_sections_emails', array(
			'main' => __( 'Email Settings', 'easy-plugin-demo' )
		) ),
		'extensions' => apply_filters( 'epd_settings_sections_extensions', array(
			'main' => __( 'Main', 'easy-plugin-demo' )
		) ),
		'licenses'   => apply_filters( 'epd_settings_sections_licenses', array() ),
		'misc'       => apply_filters( 'epd_settings_sections_misc', array(
			'main'      => __( 'Misc Settings', 'easy-plugin-demo' )
		) )
	);

	$sections = apply_filters( 'epd_settings_sections', $sections );

	return $sections;
} // epd_get_registered_settings_sections

/**
 * Sanitize text fields
 *
 * @since	1.0
 * @param	array	$input	The field value
 * @return	string	$input	Sanitizied value
 */
function epd_sanitize_text_field( $input ) {
	return trim( $input );
} // epd_sanitize_text_field
add_filter( 'epd_settings_sanitize_text', 'epd_sanitize_text_field' );

/**
 * Sanitizes a string key for EPD Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
 *
 * @since 	1.0
 * @param	string	$key	String key
 * @return	string	Sanitized key
 */
function epd_sanitize_key( $key ) {
	$raw_key = $key;
	$key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	/**
	 * Filter a sanitized key string.
	 *
	 * @since  1.0
	 * @param  string	$key     Sanitized key.
	 * @param  string	$raw_key The key prior to sanitization.
	 */
	return apply_filters( 'epd_sanitize_key', $key, $raw_key );
} // epd_sanitize_key

/**
 * Sanitize HTML Class Names
 *
 * @since	1.0
 * @param	str|arr		$class	HTML Class Name(s)
 * @return	str			$class
 */
function epd_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) )	{
		$class = sanitize_html_class( $class );
	} else if ( is_array( $class ) )	{
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;

} // epd_sanitize_html_class

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function epd_header_callback( $args ) {
	echo apply_filters( 'epd_after_setting_output', '', $args );
} // epd_header_callback

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_checkbox_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']"';
	}

	$class = epd_sanitize_html_class( $args['field_class'] );

	$checked = ! empty( $epd_option ) ? checked( 1, $epd_option, false ) : '';
	$html = '<input type="checkbox" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/> <label for="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_checkbox_callback

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_multicheck_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	$class = epd_sanitize_html_class( $args['field_class'] );

	$html = '';

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option )	{
			if ( isset( $epd_option[ $key ] ) )	{
				$enabled = $option;
			} else	{
				$enabled = NULL;
			}

			$html .= '<input name="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

			$html .= '<label for="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
		}

		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_multicheck_callback

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_radio_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	$html = '';

	$class = epd_sanitize_html_class( $args['field_class'] );

	foreach ( $args['options'] as $key => $option )	{
		$checked = false;

		if ( $epd_option && $key == $epd_option )	{
			$checked = true;
		} elseif ( isset( $args['std'] ) && $key == $args['std'] && ! $epd_option )	{
			$checked = true;
		}

		$html .= '<input name="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . epd_sanitize_key( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';

		$html .= '<label for="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
	}

	$html .= '<p class="description">' . apply_filters( 'epd_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
} // epd_radio_callback

/**
 * Radio Callback
 *
 * Renders registration action radio boxes.
 *
 * @since	1.0
 * @param	array	$args	Arguments passed by the setting
 * @return	void
 */
function epd_registration_actions_callback( $args ) {
	$epd_option    = epd_get_option( $args['id'] );
	$pages         = epd_get_pages();
	$redirect      = epd_get_option( 'redirect_page' );

	$pages_html = '<select id="epd-registration-action-page" name="epd_settings[redirect_page]">';

	foreach( $pages as $page_id => $page )	{
		$page_selected = false;
		if ( $redirect == $page_id )	{
			$page_selected = selected( $redirect, $page_id, false );
		}
		
		$pages_html .= '<option value="' . esc_attr( $page_id ) . '"' . $page_selected . '>' . esc_html( $page ) . '</option>';
	}

	$pages_html .= '</select>';

	$options = apply_filters( 'epd_registration_actions', array(
		'confirm'  => __( 'Show Confirmation', 'easy-plugin-demo' ),
		'home'     => __( 'Visit Home Page', 'easy-plugin-demo' ),
		'admin'    => __( 'Login to Admin', 'easy-plugin-demo' ),
		'redirect' => __( 'Redirect to Page', 'easy-plugin-demo' )
	) );

	$html  = '';
	$class = epd_sanitize_html_class( $args['field_class'] );

	foreach ( $options as $key => $option )	{
		$page_drop = '';
		$checked   = false;

		if ( $epd_option && $key == $epd_option )	{
			$checked = true;
		} elseif ( isset( $args['std'] ) && $key == $args['std'] && ! $epd_option )	{
			$checked = true;
		}

		if ( 'redirect' == $key )	{
			$page_drop = ' ' . $pages_html;
		}

		$html .= '<input name="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . epd_sanitize_key( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';

		$html .= '<label for="epd_settings[' . epd_sanitize_key( $args['id'] ) . '][' . epd_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label>' . $page_drop . '<br/>';
	}

	$html .= '<p class="description">' . apply_filters( 'epd_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
} // epd_registration_actions_callback

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_text_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option )	{
		$value = $epd_option;
	} else	{
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="epd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = epd_sanitize_html_class( $args['field_class'] );

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
	$html    .= '<p class="description"> '  . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_text_callback

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_number_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option ) {
		$value = $epd_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="epd_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = epd_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<p class="description"> '  . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_number_callback

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_textarea_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option )	{
		$value = $epd_option;
	} else	{
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = epd_sanitize_html_class( $args['field_class'] );

	$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" name="epd_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<p class="description"> '  . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_textarea_callback

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function epd_missing_callback($args) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'easy-plugin-demo' ),
		'<strong>' . $args['id'] . '</strong>'
	);
} // epd_missing_callback

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_select_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option )	{
		$value = $epd_option;
	} else	{
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( ! empty( $args['multiple'] ) ) {
		$multiple   = ' MULTIPLE';
		$name_array = '[]';
	} else {
		$multiple   = '';
		$name_array = '';
	}

	$class = epd_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' epd_select_chosen';
	}

	$html = '<select id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" name="epd_settings[' . esc_attr( $args['id'] ) . ']' . $name_array . '" class="' . $class . '"' . $multiple . ' data-placeholder="' . esc_html( $placeholder ) . '" />';

	foreach ( $args['options'] as $option => $name ) {
		if ( ! empty( $multiple ) && is_array( $value ) ) {
			$selected = selected( true, in_array( $option, $value ), false );
		} else	{
			$selected = selected( $option, $value, false );
		}
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_select_callback

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @global	$wp_version		WordPress Version
 */
function epd_rich_editor_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option )	{
		$value = $epd_option;

		if ( empty( $args['allow_blank'] ) && empty( $value ) )	{
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else	{
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	$class = epd_sanitize_html_class( $args['field_class'] );

	ob_start();
	wp_editor(
		stripslashes( $value ),
		'epd_settings_' . esc_attr( $args['id'] ),
		array(
			'textarea_name' => 'epd_settings[' . esc_attr( $args['id'] ) . ']',
			'textarea_rows' => absint( $rows ),
			'editor_class'  => $class
		)
	);
	$html = ob_get_clean();

	$html .= '<p class="description"> ' . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_rich_editor_callback

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD Options
 * @return	void
 */
function epd_color_callback( $args ) {
	$epd_option = epd_get_option( $args['id'] );

	if ( $epd_option )	{
		$value = $epd_option;
	} else	{
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$class = epd_sanitize_html_class( $args['field_class'] );

	$html = '<input type="text" class="epd-color-picker" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" name="epd_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<p class="description"> '  . wp_kses_post( $args['desc'] ) . '</p>';

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_color_callback

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function epd_descriptive_text_callback( $args ) {
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'epd_after_setting_output', $html, $args );
} // epd_descriptive_text_callback

/**
 * Registers the license field callback for Software Licensing
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @global	$epd_options	Array of all the EPD options
 * @return void
 */
if ( ! function_exists( 'epd_license_key_callback' ) ) {
	function epd_license_key_callback( $args )	{

		$epd_option = epd_get_option( $args['id'] );

		$messages = array();
		$license  = get_site_option( $args['options']['is_valid_license_option'] );

		if ( $epd_option )	{
			$value = $epd_option;
		} else	{
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if ( ! empty( $license ) && is_object( $license ) )	{

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch( $license->error ) {

					case 'expired' :

						$class = 'expired';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'easy-plugin-demo' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'http://easy-plugin-demo.com/checkout/?edd_license_key=' . $value
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'easy-plugin-demo' ),
							'https://easy-plugin-demo.com/support'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'easy-plugin-demo' ),
							'http://easy-plugin-demo.com/your-account'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'easy-plugin-demo' ),
							$args['name'],
							'http://easy-plugin-demo.com/your-account'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'error';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'easy-plugin-demo' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'easy-plugin-demo' ), 'http://easy-plugin-demo.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':

						$class = 'error';
						$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'easy-plugin-demo' );

						$license_status = 'license-' . $class . '-notice';
						break;

					default :

						$class = 'error';
						$error = ! empty(  $license->error ) ?  $license->error : __( 'unknown_error', 'easy-plugin-demo' );
						$messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'easy-plugin-demo' ), $error, 'https://easy-plugin-demo.com/support' );

						$license_status = 'license-' . $class . '-notice';
						break;

				}

			} else {

				switch( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'easy-plugin-demo' );

							$license_status = 'license-lifetime-notice';

						} elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'easy-plugin-demo' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
								'http://easy-plugin-demo.com/checkout/?edd_license_key=' . $value
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'easy-plugin-demo' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expiration-date-notice';

						}

						break;

				}

			}

		} else	{
			$class = 'empty';

			$messages[] = sprintf(
				__( 'To receive updates, please enter your valid %s license key.', 'easy-plugin-demo' ),
				$args['name']
			);

			$license_status = null;
		}

		$class .= ' ' . epd_sanitize_html_class( $args['field_class'] );

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" name="epd_settings[' . epd_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'easy-plugin-demo' ) . '"/>';
		}

		$html .= '<p class="description"> '  . wp_kses_post( $args['desc'] ) . '</p>';

		if ( ! empty( $messages ) ) {
			foreach( $messages as $message ) {

				$html .= '<div class="epd-license-data epd-license-' . $class . ' ' . $license_status . '">';
					$html .= '<p>' . $message . '</p>';
				$html .= '</div>';

			}
		}

		wp_nonce_field( epd_sanitize_key( $args['id'] ) . '-nonce', epd_sanitize_key( $args['id'] ) . '-nonce' );

		echo $html;
	}

} // epd_license_key_callback

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since	1.0
 * @param	arr		$args	Arguments passed by the setting
 * @return	void
 */
function epd_hook_callback( $args ) {
	do_action( 'epd_' . $args['id'], $args );
} // epd_hook_callback

/**
 * Set manage_sites as the cap required to save EPD settings pages
 *
 * @since	1.0
 * @return	str		Capability required
 */
function epd_set_settings_cap() {
	return 'manage_sites';
} // epd_set_settings_cap
add_filter( 'option_page_capability_epd_settings', 'epd_set_settings_cap' );

/**
 * Adds the tooltip after the setting field.
 *
 * @since	1.0
 * @param	str		$html	HTML output
 * @param	arr		$args	Array containing tooltip title and description
 * @return	str		Filtered HTML output
 */
function epd_add_setting_tooltip( $html, $args ) {

	if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
		$tooltip = '<span alt="f223" class="epd-help-tip dashicons dashicons-editor-help" title="<strong>' . $args['tooltip_title'] . '</strong>: ' . $args['tooltip_desc'] . '"></span>';
		$html .= $tooltip;
	}

	return $html;
} // epd_add_setting_tooltip
add_filter( 'epd_after_setting_output', 'epd_add_setting_tooltip', 10, 2 );

/**
 * List email tags.
 *
 * @since	1.0
 * @param	array	$args	Array of arguments passed by the setting
 * @return	string	List of email tags
 */
function epd_email_tags_list_callback( $args )	{
	printf(
		'<p class="description">%s:%s</p>',
		__( 'The following email tags can be used within email subject and content', 'easy-plugin-demo' ),
		'<br>' . epd_get_emails_tags_list()
	);
} // epd_email_tags_list_callback
add_action( 'epd_email_tags_list', 'epd_email_tags_list_callback' );

/**
 * Retrieve a list of all published pages.
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since	1.0
 * @param	bool	$force			Force the pages to be loaded even if not on settings
 * @return	arr		$pages_options	An array of the pages
 */
function epd_get_pages( $force = false ) {
	$pages_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'epd-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
} // epd_get_pages

/**
 * Retrieve a list of all installed themes.
 *
 * @since	1.0
 * @param   mixed   $allowed    Type of themes to retrieve. @See wp_get_themes().
 * @return	array	An array of the pages
 */
function epd_get_themes( $allowed = null ) {

	$themes_options = array( '' => '' ); // Blank option

	if ( ! isset( $_GET['page'] ) || 'epd-settings' != $_GET['page'] ) {
		return $themes_options;
	}

	$themes = wp_get_themes( array( 'allowed' => $allowed ) );
	if ( $themes ) {
		$themes_options = array();

		foreach ( $themes as $stylesheet => $theme ) {
			$themes_options[ esc_attr( $stylesheet ) ] = esc_html( $theme->Name );
		}
	} else {
        $themes_options = array( '' => '' ); // Blank option
    }

	return $themes_options;

} // epd_get_themes

/**
 * Returns a select list for lifetime options.
 *
 * @since	1.0
 * @return	array	Array of selectable options for lifetimes.
 */
function epd_get_lifetime_options()	{
	$lifetime = array(
		0                    => __( 'Never', 'easy-plugin-demo' ),
		HOUR_IN_SECONDS      => __( '1 Hour', 'easy-plugin-demo' ),
		2 * HOUR_IN_SECONDS  => __( '2 Hours', 'easy-plugin-demo' ),
		3 * HOUR_IN_SECONDS  => __( '3 Hours', 'easy-plugin-demo' ),
		4 * HOUR_IN_SECONDS  => __( '4 Hours', 'easy-plugin-demo' ),
		5 * HOUR_IN_SECONDS  => __( '5 Hours', 'easy-plugin-demo' ),
		6 * HOUR_IN_SECONDS  => __( '6 Hours', 'easy-plugin-demo' ),
		7 * HOUR_IN_SECONDS  => __( '7 Hours', 'easy-plugin-demo' ),
		8 * HOUR_IN_SECONDS  => __( '8 Hours', 'easy-plugin-demo' ),
		12 * HOUR_IN_SECONDS => __( '12 Hours', 'easy-plugin-demo' ),
		DAY_IN_SECONDS       => __( '1 Day', 'easy-plugin-demo' ),
		2 * DAY_IN_SECONDS   => __( '2 Days', 'easy-plugin-demo' ),
		3 * DAY_IN_SECONDS   => __( '3 Days', 'easy-plugin-demo' ),
		4 * DAY_IN_SECONDS   => __( '4 Days', 'easy-plugin-demo' ),
		5 * DAY_IN_SECONDS   => __( '5 Days', 'easy-plugin-demo' ),
		6 * DAY_IN_SECONDS   => __( '6 Days', 'easy-plugin-demo' ),
		WEEK_IN_SECONDS      => __( '1 Week', 'easy-plugin-demo' ),
		2 * WEEK_IN_SECONDS  => __( '2 Weeks', 'easy-plugin-demo' ),
		3 * WEEK_IN_SECONDS  => __( '3 Weeks', 'easy-plugin-demo' ),
		4 * WEEK_IN_SECONDS  => __( '4 Weeks', 'easy-plugin-demo' )
	);

	return apply_filters( 'epd_get_lifetime_options', $lifetime );
} // epd_get_lifetime_options
