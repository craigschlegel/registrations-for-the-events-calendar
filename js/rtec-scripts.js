jQuery(document).ready(function($) {

    $('.rtec-js-show').show();
    $('.rtec-js-hide').hide();

    $('.rtec-form-toggle-button').on('click', function() {
        $rtecEl = $(this).closest('.rtec');
        $rtecEl.find('.rtec-toggle-on-click').toggle('slow');
        if ($(this).hasClass('tribe-bar-filters-open')) {
            $(this).removeClass('tribe-bar-filters-open');
        } else {
            $(this).addClass('tribe-bar-filters-open');
        }
    });

    var RtecForm = {

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
                if (formEl.hasClass(RtecForm.validClass)) {
                    formEl.removeClass(RtecForm.validClass);
                }
                formEl.addClass(RtecForm.invalidClass);
                RtecForm.showErrorMessage(formEl);
            } else {
                if (formEl.hasClass(RtecForm.invalidClass)) {
                    formEl.removeClass(RtecForm.invalidClass);
                }
                formEl.addClass(RtecForm.validClass);
                RtecForm.removeErrorMessage(formEl);
            }
        },

        validateOption : function($input){

            var eqTest = false;

            if (!$input.find('option').length) {
                if ($input.is(':checked')) {
                    eqTest = true;
                }
                var formEl = $input.closest('.rtec-input-wrapper');
            } else {
                if ($input.find('option:selected').val() !== '') {
                    eqTest = true;
                }
                var formEl = $input;
            }

            if (eqTest) {
                if (formEl.hasClass(RtecForm.invalidClass)) {
                    formEl.removeClass(RtecForm.invalidClass);
                }
                formEl.addClass(RtecForm.validClass);
                RtecForm.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(RtecForm.validClass)) {
                    formEl.removeClass(RtecForm.validClass);
                }
                formEl.addClass(RtecForm.invalidClass);
                RtecForm.showErrorMessage(formEl);
            }
        },

        isValidEmail : function(val) {
            var regEx = /[^\s@]+@[^\s@]+\.[^\s@]+/;

            return regEx.test(val);
        },

        validateEmail : function(formEl) {
            if (RtecForm.isValidEmail(formEl.val()) && !formEl.closest('form').find('#rtec-error-duplicate').length) {
                if (formEl.hasClass(RtecForm.invalidClass)) {
                    formEl.removeClass(RtecForm.invalidClass);
                }
                formEl.addClass(RtecForm.validClass);
                RtecForm.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(RtecForm.validClass)) {
                    formEl.removeClass(RtecForm.validClass);
                }
                formEl.addClass(RtecForm.invalidClass);
                RtecForm.showErrorMessage(formEl);
            }
        },

        validateCount : function(formEl, validCountArr){

            var strippedNumString = formEl.val().replace(/\D/g,''),
                formElCount = strippedNumString.length,
                validCountNumbers = validCountArr.map(function(x) {
                    return parseInt(x);
                }),
                countTest = validCountNumbers.indexOf(formElCount);

            // if the valid counts is blank, allow any entry that contains at least one number
            if (validCountArr[0] === '') {
                countTest = formElCount - 1;
            }

            if (countTest !== -1) {
                if (formEl.hasClass(RtecForm.invalidClass)) {
                    formEl.removeClass(RtecForm.invalidClass);
                }
                formEl.addClass(RtecForm.validClass);
                RtecForm.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(RtecForm.validClass)) {
                    formEl.removeClass(RtecForm.validClass);
                }
                formEl.addClass(RtecForm.invalidClass);
                RtecForm.showErrorMessage(formEl);
            }
        },

        validateSum : function(formEl, val1, val2 ){

            var eqTest = (parseInt(val1) === parseInt(val2));

            if (eqTest) {
                if (formEl.hasClass(RtecForm.invalidClass)) {
                    formEl.removeClass(RtecForm.invalidClass);
                }
                formEl.addClass(RtecForm.validClass);
                RtecForm.removeErrorMessage(formEl);
            } else {
                if (formEl.hasClass(RtecForm.validClass)) {
                    formEl.removeClass(RtecForm.validClass);
                }
                formEl.addClass(RtecForm.invalidClass);
                RtecForm.showErrorMessage(formEl);
            }
        },

        enableSubmitButton : function(_callback,$context) {
            if (_callback()) {
                $context.find('input[name=rtec_submit]').removeAttr('disabled').css('opacity',1);
            }
        },

        isDuplicateEmail : function(email,eventID,$context) {
            var $emailEl = $context.find('input[name=rtec_email]'),
                $spinnerImg = $('.rtec-spinner').length ? $('.rtec-spinner').html() : '',
                $spinner = '<span class="rtec-email-spinner">'+$spinnerImg+'</span>';

            $emailEl.attr('disabled',true)
                .css('opacity',.5)
                .closest('div').append($spinner);

            var submittedData = {
                'action': 'rtec_registrant_check_for_duplicate_email',
                'event_id': eventID,
                'email': email
            };

            $.ajax({
                url : rtec.ajaxUrl,
                type : 'post',
                data : submittedData,
                success : function(data) {

                    if (data.indexOf('<p class=') > -1) {
                        RtecForm.removeErrorMessage($emailEl);
                        if ($emailEl.hasClass(RtecForm.validClass)) {
                            $emailEl.removeClass(RtecForm.validClass);
                        }
                        $emailEl.addClass(RtecForm.invalidClass);
                        $emailEl.closest($('.rtec-input-wrapper')).append(data);
                    } else if (data === 'not') {
                        RtecForm.removeErrorMessage($emailEl);
                        if ($emailEl.hasClass(RtecForm.validClass)) {
                            $emailEl.removeClass(RtecForm.validClass);
                        }
                        $emailEl.addClass(RtecForm.invalidClass);
                        var $formField = $emailEl.closest($('.rtec-input-wrapper'));
                        if (!$formField.find('.rtec-error-message').length) {
                            $formField.append('<p class="rtec-error-message" role="alert">'+$emailEl.closest($('.rtec-form-field')).attr('data-rtec-error-message')+'</p>');
                        }
                        $emailEl.attr('aria-invalid','true');
                    } else {
                        if ($emailEl.hasClass(RtecForm.invalidClass)) {
                            $emailEl.removeClass(RtecForm.invalidClass);
                        }
                        $emailEl.addClass(RtecForm.validClass);
                        RtecForm.removeErrorMessage($emailEl);
                    }
                    $context.find('input[name=rtec_submit]').removeAttr('disabled').css('opacity',1);
                    $emailEl.removeAttr('disabled')
                        .css('opacity',1);
                    $context.find('.rtec-email-spinner').remove();

                }
            }); // ajax

        }

    };

    if (typeof rtec.checkForDuplicates !== 'undefined' && rtec.checkForDuplicates === '1') {
        var $rtecEmailField = $('input[name=rtec_email]'),
            typingTimer,
            doneTypingInterval = 1500;
        $rtecEmailField.keyup(function(){
            var $this = $(this),
                $context = $this.closest('.rtec');
            $context.find('input[name=rtec_submit]').attr('disabled',true).css('opacity','.5');
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                var $eventID = $context.find('input[name=rtec_event_id]').val();
                RtecForm.enableSubmitButton(function() {
                    RtecForm.isDuplicateEmail($this.val(), $eventID, $context);
                },$context);
            }, doneTypingInterval);
        });
        $rtecEmailField.each(function() {
            var $this = $(this),
                $context = $this.closest('.rtec'),
                $eventID = $context.find('input[name=rtec_event_id]').val();
            if (RtecForm.isValidEmail($this.val())) {
                RtecForm.isDuplicateEmail($this.val(),$eventID,$context);
            }
        });
    }
    
    $('.rtec-form').submit(function(event) {
        event.preventDefault();

        $rtecEl = $(this).closest('.rtec');
        $rtecEl.find('input[name=rtec_submit]').attr('disabled',true);

        if ($rtecEl.find('.rtec-screen-reader-error').length) {
            $rtecEl.find('.rtec-screen-reader-error').remove();
        }

        var required = [];

        $rtecEl.find('#rtec-form .rtec-form-field').each(function() {
            var $input = $(this).find('.rtec-field-input');
            if ($input.attr('aria-required') == 'true') {
                if ($input.attr('data-rtec-valid-email') == 'true') {
                    RtecForm.validateEmail($input);
                } else if (typeof $input.attr('data-rtec-valid-count') == 'string') {
                    RtecForm.validateCount($input, $input.attr('data-rtec-valid-count').replace(' ', '').split(','), $input.attr('data-rtec-count-what'));
                } else if (typeof $input.attr('data-rtec-recaptcha') == 'string') {
                    RtecForm.validateSum($input, $input.val(), $input.closest('.rtec-form').find('input[name='+$input.attr('name')+'_sum]').val());
                } else if ($input.attr('data-rtec-valid-options') == 'true') {
                    RtecForm.validateOption($input);
                } else {
                    RtecForm.validateLength($input, 1, 10000);
                }
            }
        });

        if (!$rtecEl.find('.rtec-error').length && !$rtecEl.find('#rtec-error-duplicate').length) {
            $rtecEl.find('.rtec-spinner').show();
            $rtecEl.find('.rtec-form-wrapper #rtec-form, .rtec-form-wrapper p').fadeTo(500,.1);
            $rtecEl.find('#rtec-form-toggle-button').css('visibility','hidden');

            var submittedData = {};

            $rtecEl.find('#rtec-form :input').each(function() {
                var name = $(this).attr('name');
                if ($(this).attr('type') === 'checkbox') {
                    if ($(this).is(':checked')) {
                        submittedData[name] = $(this).val();
                    }
                } else {
                    submittedData[name] = $(this).val();
                }
            });

            submittedData['action'] = 'rtec_process_form_submission';
            if ($('input[name=lang]').length) {
                submittedData['lang'] = $('input[name=lang]').val();
            }

            $.ajax({
                url : rtec.ajaxUrl,
                type : 'post',
                data : submittedData,
                success : function(data) {

                    $rtecEl.find('.rtec-spinner, #rtec-form-toggle-button').hide();
                    $rtecEl.find('.rtec-form-wrapper').slideUp();
                    $('html, body').animate({
                        scrollTop: $rtecEl.offset().top - 200
                    }, 750);

                    if (data === 'full') {
                        $rtecEl.prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">Sorry! Registrations just filled up for this event. You are not registered</p>');
                    } else if (data === 'email') {
                        $rtecEl.prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">There was a problem sending the email confirmation. Please contact the site administrator to confirm your registration</p>');
                    } else if (data === 'form') {
                        $rtecEl.prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">There was a problem with one or more of the entries you submitted. Please try again</p>');
                    } else {
                        $rtecEl.prepend('<p class="rtec-success-message tribe-events-notices" aria-live="polite">'+$('#rtec').attr('data-rtec-success-message')+'</p>');
                    }
                    if (typeof rtecAfterSubmit === 'function') {
                        rtecAfterSubmit();
                    }

                }
            }); // ajax
        } else { // if not .rtec-error
            $rtecEl.find('input[name=rtec_submit]').removeAttr('disabled').css('opacity',1);
            RtecForm.addScreenReaderError();
            $('html, body').animate({
                scrollTop: $('.rtec-error-message').first().closest('.rtec-input-wrapper').offset().top - 200
            }, 750);
        } // if not .rtec-error
    }); // on rtec-form submit

    // hide options initially
    var $rtecReveal = $('.rtec-already-registered-reveal'),
        $rtecOptions = $('.rtec-already-registered-options.rtec-is-visitor'),
        $rtecOptionsRemove = $('.rtec-already-registered-js-remove');
    $rtecReveal.show();
    $rtecOptions.hide();
    $rtecOptionsRemove.remove();
    $rtecReveal.click(function() {
        if ($rtecOptions.is(':visible')) {
            $rtecOptions.slideUp();
        } else {
            $rtecOptions.slideDown();
        }
    });

});
