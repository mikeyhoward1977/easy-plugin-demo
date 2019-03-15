<?php
/**
 * Dashboard Functions
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Renders a custom dashboard welcome panel.
 *
 * @since	1.0.1
 * @return	string	Custom welcome panel content
 */
function epd_render_custom_welcome_panel()	{
	$user_id = get_current_user_id();
	$site_id = get_current_blog_id();

    if ( has_action( 'epd_welcome_panel_text' ) )   {
        do_action( 'epd_welcome_panel_text', $user_id, $site_id );
    } else  {
        $welcome = epd_get_option( 'custom_welcome' );
        $welcome = stripslashes( $welcome );
        $welcome = apply_filters( 'the_content', $welcome );
        $welcome = epd_do_email_tags( $welcome, $site_id, $user_id  );

        ?>
        <div class="welcome-panel-content">
            <?php echo $welcome; ?>
        </div>
        <?php
    }
} // epd_render_custom_welcome_panel

/**
 * Example welcome panel text.
 *
 * @since	1.0.1
 * @return	string	Example welcome panel text
 */
function epd_get_example_welcome_panel_text()	{
	$settings_url = add_query_arg( 'page', 'epd-settings', admin_url( 'network/settings.php' ) );
	ob_start(); ?>

	<style>
		.welcome-panel .welcome-register:before { content: "\f481"; }
		.welcome-panel .welcome-themes:before { content: "\f100"; }
		.welcome-panel .welcome-plugins:before { content: "\f106"; top: -3px; }
		.welcome-panel .welcome-docs:before { content: "\f118"; top: -2px; }
		.welcome-panel .welcome-rate:before { content: "\f155"; top: -2px; }
		.welcome-panel .welcome-support:before { content: "\f125"; top: -2px; }
	</style>

	<h2><?php _e( 'Welcome to the {demo_product_name} (EPD) Demo!', 'easy-plugin-demo' ); ?></h2>
	<p class="about-description"><?php _e( "We've provided some links below to help you get started quickly and easily", 'easy-plugin-demo' ); ?></p>

	<div class="welcome-panel-column-container">

		<div class="welcome-panel-column">
			<h3><?php _e( 'Get Started', 'easy-plugin-demo' ); ?></h3>
			<a class="button button-primary button-hero" href="<?php echo $settings_url; ?>"><?php _e( 'Configure EPD Settings', 'easy-plugin-demo' ); ?></a>
		</div>

		<div class="welcome-panel-column">
			<h3><?php _e( 'Next Steps', 'easy-plugin-demo' ); ?></h3>
			<ul>
				<li><?php printf( '<a href="%s" class="welcome-icon welcome-register">' . __( 'Create your registration page', 'easy-plugin-demo' ) . '</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
				<li><?php printf( '<a href="%s" class="welcome-icon welcome-themes">' . __( 'Install themes for your demo sites', 'easy-plugin-demo' ) . '</a>', admin_url( 'network/themes.php' ) ); ?></li>
				<li><?php printf( '<a href="%s" class="welcome-icon welcome-plugins">' . __( 'Install plugins for your demo sites', 'easy-plugin-demo' ) . '</a>', admin_url( 'network/plugins.php' ) ); ?></li>
			</ul>
		</div>

		<div class="welcome-panel-column welcome-panel-last">
			<h3><?php _e( 'More Actions', 'easy-plugin-demo' ); ?></h3>
			<ul>
				<li><?php printf( __( '<a href="%s" target="_blank" class="welcome-icon welcome-docs">View support documents</a>', 'easy-plugin-demo' ), 'https://easy-plugin-demo.com/support/' );?></li>
				<li><?php printf( __( '<a href="%s" target="_blank" class="welcome-icon welcome-support">Visit support forum</a>', 'easy-plugin-demo' ), 'https://wordpress.org/support/plugin/easy-plugin-demo' );?></li>
				<li><?php printf( __( '<a href="%s" target="_blank" class="welcome-icon welcome-rate">Add your review on WordPress.org</a>', 'easy-plugin-demo' ), 'https://wordpress.org/support/plugin/easy-plugin-demo/reviews/#new-post' );?></li>
		
			</ul>
		</div>

	</div>

	<?php return ob_get_clean();
} // epd_get_example_welcome_panel_text
