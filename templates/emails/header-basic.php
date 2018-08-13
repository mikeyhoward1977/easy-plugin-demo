<?php
/**
 * Email Header (Basic)
 *
 * @author 		KB Support
 * @package 	KB Support/Templates/Emails
 * @version     1.1.10
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

$heading = EPD()->emails->get_heading();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
    <body>
    	<div>
			<?php if ( ! empty ( $heading ) ) : ?>
                <!-- Header -->
                <h1><?php echo $heading; ?></h1>
                <!-- End Header -->
            <?php endif; ?>