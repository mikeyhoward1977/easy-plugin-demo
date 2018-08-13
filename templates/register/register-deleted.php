<?php
/**
 * This template is used to display a site deletion confirmation
 */

$class        = 'success';
$confirmation = __( 'The site was deleted successfully.', 'easy-plugin-demo' );

if ( empty( $_GET['epd-result'] ) )	{
	$class        = 'error';
	$confirmation = __( 'The site could not be deleted.', 'easy-plugin-demo' );
}

?>
<div class="epd_alert epd_alert_<?php echo $class; ?>">
	<?php echo $confirmation; ?>
</div>