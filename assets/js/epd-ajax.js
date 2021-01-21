var epd_vars;
jQuery(document).ready(function ($) {

    /* = reCaptcha V3
	====================================================================================== */
    if ( $( '#recaptcha-action' ).length ) {
        epd_recaptcha_V3();
    }

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

/* = reCaptcha V3
====================================================================================== */
function epd_recaptcha_V3()  {
    var recaptcha_version  = epd_vars.recaptcha_version,
        recaptcha_site_key = epd_vars.recaptcha_site_key;

    if ( 'v3' === recaptcha_version && false !== recaptcha_site_key )  {
        grecaptcha.ready(function() {
            grecaptcha.execute(recaptcha_site_key, {
                action: 'submit_epd_form'
            }).then(function(token) {
                jQuery('#g-recaptcha-response').val( token );
                jQuery('#recaptcha-action').val( 'submit_epd_form' );
            });
        });

        setInterval(function () {
            grecaptcha.ready(function() {
                grecaptcha.execute(recaptcha_site_key, {
                    action: 'submit_epd_form'
                }).then(function(token) {
                    jQuery('#g-recaptcha-response').val( token );
                    jQuery('#recaptcha-action').val( 'submit_epd_form' );
                });
            });
        }, 90 * 1000);
    }
}
