/*---------------------------------------------------------------------
	Form Fields & Modal PopUp 
-----------------------------------------------------------------------*/

const loginbtn = document.querySelector(".btn-login");
const loginform = document.getElementById("user_login_form");
const forgetform = document.getElementById("user_forget_form");
const chnagepassform = document.getElementById("user_changepass");

function setDisplay(loginformDisplay, forgetpwdDisplay) {
	document.getElementById("user_loginform").style.display = loginformDisplay;
	document.getElementById("user_forgetpwd").style.display = forgetpwdDisplay;
}

if (loginbtn !== null) {
	loginbtn.addEventListener('click', () => {
		setDisplay("", "none");
	});
}

if (loginform !== null) {
	loginform.addEventListener('click', () => {
		setDisplay("", "none");
	});
}

if (forgetform !== null) {
	forgetform.addEventListener('click', () => {
		setDisplay("", "none");
	});
}

if (chnagepassform !== null) {
	chnagepassform.addEventListener('click', () => {
		setDisplay("none", "");
	});
}

(function ($) {
	"use strict";
	$(document).ready(function () {
		var getFormArray = ['.iqonic-login-form', '.iqonic-lost-password-form', '.iqonic-resend-verification-email-form'];
		$.each(getFormArray, function (index, value) {
			$(value).on('submit', function (e) {
				e.preventDefault();
				var form = $(this);
				// Show verifying loader text
				form.find('.iqonic-result-msg').fadeIn('slow').html('<div class="socialv-alert  socialv-alert-primary">' + socialv_ajax_login_params.loading_message + '</div>');
				var urlParams = new URLSearchParams(window.location.search);
				var formParameters = {
					action: 'socialv_ajax_login',
					loginUsername: form.find('[name="log"]').val(),
					formType: form.find('[name="iq_form_type"]').val(),
					loginPassword: form.find('[name="pwd"]').val(),
					loginRemember: form.find('[name="rememberme"]').val(),
					registrationUsername: form.find('[name="user_login"]').val(),
					registrationEmail: form.find('[name="user_email"]').val(),
					registrationPassword: form.find('[name="user_pass"]').val(),
					registrationConfirmPassword: form.find('[name="user_confirm_pass"]').val(),
					resetLogin: form.find('[name="login"]').val(),
					resetKey: form.find('[name="key"]').val(),
					resetPass1: form.find('[name="pass1"]').val(),
					resetPass2: form.find('[name="pass2"]').val(),
					resendEmail: form.find('[name=user_resend_email]').val(),
					redirect_to: urlParams.get('redirect_to') || ''
				};
				formParameters['g-recaptcha-response'] = form.find('[name="g-recaptcha-response"]').val();
				formParameters['wfls-captcha-token'] = form.find('[id="wfls-captcha-token]"]').val();
				formParameters['wfls-email-verification'] = form.find('[id="wfls-email-verification"]').val();
				$.ajax({
					type: 'POST',
					url: socialv_ajax_login_params.ajax_url,
					data: formParameters,
					dataType: 'json',
					success: function (data) {
						// Show success/error message
						if (data.status == 'login-success' || data.status == 'lost-password-success' || data.status == 'reset-password-success' || data.status == 'resend-verification-email-success') {
							form.find('.iqonic-result-msg').fadeIn('slow').html('<div class="socialv-alert  socialv-alert-success">' + data.message + '</div>');
						} else {
							form.find('.iqonic-result-msg').fadeIn('slow').html('<div class="socialv-alert  socialv-alert-danger">' + data.message + '</div>');
						}
						// // Upon successfull login, redirect

						if (data.status == 'login-success') {
							if (data.redirect != '') {
								window.location.href = data.redirect;
							} else {
								document.location.reload();
							}
						}

						// // Upon succesfull lost password submission
						if (data.status == 'lost-password-success') {
							form.find('.iqonic-data-input').hide();
						}

						// Upon succesfull password reset
						if (data.status == 'reset-password-success') {
							form.find('.iqonic-data-input').hide();
						}

					},
					error: function (xhr, status, error) {
						// Show error message
						form.find('.iqonic-result-msg').fadeIn('slow').html('<div class="socialv-alert  socialv-alert-danger">' + xhr.responseText + '</div>');

					}
				});
			});
		});
		$('#register_modal').on('hidden.bs.modal', function () {
			$(this).find('form').trigger('reset');
		});

	});
}(jQuery));