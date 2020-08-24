<?php
/**
 * This template is used to display a registration confirmation
 */
$confirmation = __( 'Your {demo_product_name} demo is ready!', 'easy-plugin-demo' );

$confirmation = apply_filters( 'epd_site_registration_confirmation' );
$confirmation = epd_do_email_tags( $confirmation, $_GET['epd-registered'], get_current_user_id() );
?>
<div class="epd_alert epd_alert_success">
	<?php echo $confirmation; ?>
</div>