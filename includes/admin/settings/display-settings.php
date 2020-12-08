<?php
/**
 * Admin Options Page
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
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since	1.0
 * @return	void
 */
function epd_options_page() {
	$all_settings  = epd_get_registered_settings();
	$settings_tabs = epd_get_settings_tabs();
	$settings_tabs = empty( $settings_tabs) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'sites';
	$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'sites';
	$sections      = epd_get_settings_tab_sections( $active_tab );
	$section        = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
	$section        = array_key_exists( $section, $sections ) ? $section : 'main';

	// Let's verify we have a 'main' section to show
	$has_main_settings = true;
	if ( empty( $all_settings[ $active_tab ]['main'] ) )	{
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings
	if ( ! $has_main_settings )	{
		foreach( $all_settings[ $active_tab ] as $sid => $stitle )	{
			if ( is_string( $sid ) && is_array( $sections ) && array_key_exists( $sid, $sections ) )	{
				continue;
			} else	{
				$has_main_settings = true;
				break;
			}
		}
	}

	$override = false;
	if ( false === $has_main_settings ) {
		unset( $sections['main'] );

		if ( 'main' === $section ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
					$section  = $section_key;
					$override = true;
					break;
				}
			}
		}
	}

    $has_premium_pack = epd_has_premium_pack();
    $promotions       = epd_get_current_promotions();
	$wrapper_class    = ( ! empty( $promotions ) || ! $has_premium_pack ) ? array( ' epd-has-sidebar' ) : array();

	ob_start();

	if ( isset( $_GET['updated'] ) ) : ?>
		<div id="message" class="updated notice is-dismissible">
			<p><?php _e( 'Settings saved.', 'easy-plugin-demo' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="wrap <?php echo 'wrap-' . $active_tab; ?>">
		<h1 class="nav-tab-wrapper">
			<?php
			foreach( epd_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'page'             => 'epd-settings',
					'settings-updated' => false,
					'tab'              => $tab_id
				), admin_url( 'network/settings.php' ) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}

            if ( ! get_site_option( 'epd_premium_version' ) )   {
                echo '<a href="https://easy-plugin-demo.com/premium-pack/" target="_blank" class="nav-tab epd-nav-tab-active">';
					_e( 'Go Premium', 'easy-plugin-demo' );
				echo '</a>';
            }
			?>
		</h1>
		<?php

		$number_of_sections = is_array( $sections ) ? count( $sections ) : 0;
		$number = 0;
		if ( $number_of_sections > 1 ) {
            echo '<div class="wp-clearfix">';
                echo '<div><ul class="subsubsub">';
                foreach( $sections as $section_id => $section_name ) {
                    echo '<li>';
                    $number++;
                    $tab_url = add_query_arg( array(
                        'page'             => 'epd-settings',
                        'settings-updated' => false,
                        'tab'              => $active_tab,
                        'section'          => $section_id
                    ), admin_url( 'network/settings.php' ) );

                    /**
                     * Allow filtering of the section URL.
                     *
                     * Enables plugin authors to insert links to non-setting pages as sections.
                     *
                     * @since	1.1.10
                     * @param	str		The section URL
                     * @param	str		The section ID (array key)
                     * @param	str		The current active tab
                     * @return	str
                     */
                    $tab_url = apply_filters( 'epd_options_page_section_url', $tab_url, $section_id, $active_tab );

                    $class = '';
                    if ( $section == $section_id ) {
                        $class = 'current';
                    }
                    echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

                    if ( $number != $number_of_sections ) {
                        echo ' | ';
                    }
                    echo '</li>';
                }
                echo '</ul></div>';
            echo '</div>';
		}
		?>
		<div class="epd-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
            <div class="epd-settings-content">
                <form method="post" action="<?php echo admin_url(); ?>" novalidate="novalidate">
                    <?php wp_nonce_field( 'epd_options', 'update_epd_options' ); ?>
                    <input type="hidden" name="epd_action" value="epd_update_settings">
                    <?php

                    settings_fields( 'epd_settings' );

                    if ( 'main' === $section ) {
                        do_action( 'epd_settings_tab_top', $active_tab );
                    }

                    do_action( 'epd_settings_tab_top_' . $active_tab . '_' . $section );

                    do_settings_sections( 'epd_settings_' . $active_tab . '_' . $section );

                    do_action( 'epd_settings_tab_bottom_' . $active_tab . '_' . $section  );

                    // For backwards compatibility
                    if ( 'main' === $section ) {
                        do_action( 'epd_settings_tab_bottom', $active_tab );
                    }

                    // If the main section was empty and we overrode the view with the next subsection, prepare the section for saving
                    if ( true === $override ) {
                        ?><input type="hidden" name="epd_section_override" value="<?php echo $section; ?>" /><?php
                    }
                    ?>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
            if ( ! empty( $promotions ) || ! $has_premium_pack ) {
                epd_options_sidebar( $promotions, $has_premium_pack );
            }
            ?>
		</div><!-- .epd-settings-wrap -->
	</div><!-- .wrap -->
	<?php

	echo ob_get_clean();
} // epd_options_page

/**
 * Display the sidebar
 *
 * @since   1.3.11
 * @return  string
 */
function epd_options_sidebar( $promotions, $premium ) {
	// Get settings tab and section info
	$active_tab     = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab     = array_key_exists( $active_tab, epd_get_settings_tabs() ) ? $active_tab : 'general';
	$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
	$active_section = array_key_exists( $active_section, epd_get_settings_tab_sections( $active_tab ) ) ? $active_section : 'main';
	$url            = 'https://easy-plugin-demo.com/downloads/epd-premium-pack/';
    $date_format    = 'H:i A F jS';

    foreach( $promotions as $code => $data ) : ?>
        <?php extract( $data ); ?>
        <div class="epd-settings-sidebar">
            <div class="epd-settings-sidebar-content">
                <div class="epd-sidebar-description-section">
                    <p class="epd-sidebar-description">
                        <?php printf(
                            __( 'Save %s when purchasing the %s <strong>this week</strong>. Including renewals and upgrades!', 'easy-plugin-demo' ),
                            $discount,
                            $product
                        ); ?>
                    </p>
                </div>
                <div class="epd-sidebar-coupon-section">
                    <label for="epd-coupon-code"><?php _e( 'Use code at checkout:', 'easy-plugin-demo' ); ?></label>
                    <input id="epd-coupon-code" type="text" value="<?php echo $code; ?>" readonly>
                    <p class="epd-coupon-note">
                        <?php printf(
                            __( 'Sale ends %s %s.', 'easy-plugin-demo' ),
                            date_i18n( $date_format, $finish ),
                            $timezone
                        ); ?>
                    </p>
                </div>
                <div class="epd-sidebar-footer-section">
                    <a class="epd-cta-button" href="<?php echo esc_url( $cta_url ); ?>" target="_blank"><?php echo $cta; ?></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ( empty( $promotions ) && ! $premium ) : ?>
        <div class="epd-settings-sidebar">
            <div class="epd-settings-sidebar-content">
				<div class="epd-sidebar-header-section">
					<?php _e( 'Go Premium', 'eady-plugin-demo' ); ?>
				</div>
                <div class="epd-sidebar-description-section epd-upsell">
					<ul>
						<li><?php _e( 'Demo templates', 'easy-plugin-demo' ); ?></li>
						<li><?php _e( 'Site cloning', 'easy-plugin-demo' ); ?></li>
						<li><?php _e( 'Zapier integration', 'easy-plugin-demo' ); ?></li>
						<li><?php _e( 'REST API', 'easy-plugin-demo' ); ?></li>
						<li><?php _e( 'and much more', 'easy-plugin-demo' ); ?></li>
					</ul>
                </div>
                <div class="epd-sidebar-footer-section">
                    <a class="epd-cta-button" href="https://easy-plugin-demo.com/downloads/epd-premium-pack/" target="_blank"><?php _e( 'Shop Now!', 'easy-plugin-demo' ); ?></a>
                </div>
            </div>
        </div>
        <?php endif;
} // epd_options_sidebar

/**
 * Output all settings sections and fields.
 *
 * @since	1.0
 * @return	void
*/
function epd_render_settings_fields() {
	if ( false == get_site_option( 'epd_settings' ) ) {
		add_site_option( 'epd_settings', array() );
	}

	foreach ( epd_get_registered_settings() as $option ) {
		$args = wp_parse_args( $option, array(
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

		$field_id = "epd_setting_{$args['id']}";

		?>
		<tr id="<?php echo $field_id; ?>">
			<th scope="row">
				<label for="<?php echo esc_attr( $args['id'] ); ?>">
					<?php echo esc_html( $args['name'] ); ?>
				</label>
			</th>
			<td>
				<?php
				if ( function_exists( "epd_{$args['type']}_callback" ) )   {
					call_user_func( "epd_{$args['type']}_callback", $args );
				} else  {
					epd_missing_callback( $args );
				}
				?>
			</td>
		</tr>
		<?php
	}
} // epd_render_settings_fields

/**
 * Display help text on licensing page.
 *
 * @since	1.3.11
 * @return	string
 */
function epd_license_tab_help_text()	{
	printf(
		'<h1 class="wp-heading-inline">%s</h1>',
		__( 'Manage Licenses', 'easy-plugin-demo' )
	);

	printf(
		'<a href="%s" target="_blank" class="page-title-action">%s</a>',
		'https://easy-plugin-demo.com/downloads/epd-premium-pack/',
		__( 'Visit Extension Store', 'easy-plugin-demo' )
	);
} // epd_license_tab_help_text
add_action( 'epd_settings_tab_top_licenses_main', 'epd_license_tab_help_text', 5 );
