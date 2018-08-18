var epd_admin_vars;
jQuery(document).ready(function ($) {

	// Disable changing search engine settings
	var epd_search = $('#blog_public');
	if ( epd_search.length > 0 && ! epd_admin_vars.super_admin && ! epd_admin_vars.primary_site && epd_admin_vars.hide_blog_public )	{
			$('.option-site-visibility').hide();
	}

});