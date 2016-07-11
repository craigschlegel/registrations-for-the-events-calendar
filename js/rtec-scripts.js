jQuery(document).ready(function($) {

    $('.rtec-js-show').show();
    $('.rtec-js-hide').hide();

    $('#rtec-form-toggle-button').on('click', function() {
        $('.rtec-toggle-on-click').toggle('slow');
        if($(this).hasClass('tribe-bar-filters-open')) {
            $(this).removeClass('tribe-bar-filters-open');
        } else {
            $(this).addClass('tribe-bar-filters-open');
        }
    });

    var Form = {

        validClass : 'rtec-valid',

        invalidClass : 'rtec-error',

        showErrorMessage : function(formEl){
            var $formField = formEl.closest($('.rtec-form-field'));
            if(!$formField.find('.rtec-error-message').length) {
                $formField.append('<p class="rtec-error-message" role="alert">'+$formField.attr('data-rtec-error-message')+'</p>');
            }
            formEl.attr('aria-invalid','true');
        },

        removeErrorMessage : function(formEl){
            formEl.closest($('.rtec-form-field')).find('.rtec-error-message').remove();
            formEl.attr('aria-invalid','false');
        },

        addScreenReaderError : function(){
            $('#rtec .rtec-form-wrapper').prepend('<div class="rtec-screen-reader rtec-screen-reader-error" role="alert" aria-live="assertive">There were errors with your submission. Please try again.</div>');
        },

        validateLength : function(formEl, min, max){
            if(formEl.val().length > max || formEl.val().length < min ) {
                if(formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            } else {
                if(formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            }
        },

        validateEmail : function(formEl){
            var regEx = /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/;
            var emailTest = regEx.test(formEl.val());
            if(emailTest) {
                if(formEl.hasClass(Form.invalidClass)) {
                    formEl.removeClass(Form.invalidClass);
                }
                formEl.addClass(Form.validClass);
                Form.removeErrorMessage(formEl);
            } else {
                if(formEl.hasClass(Form.validClass)) {
                    formEl.removeClass(Form.validClass);
                }
                formEl.addClass(Form.invalidClass);
                Form.showErrorMessage(formEl);
            }
        }

    };
    
    $('#rtec-form').submit(function(event){

        event.preventDefault();
        
        if($('#rtec .rtec-screen-reader-error').length) {
            $('#rtec .rtec-screen-reader-error').remove();
        }

        var required = [];

        $('#rtec #rtec-form :input').each(function() {
            if($(this).attr('aria-required') == 'true') {
                if($(this).attr('name') == 'rtec_email') {
                    Form.validateEmail($(this));
                } else {
                    Form.validateLength($(this), 2, 25);
                }
            }
        });

        if(!$('#rtec #rtec-form .rtec-error').length) {
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
                success : function() {

                    $('.rtec-spinner, #rtec-form-toggle-button').hide();
                    $('.rtec-form-wrapper').slideUp();
                    $('html, body').animate({
                        scrollTop: $('#tribe-events').offset().top
                    }, 200);
                    $('#rtec').prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">'+$('#rtec').attr('data-rtec-success-message')+'</p>');
                    
                }
            }); // ajax
        } else { // if not .rtec-error
            Form.addScreenReaderError();
        } // if not .rtec-error
    }); // on rtec-form submit


});