var epd_vars;
jQuery(document).ready(function ($) {

	/* = Registration form validation and submission
	====================================================================================== */
	$(document).on('click', '#epd-register-submit', function(e) {
		var epdRegistrationForm = document.getElementById('epd_register_form');

		if ( typeof epdRegistrationForm.checkValidity === 'function' && false === epdRegistrationForm.checkValidity() ) {
			return;
		}

		e.preventDefault();
		$(this).val(epd_vars.submit_register_loading);
		$(this).prop('disabled', true);
		$(this).after(' <span id="epd-loading" class="epd-loader"><img src="' + epd_vars.ajax_loader + '" /></span>');
        $('.epd_alert_error').html('');
        $('.epd_alert_error').hide('fast');
		$('input').removeClass('error');

		var $form    = $('#epd_register_form');
		var formData = $('#epd_register_form').serialize();

		$.ajax({
			type       : 'POST',
			dataType   : 'json',
			data       : formData,
			url        : epd_vars.ajaxurl,
			success    : function (response) {
				if ( response.success )	{
					$form.append( '<input type="hidden" name="epd_action" value="register_user" />' );
					$form.get(0).submit();
				} else	{
                    $form.find('.epd_alert_error').show('fast');
					$form.find('.epd_alert_error').html(response.data.error);
                    $('input[name=' + response.data.field + ']').addClass('error');
					$('#epd-register-submit').val(epd_vars.submit_register);
					$('#epd-loading').remove();
					$('#epd-register-submit').prop('disabled', false);
				}
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

	});
});
