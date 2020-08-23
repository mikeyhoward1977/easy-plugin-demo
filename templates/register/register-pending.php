<?php
/**
 * This template is used to display a registration confirmation
 */
$confirmation = __( 'Your {demo_product_name} demo is waiting to be activated. Please check your email to activate and access it.', 'easy-plugin-demo' );
$confirmation = epd_do_email_tags( $confirmation, $_GET['epd-registered'], get_current_user_id() );
?>
<div class="epd_alert epd_alert_info">
	<?php echo $confirmation; ?>
</div>