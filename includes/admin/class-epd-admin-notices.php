<?php
/**
 * Admin notices
 *
 * @package     EPD
 * @subpackage  Classes/Admin Notices
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.6
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class EPD_Admin_Notices	{

    /**
     * If Premium Pack is installed.
     *
     * @since   1.3.5
     * @var     bool
     */
    private $premium_pack = false;

	/**
	 * Get things going.
	 */
	public function __construct()	{
        $this->premium_pack = epd_has_premium_pack();

        // General notices
        add_action( 'network_admin_notices', array( $this, 'registration_page_notice' ) );

        // Site action notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Review notice
        add_action( 'plugins_loaded', array( $this, 'request_wp_5star_rating' ) );

        // Upselling notices
        if ( ! $this->premium_pack )    {
            add_action( 'plugins_loaded', array( $this, 'notify_premium_pack'     ) );
            add_action( 'plugins_loaded', array( $this, 'demo_templates_upsell'   ) );
        }
	} // __construct

    /**
	 * Notice to set registration page.
	 *
	 * @since	1.2
	 * @return	void
	 */
	public function registration_page_notice()	{
		$screen = get_current_screen();

		if ( 'settings-network' != $screen->id && ! epd_get_option( 'registration_page', false ) )	{
			ob_start(); ?>

			<div class="updated notice">
				<p>
					<?php printf(
						__( '<strong>Important!</strong> Your Easy Plugin Demo registration page is not defined. <a href="%s">Click here</a> to set it now.', 'easy-plugin-demo' ),
						add_query_arg( 'page', 'epd-settings', network_admin_url( 'settings.php' ) )
					); ?>
				</p>
			</div>

			<?php echo ob_get_clean();
		}
	} // registration_page_notice

    /**
     * Admin notices.
     *
     * @since	1.3.4
     * @return	void
     */
    public function admin_notices() {
		if ( is_network_admin() )	{
			return;
		}

        if ( isset( $_GET['epd-activated'] ) && 1 == $_GET['epd-activated'] ) {
            $message = __( '<strong>Demo Site Activated</strong>. Your demo has been activated and is now ready to use.', 'easy-plugin-demo' );
            $message = apply_filters( 'epd_site_activated_admin_notice', $message );
            ob_start(); ?>
            <div class="updated notice is-dismissible">
				<p>
					<?php echo $message; ?>
				</p>
			</div>
            <?php echo ob_get_clean();
        }
    } // admin_notices

    /**
     * Request 5 star rating after 15 sites have been registered via EPD.
     *
     * After 15 sites are registered via EPD we ask the admin for a 5 star rating on WordPress.org
     *
     * @since	1.0
     * @return	void
     */
    public function request_wp_5star_rating() {
		if ( ! is_network_admin() && get_network()->site_id != get_current_blog_id() )	{
			return;
		}
	
        if ( ! current_user_can( 'manage_network' ) )	{
            return;
        }

        if ( epd_is_notice_dismissed( 'epd_request_wp_5star_rating' ) )   {
            return;
        }

        $epd_registered = epd_get_registered_demo_sites_count();

        if ( $epd_registered > 15 ) {
            add_action( 'admin_notices', array( $this, 'admin_wp_5star_rating_notice' ) );
            add_action( 'network_admin_notices', array( $this, 'admin_wp_5star_rating_notice' ) );
        }
    } // request_wp_5star_rating

    /**
     * Notify users that a premium pack exists.
     *
     * After 3 sites are registered via EPD we notify admins of the premium pack
     *
     * @since	1.0
     * @return	void
     */
    public function notify_premium_pack() {
        if ( ! is_network_admin() && get_network()->site_id != get_current_blog_id() )	{
			return;
		}
	
        if ( ! current_user_can( 'manage_network' ) )	{
            return;
        }

        if ( epd_is_notice_dismissed( 'epd_upsell_premium_pack' ) )   {
            return;
        }

        $epd_registered = epd_get_registered_demo_sites_count();

        if ( $epd_registered > 2 ) {
            add_action( 'admin_notices', array( $this, 'admin_wp_premium_pack_upsell_notice' ) );
            add_action( 'network_admin_notices', array( $this, 'admin_wp_premium_pack_upsell_notice' ) );
        }
    } // notify_premium_pack

    /**
     * Notify users that demo templates are available in the Premium Pack.
     *
     * After 10 sites are registered via EPD we notify admins of the demo templates
     *
     * @since	1.3.5
     * @return	void
     */
    public function demo_templates_upsell() {
		if ( ! is_network_admin() && get_network()->site_id != get_current_blog_id() )	{
			return;
		}
	
        if ( ! current_user_can( 'manage_network' ) )	{
            return;
        }

        if ( epd_is_notice_dismissed( 'epd_demo_templates_upsell' ) )   {
            return;
        }

        $epd_registered = epd_get_registered_demo_sites_count();

        if ( $epd_registered >= 10 ) {
            add_action( 'admin_notices', array( $this, 'admin_wp_demo_templates_upsell_notice' ) );
            add_action( 'network_admin_notices', array( $this, 'admin_wp_demo_templates_upsell_notice' ) );
        }
    } // demo_templates_upsell

    /**
     * Admin WP Rating Request Notice
     *
     * @since	1.1
     * @return	void
    */
    function admin_wp_5star_rating_notice() {
        ob_start(); ?>

		<script>
		jQuery(document).ready(function ($) {
			// Dismiss admin notices
			$( document ).on( 'click', '.notice-epd-dismiss .notice-dismiss', function () {
				var notice = $( this ).closest( '.notice-epd-dismiss' ).data( 'notice' );

				var postData = {
					notice : notice,
					action : 'epd_dismiss_notice'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl
				});
			});
		});
		</script>

        <div class="updated notice notice-epd-dismiss is-dismissible" data-notice="epd_request_wp_5star_rating">
            <p>
                <?php _e( "<strong>Nice!</strong> More than 15 demo sites have been registered using Easy Plugin Demo!", 'easy-plugin-demo' ); ?>
            </p>
            <p>
                <?php printf(
                    __( 'Would you <strong>please</strong> do us a favor and leave a 5 star rating on WordPress.org? It only takes a minute and it <strong>really helps</strong> to keep us motivated towards continued development and support. <a href="%1$s" target="_blank">Sure thing, you deserve it!</a>', 'easy-plugin-demo' ),
                    'https://wordpress.org/support/plugin/easy-plugin-demo/reviews/'
                ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_wp_5star_rating_notice

    /**
     * Admin WP Upsell Premium Pack Notice
     *
     * @since	1.1
     * @return	void
    */
    function admin_wp_premium_pack_upsell_notice() {
        ob_start(); ?>

		<script>
		jQuery(document).ready(function ($) {
			// Dismiss admin notices
			$( document ).on( 'click', '.notice-epd-dismiss .notice-dismiss', function () {
				var notice = $( this ).closest( '.notice-epd-dismiss' ).data( 'notice' );

				var postData = {
					notice : notice,
					action : 'epd_dismiss_notice'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl
				});
			});
		});
		</script>

        <div class="updated notice notice-epd-dismiss is-dismissible" data-notice="epd_upsell_premium_pack">
            <p>
                <?php printf(
                    __( '<strong>Go Premium with Easy Plugin Demo!</strong> Purchase our <a href="%1$s" target="_blank">Premium Pack</a> extension to enable additional features such as demo site templates, EDD integration, Woocommerce integration, Zapier integration, button shortcodes, site cloning, post duplication, enhanced user management and much more.', 'easy-plugin-demo' ),
                    'https://easy-plugin-demo.com/downloads/epd-premium-pack/'
                ); ?>
            </p>
            <p>
                <?php printf(
                    __( '<a href="%1$s" target="_blank">Click here</a> for more information and to secure a %2$s discount.', 'easy-plugin-demo' ),
                    'https://easy-plugin-demo.com/downloads/epd-premium-pack/?discount=15OFFNOW',
                    '15%'
                ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_wp_premium_pack_upsell_notice

    /**
     * Admin WP Upsell Demo Templates Notice
     *
     * @since	1.3.5
     * @return	void
    */
    function admin_wp_demo_templates_upsell_notice() {
        ob_start(); ?>

		<script>
		jQuery(document).ready(function ($) {
			// Dismiss admin notices
			$( document ).on( 'click', '.notice-epd-dismiss .notice-dismiss', function () {
				var notice = $( this ).closest( '.notice-epd-dismiss' ).data( 'notice' );

				var postData = {
					notice : notice,
					action : 'epd_dismiss_notice'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl
				});
			});
		});
		</script>

        <div class="updated notice notice-epd-dismiss is-dismissible" data-notice="epd_demo_templates_upsell">
            <p>
                <?php printf(
                    __( 'Demo templates enable you to create multiple demo site templates, each with fully customized settings, content, plugins and themes. Showcase all your products and content in one place! Purchase the <a href="%1$s" target="_blank">EPD Premium Pack</a> now.', 'easy-plugin-demo' ),
                    'https://easy-plugin-demo.com/downloads/epd-premium-pack/?discount=15OFFNOW'
                ); ?>
            </p>
            <p>
                <?php printf(
                    __( '<a class="button button-small button-primary" href="%1$s" target="_blank">Click here</a> for more information and to secure a %2$s discount.', 'easy-plugin-demo' ),
                    'https://easy-plugin-demo.com/downloads/epd-premium-pack/?discount=15OFFNOW',
                    '15%'
                ); ?>
            </p>
        </div>

        <?php echo ob_get_clean();
    } // admin_wp_demo_templates_upsell_notice

    /**
     * Check if there is a notice to dismiss.
     *
     * @since	1.3.5
     * @param	array	$data	Contains the notice ID
     * @return	void
     */
    public function grab_notice_dismiss( $data ) {
        $notice_id = isset( $data['notice_id'] ) ? $data['notice_id'] : false;

        if ( false === $notice_id ) {
            return;
        }

        epd_dismiss_notice( $notice_id );
    } // grab_notice_dismiss

    /**
     * Dismisses admin notices when Dismiss links are clicked
     *
     * @since	1.3.5
     * @return	void
    */
    function dismiss_notices() {
        $notice = isset( $_GET['epd_notice'] ) ? $_GET['epd_notice'] : false;

        if ( ! $notice )	{
            return;
        }

        epd_dismiss_notice( $notice );

        wp_redirect( remove_query_arg( array( 'epd_action', 'epd_notice' ) ) );
        exit;
    } // dismiss_notices

} // EPD_Admin_Notices

new EPD_Admin_Notices;
