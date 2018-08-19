var epd_admin_vars;
jQuery(document).ready(function ($) {

	/**
	 * Disable changing search engine settings.
	 *
	 * Hides the "Search Engine Visibility".
	 *
	 * @since	1.0.1
	 */
	var epd_search = $('#blog_public');
	if ( epd_search.length > 0 && ! epd_admin_vars.super_admin && ! epd_admin_vars.primary_site && epd_admin_vars.hide_blog_public )	{
			$('.option-site-visibility').hide();
	}

	/**
	 * Load the example welcome panel text into the setting field.
	 *
	 * @since	1.0.1
	 */
	$('#epd-welcome-example').on('click', function(e) {
		e.preventDefault();

		var postData = {
			action : 'epd_example_welcome_panel_text'
		};

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: ajaxurl,
			success: function (response) {
				if ( true === response.success )	{
					window.send_to_editor( response.data.welcome );
				}
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
	});

});
