<?php
/**
 * Admin notices
 *
 * @package     EPD
 * @subpackage  Classes/Display Settings
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.11
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class EPD_Display_Settings	{

    /**
     * All out settings.
     *
     * @since   1.3.11
     * @var     array
     */
    public $all_settings = array();

	/**
     * Tabs.
     *
     * @since   1.3.11
     * @var     array
     */
	public $all_tabs = array();

	/**
     * Currently active tab.
     *
     * @since   1.3.11
     * @var     string
     */
	public $active_tab;

	/**
     * Tab sections.
     *
     * @since   1.3.11
     * @var     array
     */
	public $all_sections = array();

	/**
     * Currently active section.
     *
     * @since   1.3.11
     * @var     string
     */
	public $section = 'main';

	public $has_main_settings = true;
	public $override          = false;

	/**
     * Premium pack.
     *
     * @since   1.3.11
     * @var     bool
     */
	public $has_premium_pack = false;

	/**
     * Active promotions.
     *
     * @since   1.3.11
     * @var     array
     */
    public $promotions = array();

	/**
	 * Get things going.
	 */
	public function __construct()	{
		add_action( 'network_admin_menu', array( $this, 'add_options_link' ), 20 );
	} // __construct

	/**
	 * Add the settings page to the network menu.
	 *
	 * @since	1.3.11
	 * @return	void
	 */
	public function add_options_link()	{
		add_submenu_page(
			'settings.php',
			__( 'EPD Settings', 'easy-plugin-demo' ),
			__( 'Easy Plugin Demo', 'easy-plugin-demo' ),
			'manage_sites',
			'epd-settings',
			array( $this, 'options_page' )
		);
	} // add_options_link

	/**
	 * Setup options screen.
	 *
	 * @since	1.3.11
	 * @return	void
	 */
	public function setup_options()	{
		$this->all_settings  = epd_get_registered_settings();
		$this->all_tabs      = epd_get_settings_tabs();
		$this->all_tabs      = empty( $this->all_tabs ) ? array() : $this->all_tabs;
		$this->active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'sites';
		$this->active_tab    = array_key_exists( $this->active_tab, $this->all_tabs ) ? $this->active_tab : 'sites';
		$this->all_sections  = epd_get_settings_tab_sections( $this->active_tab );
		$this->section       = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
		$this->section       = array_key_exists( $this->section, $this->all_sections ) ? $this->section : 'main';

		// Verify we have a 'main' section to show
		if ( empty( $this->all_settings[ $this->active_tab ]['main'] ) ) {
			$this->has_main_settings = false;
		}

		// Check for old non-sectioned settings
		if ( ! $this->has_main_settings )	{
			foreach( $this->all_settings[ $this->active_tab ] as $sid => $stitle )	{
				if ( is_string( $sid ) && is_array( $this->all_sections ) && array_key_exists( $sid, $this->all_sections ) )	{
					continue;
				} else	{
					$this->has_main_settings = true;
					break;
				}
			}
		}

		if ( false === $this->has_main_settings ) {
			unset( $this->all_sections['main'] );

			if ( 'main' === $this->section ) {
				foreach ( $this->all_sections as $section_key => $section_title ) {
					if ( ! empty( $this->all_settings[ $this->active_tab ][ $section_key ] ) ) {
						$this->section  = $section_key;
						$this->override = true;
						break;
					}
				}
			}
		}

		$this->has_premium_pack = epd_has_premium_pack();
		$this->promotions       = epd_get_current_promotions();
	} // setup_options

    /**
	 * Output the primary navigation (tabs).
	 *
	 * @since	1.3.11
	 * @return	string
	 */
	public function output_primary_nav()	{
		ob_start();?>

        <nav class="nav-tab-wrapper epd-nav-tab-wrapper epd-settings-nav" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-plugin-demo' ); ?>">
            <?php

            foreach ( $this->all_tabs as $tab_id => $tab_name ) {
                $tab_url = add_query_arg( array(
					'page'             => 'epd-settings',
					'settings-updated' => false,
					'tab'              => $tab_id
				), admin_url( 'network/settings.php' ) );

                // Remove the section from the tabs so we always end up at the main section
                $tab_url   = remove_query_arg( 'section', $tab_url );
                $tab_class = $this->active_tab == $tab_id ? ' nav-tab-active' : '';

                // Link
                printf(
                    '<a href="%s" class="nav-tab%s">%s</a>',
                    esc_url( $tab_url ),
                    $tab_class,
                    esc_html( $tab_name )
                );
            }
            ?>
        </nav>

        <?php echo ob_get_clean();
	} // output_primary_nav

    /**
     * Output the secondary navigation
     *
     * @since   1.3.11
     * @return	string
     */
    function output_secondary_nav() {
        // No sections, bail
        if ( empty( $this->all_sections ) || 1 === count( $this->all_sections ) ) {
            return;
        }

        // Default links array
        $links = array();

        // Loop through sections
        foreach ( $this->all_sections as $section_id => $section_name ) {
            // Tab & Section
           $tab_url = add_query_arg( array(
                'page'             => 'epd-settings',
                'settings-updated' => false,
                'tab'              => $this->active_tab,
                'section'          => $section_id
            ), admin_url( 'network/settings.php' ) );

            // Settings not updated
            $tab_url = remove_query_arg( 'settings-updated', $tab_url );

            // Class for link
            $class = ( $this->section === $section_id ) ? 'current' : '';

            // Add to links array
            $links[ $section_id ] = sprintf(
                '<li class="%1$s"><a class="%1$s" href="%2$s">%3$s</a><li>',
                esc_attr( $class ),
                esc_url( $tab_url ),
                esc_html( $section_name )
            );
        } ?>

        <div class="wp-clearfix">
            <ul class="subsubsub epd-settings-sub-nav">
                <?php echo implode( '&#124;', $links ); ?>
            </ul>
        </div>

        <?php
    } // output_secondary_nav

    /**
     * Output the options form.
     *
     * @since   1.3.11
     * @return  string
     */
    public function output_form()   {
        // Setup the action & section suffix
        $suffix          = ! empty( $this->section ) ? $this->active_tab . '_' . $this->section : $this->active_tab . '_main';
        $wrapper_class   = ! empty( $this->promotions || ! $this->has_premium_pack ) ? array( ' epd-has-sidebar' ) : array();
        ?>

        <div class="epd-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
            <div class="epd-settings-content">
                <form method="post" action="<?php echo admin_url(); ?>" class="epd-settings-form">
                    <?php wp_nonce_field( 'epd_options', 'update_epd_options' ); ?>
                    <input type="hidden" name="epd_action" value="epd_update_settings">
                    <?php

                    settings_fields( 'epd_settings' );

                    if ( 'main' === $this->section ) {
                        do_action( 'epd_settings_tab_top', $this->active_tab );
                    }

                    do_action( 'edd_settings_tab_top_' . $suffix );

                    do_settings_sections( 'epd_settings_' . $suffix );

                    do_action( 'epd_settings_tab_bottom_' . $suffix  );

                    // For backwards compatibility
                    if ( 'main' === $this->section ) {
                        do_action( 'epd_settings_tab_bottom', $this->active_tab );
                    }

                    // If the main section was empty and we overrode the view with the
                    // next subsection, prepare the section for saving
                    if ( true === $this->override ) {
                        ?><input type="hidden" name="epd_section_override" value="<?php echo esc_attr( $this->section ); ?>" /><?php
                    }

                    submit_button(); ?>
                </form>
            </div>
            <?php $this->output_sidebar(); ?>
        </div>
        <?php
    } // output_form

	/**
	 * Output the settings pages.
	 *
	 * @since	1.3.11
	 * @return	string
	 */
	public function options_page()	{
		$this->setup_options();

        ob_start();

        $this->maybe_display_notice(); ?>

        <div class="wrap <?php echo 'wrap-' . esc_attr( $this->active_tab ); ?>">
            <h1><?php _e( 'Settings', 'easy-plugin-demo' ); ?></h1><?php
            // Primary nav
            $this->output_primary_nav();

            // Secondary nav
            $this->output_secondary_nav();

            // Form
            $this->output_form();
        ?></div><!-- .wrap --><?php

        // Output the current buffer
        echo ob_get_clean();
	} // options_page

    /**
	 * Output the sidebar.
	 *
	 * @since	1.3.11
	 * @return	string
	 */
	public function output_sidebar()	{
		if ( empty( $this->promotions ) && $this->has_premium_pack ) {
            return;
        }

        $url            = 'https://easy-plugin-demo.com/downloads/epd-premium-pack/';
        $date_format    = 'H:i A F jS';

        foreach( $this->promotions as $code => $data ) : ?>
            <?php extract( $data ); ?>
            <div class="epd-settings-sidebar">
                <div class="epd-settings-sidebar-content">
                    <div class="epd-sidebar-header-section">
                        <?php if ( ! empty( $image ) )  {
                            printf(
                                '<img class="edd-bfcm-header" src="%s">',
                                esc_url( EPD_PLUGIN_URL . "assets/images/promo/{$image}" )
                            );
                        } else  {
                            echo $name;
                        } ?>
                    </div>
                    <div class="epd-sidebar-description-section">
                        <p class="epd-sidebar-description">
                            <?php if ( ! empty( $description ) )    {
                                echo $description;
                            } else  {
                                printf(
                                    __( 'Save %s when purchasing the %s <strong>this week</strong>. Including renewals and upgrades!', 'easy-plugin-demo' ),
                                    $discount,
                                    $product
                                );
                            } ?>
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
                        <a class="button button-primary epd-cta-button" href="<?php echo esc_url( $cta_url ); ?>" target="_blank"><?php echo $cta; ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ( empty( $this->promotions ) && ! $this->has_premium_pack ) : ?>
            <div class="epd-settings-sidebar">
                <div class="epd-settings-sidebar-content">
                    <div class="epd-sidebar-header-section">
                        <?php printf(
							'<img class="edd-bfcm-header" src="%s">',
							esc_url( EPD_PLUGIN_URL . 'assets/images/promo/go-premium-header.svg' )
						); ?>
                    </div>
                    <div class="epd-sidebar-description-section epd-upsell">
                        <p class="epd-sidebar-description">
                            <?php _e( 'Power up Easy Plugin Demo with great additional features including:', 'easy-plugin-demo' ); ?>
                        </p>
                        <ul>
                            <li><?php _e( 'Demo templates', 'easy-plugin-demo' ); ?></li>
                            <li><?php _e( 'Site cloning', 'easy-plugin-demo' ); ?></li>
                            <li><?php _e( 'Zapier integration', 'easy-plugin-demo' ); ?></li>
                            <li><?php _e( 'REST API', 'easy-plugin-demo' ); ?></li>
                            <li><?php _e( 'and much more', 'easy-plugin-demo' ); ?></li>
                        </ul>
                    </div>
                    <div class="epd-sidebar-footer-section epd-sidebar-promo-footer">
                        <a class="button button-primary epd-cta-button" href="https://easy-plugin-demo.com/downloads/epd-premium-pack/" target="_blank"><?php _e( 'Shop Now!', 'easy-plugin-demo' ); ?></a>
                    </div>
                </div>
            </div>
        <?php endif;
	} // output_sidebar

    /**
     * Display notice.
     *
     * @since   1.3.11
     */
    public function maybe_display_notice()    {
        if ( isset( $_GET['updated'] ) ) : ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e( 'Settings saved.', 'easy-plugin-demo' ); ?></p>
            </div>
        <?php endif;
    } // maybe_display_notice
} // EPD_Display_Settings

new EPD_Display_Settings;
