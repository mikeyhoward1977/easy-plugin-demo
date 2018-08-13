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

	if ( ! current_user_can( 'manage_network_options' ) )	{
		wp_die(
			'<h1>' . __( 'Permissions Error', 'easy-plugin-demo' ) . '</h1>' .
			'<p>'  . __( 'You are not allowed to update EPD options.', 'easy-plugin-demo' ) . '</p>',
			403
		);
	}

	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
	$all_settings = epd_get_registered_settings();

	ob_start();

	if ( isset( $_GET['updated'] ) ) : ?>
		<div id="message" class="updated notice is-dismissible">
			<p><?php _e( 'Settings saved.', 'easy-plugin-demo' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="wrap">
		<h1><?php echo get_admin_page_title(); ?></h1>
        <form method="post" action="<?php echo admin_url(); ?>" novalidate="novalidate">
            <?php wp_nonce_field( 'epd_options', 'update_epd_options' ); ?>
            <input type="hidden" name="epd_action" value="epd_update_settings">
            <table class="form-table">
            <?php

            do_action( 'epd_settings_top' );

            epd_render_settings_fields();

            do_action( 'epd_settings_bottom' );

            ?>
            </table>
            <?php submit_button(); ?>
        </form>
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
} // epd_options_page

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

		?>
		<tr>
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
