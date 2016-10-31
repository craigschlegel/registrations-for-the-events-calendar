jQuery(document).ready(function($) {

    $('.rtec-js-show').show();
    $('.rtec-js-hide').hide();

    $('#rtec-form-toggle-button').on('click', function() {
        $('.rtec-toggle-on-click').toggle('slow');
        if ($(this).hasClass('tribe-bar-filters-open')) {
            $(this).removeClass('tribe-bar-filters-open');
        } else {
            $(this).addClass('tribe-bar-filters-open');
        }
    });

    var Form = {

        validClass : 'rtec-valid',

        invalidClass : 'rtec-error',

        showErrorMessage : function(formEl){
            var $formField = formEl.closest($('.rtec-input-wrapper'));
            if (!$formField.find('.rtec-error-message').length) {
                $formField.append('<p class="rtec-error-message" role="alert">'+formEl.closest($('.rtec-form-field')).attr('data-rtec-error-message')+'</p>');
            }
            formEl.attr('aria-invalid','true');
        },

        removeErrorMessage : function(formEl){
            formEl.closest($('.rtec-input-wrapper')).find('.rtec-error-message').remove();
            formEl.attr('aria-invalid','false');
        },

        addScreenReaderError : function(){
            $('#rtec .rtec-form-wrapper').prepend('<div class="rtec-screen-reader rtec-screen-reader-error" role="alert" aria-live="assertive">There were errors with your submission. Please try again.</div>');
        },

        validateLength : function(formEl, min, max){
            if (formEl.val().length > max || formEl.val().length < min ) {
                if (formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            } else {
                if (formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            }
        },

        validateEmail : function(formEl) {
            var regEx = /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/,
                emailTest = regEx.test(formEl.val());
            if (emailTest) {
                if (formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            }
        },

        validateCount : function(formEl, validCountArr){

            var strippedNumString = formEl.val().replace(/\D/g,''),
                formElCount = strippedNumString.length,
                validCountNumbers = validCountArr.map(function(x) {
                    return parseInt(x);
                }),
                countTest = validCountNumbers.indexOf(formElCount);

            if (countTest !== -1) {
                if (formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            }
        },

        validateSum : function(formEl, val1, val2 ){

            var eqTest = (parseInt(val1) === parseInt(val2));

            if (eqTest) {
                if (formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            }
        }

    };
    
    $('#rtec-form').submit(function(event) {
        event.preventDefault();

        if ($('#rtec .rtec-screen-reader-error').length) {
            $('#rtec .rtec-screen-reader-error').remove();
        }

        var required = [];

        $('#rtec #rtec-form :input').each(function() {
            if ($(this).attr('aria-required') == 'true') {
                if ($(this).attr('name') == 'rtec_email') {
                    Form.validateEmail($(this));
                } else if ($(this).attr('name') == 'rtec_phone') {
                    Form.validateCount($(this), $(this).closest('.rtec-form-field').attr('data-rtec-valid-count').replace(' ', '').split(','));
                } else if ($(this).attr('name') == 'rtec_recaptcha_input') {
                    Form.validateSum($(this), $(this).val(), $(this).closest('.rtec-form').find('.rtec-recaptcha-sum').val());
                } else {
                    Form.validateLength($(this), 2, 25);
                }
            }
        });

        if (!$('#rtec #rtec-form .rtec-error').length) {
            $('.rtec-spinner').show();
            $('.rtec-form-wrapper #rtec-form, .rtec-form-wrapper p').fadeTo(500,.1);
            $('#rtec-form-toggle-button').css('visibility','hidden');

            var submittedData = {};

            $('#rtec #rtec-form :input').each(function() {
                var name = $(this).attr('name');
                var val = $(this).val();
                submittedData[name] = val;
            });

            submittedData['action'] = 'rtec_process_form_submission';

            $.ajax({
                url : rtec.ajaxUrl,
                type : 'post',
                data : submittedData,
                success : function(data) {

                    $('.rtec-spinner, #rtec-form-toggle-button').hide();
                    $('.rtec-form-wrapper').slideUp();
                    $('html, body').animate({
                        scrollTop: $('#rtec').offset().top - 200
                    }, 750);

                    if (data !== 'full') {
                        $('#rtec').prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">'+$('#rtec').attr('data-rtec-success-message')+'</p>');
                    } else {
                        $('#rtec').prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">Sorry! Registrations just filled up for this event. You are not registered</p>');
                    }

                }
            }); // ajax
        } else { // if not .rtec-error
            Form.addScreenReaderError();
        } // if not .rtec-error
    }); // on rtec-form submit


});