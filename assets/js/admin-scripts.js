var epd_admin_vars;
jQuery(document).ready(function ($) {

	// Setup Chosen menus
	$('.epd_select_chosen').chosen({
		inherit_select_classes: true,
		placeholder_text_single: epd_admin_vars.one_option,
		placeholder_text_multiple: epd_admin_vars.one_or_more_option
	});

	$('.epd_select_chosen .chosen-search input').each( function() {
		var selectElem = $(this).parent().parent().parent().prev('select.epd_select_chosen'),
			placeholder = selectElem.data('search-placeholder');
		$(this).attr( 'placeholder', placeholder );
	});

	// Add placeholders for Chosen input fields
	$( '.chosen-choices' ).on( 'click', function () {
		var placeholder = $(this).parent().prev().data('search-placeholder');
		if ( typeof placeholder === 'undefined' ) {
			placeholder = epd_admin_vars.type_to_search;
		}
		$(this).children('li').children('input').attr( 'placeholder', placeholder );
	});

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

    /**
     * Disable reset button until confirmation is selected.
     *
     * @since   1.3
     */
    $('#epd-confirm-reset').click(function(){
        //If the checkbox is checked.
        if ( $(this).is(':checked') ){
            //Enable the submit button.
            $('#epd-reset-submit').attr('disabled', false);
        } else{
            //If it is not checked, disable the button.
            $('#epd-reset-submit').attr('disabled', true);
        }
    });

});
