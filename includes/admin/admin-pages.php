<?php
/**
 * Admin Pages
 *
 * @package     EPD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Creates the admin submenu settings page within the MU network admin menu
 *
 * @since	1.0
 * @return	void
 */
function epd_add_options_link() {

    if ( ! is_super_admin() )   {
        return;
    }

	global $epd_settings_page;

	$epd_settings_page = add_submenu_page(
        'settings.php',
        __( 'EPD Settings', 'easy-plugin-demo' ),
        __( 'Easy Plugin Demo', 'easy-plugin-demo' ),
        'manage_sites',
        'epd-settings',
        'epd_options_page'
    );

} // epd_add_options_link
add_action( 'network_admin_menu', 'epd_add_options_link', 20 );

/**
 * Adds a reset site option to the tools menu
 *
 * @since	1.3
 * @return	void
 */
function epd_add_menu_items() {
	global $epd_reset_site_page;

	$primary_blog = get_network()->blog_id;
	$current_blog = get_current_blog_id();

	// Do not display the menu if in network admin, or is the main site
	if ( $primary_blog == $current_blog )	{
		return;
	}

	$primary_user_id = epd_get_site_primary_user_id( $current_blog );
	$current_user_id = get_current_user_id();
	$required_role   = epd_get_reset_site_cap_role();

	// Do not display the menu if not a site admin, or the original demo requestor
	if ( ! is_super_admin() && $current_user_id != $primary_user_id )	{
		return;
	}

	$show_menu = apply_filters( 'epd_show_reset_demo_menu_item', true, $current_blog );

	if ( ! $show_menu )	{
		return;
	}

	$reset_role = epd_get_reset_site_cap_role();

	$epd_reset_site_page = add_submenu_page(
        'tools.php',
        __( 'Reset Demo', 'easy-plugin-demo' ),
        __( 'Reset Demo', 'easy-plugin-demo' ),
        $reset_role,
        'epd_reset',
        'epd_output_reset_screen'
    );
} // epd_add_menu_items
add_action( 'admin_menu', 'epd_add_menu_items' );

/**
 * The site reset page.
 *
 * @since   1.3
 * @return  void
 */
function epd_output_reset_screen()  {
	$site_id  = isset( $_REQUEST['site_id'] ) ? absint( $_REQUEST['site_id'] ) : get_current_blog_id();
	$title    = __( 'Reset Demo', 'easy-plugin-demo' );
	$text_top = sprintf(
		__( 'If you want to restore your %s site to its original state, you can reset it using the form below. When you click <strong>Reset My Demo</strong> all customizations you have made since registering your demo will be erased including changes to;' ),
		get_network()->site_name
	);

    ?>
	<div class="wrap">
		<h1><?php _e( 'Reset Demo', 'easy-plugin-demo' ); ?></h1>
		<p><?php echo $text_top; ?></p>
		<ul>
			<li><?php _e( 'Themes and plugins', 'easy-plugin-demo' ); ?></li>
			<li><?php _e( 'Posts/pages', 'easy-plugin-demo' ); ?></li>
			<li><?php _e( 'Settings', 'easy-plugin-demo' ); ?></li>
			<li><?php _e( 'Users', 'easy-plugin-demo' ); ?></li>
		</ul>
		<p><?php _e( 'Remember, once reset your current demo cannot be restored and its expiration date will remain unchanged.', 'easy-plugin-demo' ); ?></p>

		<form method="post" name="epd-reset-demo">
			<?php wp_nonce_field( 'reset_site', 'epd_nonce' ); ?>
			<input type="hidden" name="epd_action" value="reset_site" />
			<input type="hidden" name="site_id" value="<?php echo $site_id; ?>" />
			
			<p><input id="epd-confirm-reset" type="checkbox" name="epd_confirm_reset" value="1" /> <label for="epd-confirm-reset"><strong>
			<?php
				_e( "I'm sure I want to reset my demo, and I am aware that all my changes will be lost.", 'easy-plugin-demo' );
			?>
			</strong></label></p>
			<?php submit_button( __( 'Reset My Demo', 'easy-plugin-demo' ) ); ?>
		</form>
	</div>
	<?php
} // epd_output_reset_screen
