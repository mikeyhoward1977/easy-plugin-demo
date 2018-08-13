<?php
/**
 * Email Header
 *
 * @author 		KB Support
 * @package 	KB Support/Templates/Emails
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// This is the header used if no others are available
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
	<body>