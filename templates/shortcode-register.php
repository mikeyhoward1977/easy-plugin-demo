<?php
/**
 * This template is used to display the registration page with [epd_register]
 */
global $epd_register_redirect;

if ( is_user_logged_in() ) :
	if ( ! empty( $_GET['epd-message'] ) ) :
		epd_get_template_part( 'register/register', sanitize_text_field( $_GET['epd-message'] ) );
	endif;

	epd_get_template_part( 'register/list', 'sites' );
endif;

epd_get_template_part( 'register/register', 'form' );
