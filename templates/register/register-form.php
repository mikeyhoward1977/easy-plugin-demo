<?php
/**
 * This template is used to display the registration form
 */
global $epd_register_redirect;

$user = false;

if ( is_user_logged_in() )	{
	$user = get_userdata( get_current_user_id() );
}

$user_id      = $user ? $user->ID         : '';
$firstname    = $user ? $user->first_name : '';
$lastname     = $user ? $user->last_name  : '';
$email        = $user ? $user->user_email : '';
$readonly     = $user ? ' readonly'       : '';
$can_register = $user ? epd_can_user_register( $user_id ) : true;

$register_to_activate = __( 'Register to Activate your Demo', 'easy-plugin-demo' );
$register_to_activate = apply_filters( 'epd_registration_form_heading', $register_to_activate );
$launch_demo_label    = epd_get_register_form_submit_label();
$limit_reached        = __( 'You have reached the limit for how many sites you may have active at any time. You can register a new site when one of your existing sites has expired.', 'easy-plugin-demo' );

$display_firstname = apply_filters( 'epd_register_display_firstname', true );
$display_lastname  = apply_filters( 'epd_register_display_lastname', true );

if ( $can_register ) :
	do_action( 'epd_notices' );
	do_action( 'epd_register_form_top' ); ?>
	<h3 class="epd_register_head"><?php echo $register_to_activate; ?></h3>
	<form id="epd_register_form" class="epd_form" action="" method="post">
		<div class="epd_alert epd_alert_error epd_hidden"></div>
		<?php do_action( 'epd_register_form_fields_top' ); ?>

		<fieldset>
			<?php do_action( 'epd_register_form_fields_before' ); ?>

            <?php if ( $display_firstname ) : ?>

			<p>
				<label for="epd-firstname"><?php _e( 'First Name', 'easy-plugin-demo' ); ?></label>
				<input id="epd-firstname" class="required epd-input" type="text" name="epd_first_name" title="<?php esc_attr_e( 'First Name', 'easy-plugin-demo' ); ?>" value="<?php echo esc_attr( $firstname ); ?>"<?php echo $readonly; ?> />
			</p>

            <?php endif; ?>

            <?php if ( $display_lastname ) : ?>

			<p>
				<label for="epd-lastname"><?php _e( 'Last Name', 'easy-plugin-demo' ); ?></label>
				<input id="epd-lastname" class="required epd-input" type="text" name="epd_last_name" title="<?php esc_attr_e( 'Last Name', 'easy-plugin-demo' ); ?>" value="<?php echo esc_attr( $lastname ); ?>"<?php echo $readonly; ?> />
			</p>

            <?php endif; ?>

			<p>
				<label for="epd-email"><?php _e( 'Email', 'easy-plugin-demo' ); ?></label>
				<input id="epd-email" class="required epd-input" type="email" name="epd_email" title="<?php esc_attr_e( 'Email Address', 'easy-plugin-demo' ); ?>" value="<?php echo esc_attr( $email ); ?>"<?php echo $readonly; ?> />
			</p>

			<?php do_action( 'epd_register_form_fields_before_submit' ); ?>

			<p>
				<input class="button" id="epd-register-submit" name="epd_register_submit" type="submit" value="<?php echo $launch_demo_label ?>" />
			</p>

			<?php do_action( 'epd_register_form_fields_after' ); ?>
		</fieldset>

		<?php do_action( 'epd_register_form_fields_bottom' ); ?>
	</form>

    <?php do_action( 'epd_register_form_bottom' ); ?>

<?php else : ?>
	<div class="epd_alert epd_alert_warn">
    	<?php echo $limit_reached; ?>
    </div>
<?php endif; ?>
