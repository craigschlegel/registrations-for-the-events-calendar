jQuery(document).ready(function($){


    // FORM tab
    $('.rtec_require_checkbox').change(function(){
        if ($(this).is(':checked')) {
            console.log('checked');
            $(this).closest('.rtec-checkbox-row').find('.rtec_include_checkbox').prop( "checked", true );
        }
    });

    $('.rtec_include_checkbox').change(function(){
        if (!$(this).is(':checked')) {
            $(this).closest('.rtec-checkbox-row').find('.rtec_require_checkbox').prop( "checked", false );
        }
    });

    String.prototype.replaceAll = function(search, replacement) {
        var target = this;
        return target.replace(new RegExp(search, 'g'), replacement);
    };

    var $rtecJsPreview = $('#rtec_js_preview').find('pre'),
        $rtecConfirmationTextarea = $('#confirmation_message_textarea'),
        typingTimer,
        doneTypingInterval = 1500;
    function updateText() {
        var confirmationMessage = $rtecConfirmationTextarea.val();
        confirmationMessage = confirmationMessage.replaceAll('{venue}', 'MI6 Headquarters');
        confirmationMessage = confirmationMessage.replaceAll('{event-title}', 'Top Secret Meeting');
        confirmationMessage = confirmationMessage.replaceAll('{event-date}', 'July 3');
        confirmationMessage = confirmationMessage.replaceAll('{first}', 'James');
        confirmationMessage = confirmationMessage.replaceAll('{last}', 'Bond');
        confirmationMessage = confirmationMessage.replaceAll('{email}', 'Bond007@ohmss.com');
        confirmationMessage = confirmationMessage.replaceAll('{other}', 'Shaken not Stirred');
        confirmationMessage = confirmationMessage.replaceAll('{nl}', "\n");
        $rtecJsPreview.text(confirmationMessage);
    }
    if ( $rtecConfirmationTextarea.length){
        updateText();
    }
    $rtecConfirmationTextarea.keyup(function(){
        clearTimeout(typingTimer);
        typingTimer = setTimeout(updateText, doneTypingInterval);
    });
    
    function rtecRegistrationAjax(submitData,successFunc) {
        $.ajax({
            url: rtecAdminScript.ajax_url,
            type: 'post',
            data: submitData,
            success: successFunc
        });
    }

    $('.rtec-delete-registration').on('click', function() {
        var idsToRemove = [];
        $('.rtec-registration-select').each(function() {
            if ($(this).is(':checked')) {
                idsToRemove.push($(this).val());
                $(this).closest('.rtec-reg-row').addClass('rtec-being-removed');
            }
        });
        // if registrations_to_be_deleted is not empty
        if (idsToRemove.length) {
            // give a warning to the user that this cannot be undone
            if (confirm(idsToRemove.length + ' registrations to be deleted. This cannot be undone.')) {
                // start spinner to show user that request is processing
                $('.rtec-single table tbody')
                    .after('<div class="rtec-table-changing spinner is-active"></div>')
                    .fadeTo("slow", .2);

                var submitData = {
                    action: 'rtec_delete_registrations',
                    registrations_to_be_deleted: idsToRemove,
                    rtec_nonce : rtecAdminScript.rtec_nonce
                },
                successFunc = function () {
                    // remove deleted entries
                    $('.rtec-being-removed').each(function () {
                        $(this).remove();
                    });
                    // remove spinner
                    $('.rtec-table-changing').remove();
                    $('.rtec-single table tbody').fadeTo("fast", 1);
                    idsToRemove = [];
                };
                rtecRegistrationAjax(submitData,successFunc);

            } else {
                idsToRemove = [];
                $('.rtec-being-removed').each(function() {
                    $(this).removeClass('rtec-being-removed');
                });
            } // if user confirms delete registrations
        } // if registrations to be deleted is not empty
    }); // delete submit click

    $('.rtec-edit-registration').click( function() {
        var editCount = 0;

        if (! $('.rtec-submit-edit').length) {
            $('.rtec-registration-select').each(function() {
                if ($(this).is(':checked') && editCount < 1) {
                    var $closestRegRow = $(this).closest('.rtec-reg-row'),
                        dateStr = $closestRegRow.find('.rtec-reg-date').text(),
                        date = $closestRegRow.find('.rtec-reg-date').attr('data-rtec-submit'),
                        lastName = $closestRegRow.find('.rtec-reg-last').text(),
                        firstName = $closestRegRow.find('.rtec-reg-first').text(),
                        email = $closestRegRow.find('.rtec-reg-email').text(),
                        other = $closestRegRow.find('.rtec-reg-other').text();

                    editCount = 1;

                    if (! $('.rtec-submit-edit').length) {
                        $closestRegRow.find('.rtec-reg-date').html('<button data-rtec-val="'+dateStr+'" data-rtec-submit="'+date+'" class="button-primary rtec-submit-edit">Submit Edit</button>');
                    }

                    $closestRegRow.find('.rtec-reg-last').html('<input type="text" name="last" id="rtec-last" data-rtec-val="'+lastName+'" value="'+lastName+'" />');
                    $closestRegRow.find('.rtec-reg-first').html('<input type="text" name="first" id="rtec-first" data-rtec-val="'+firstName+'" value="'+firstName+'" />');
                    $closestRegRow.find('.rtec-reg-email').html('<input type="text" name="email" id="rtec-email" data-rtec-val="'+email+'" value="'+email+'" />');
                    $closestRegRow.find('.rtec-reg-other').html('<input type="text" name="other" id="rtec-other" data-rtec-val="'+other+'" value="'+other+'" />');

                    $(this).addClass('rtec-editing');

                    $('.rtec-edit-registration').text('Undo');
                }
            });
        } else {
            var $rtecEditing = $('.rtec-editing'),
                $editingClosestRegRow = $rtecEditing.closest('.rtec-reg-row');

            function addBackRowData($row,findEl,inputEl) {
                var html = $editingClosestRegRow.find(inputEl).attr('data-rtec-val');
                $row.find(findEl).html(html);
            }

            addBackRowData($editingClosestRegRow,'.rtec-reg-date','.rtec-reg-date button');
            addBackRowData($editingClosestRegRow,'.rtec-reg-last','.rtec-reg-last input');
            addBackRowData($editingClosestRegRow,'.rtec-reg-first','.rtec-reg-first input');
            addBackRowData($editingClosestRegRow,'.rtec-reg-email','.rtec-reg-email input');
            addBackRowData($editingClosestRegRow,'.rtec-reg-other','.rtec-reg-other input');

            $rtecEditing.removeClass('rtec-editing');

            $('.rtec-edit-registration').text('Edit Selected');

        }

    }); // edit registration click

    var $body = $('body');
    $body.on('click', '.rtec-submit-edit', function () {
        var $table = $(this).closest('table');
        // start spinner to show user that request is processing
        $('.rtec-single table tbody')
            .after('<div class="rtec-table-changing spinner is-active"></div>')
            .fadeTo("slow", .2);

        var submitData = {
                action : 'rtec_update_registration',
                rtec_id: $table.find('.rtec-editing').val(),
                rtec_registration_date: $table.find('.rtec-reg-date').attr('data-rtec-val'),
                rtec_other: $table.find('input[name=other]').val(),
                rtec_first: $table.find('input[name=first]').val(),
                rtec_email: $table.find('input[name=email]').val(),
                rtec_last: $table.find('input[name=last]').val(),
                rtec_nonce : rtecAdminScript.rtec_nonce
            },
            successFunc = function () {
                //reload the page on success to show the added registration
                location.reload();
            };
        rtecRegistrationAjax(submitData,successFunc);
    }); // registration submit

    $('.rtec-add-registration').click( function() {
        var $table = $(this).closest('.tablenav').prev(),
            $nav = $table.next();
        // remove if input fields already displayed
        if ($table.find('.rtec-new-registration').length) {
            $nav.find('.rtec-add-registration').text('+ Add New Registration');
            $table.find('.rtec-new-registration').remove();
            // otherwise show the input fields
        } else {
            $nav.find('.rtec-add-registration').text('- Remove Add New Registration');
            $table.find('tbody')
                .append(
                    '<tr class="format-standard rtec-new-registration">' +
                        '<td></td>' +
                        '<td><button class="button-primary rtec-submit-new">Submit Entry</button></td>' +
                        '<td><input type="text" name="last" id="last" placeholder="Last" /></td>' +
                        '<td><input type="text" name="first" id="first" placeholder="First" /></td>' +
                        '<td><input type="email" name="email" id="email" placeholder="you@example.com" /></td>' +
                        '<td><input type="text" name="other" id="other" placeholder="Other" /></td>' +
                    '</tr>'
                );
        }
    });

    $body.on('click', '.rtec-submit-new', function () {
        var $table = $(this).closest('.tablenav').prev();
        // start spinner to show user that request is processing
        $('.rtec-single table tbody')
            .after('<div class="rtec-table-changing spinner is-active"></div>')
            .fadeTo("slow", .2);

        var submitData = {
                action : 'rtec_add_registration',
                rtec_event_id: $('.rtec-single-event').attr('data-rtec-event-id'),
                rtec_other: $table.find('input[name=other]').val(),
                rtec_first: $table.find('input[name=first]').val(),
                rtec_email: $table.find('input[name=email]').val(),
                rtec_last: $table.find('input[name=last]').val(),
                rtec_venue_title: $table.closest('.rtec-single-event').find('.rtec-venue-title').text(),
                rtec_end_time: $table.closest('.rtec-single-event').find('.rtec-end-time').text(),
                rtec_nonce : rtecAdminScript.rtec_nonce
            },
            successFunc = function () {
                //reload the page on success to show the added registration
                location.reload();
            };
        rtecRegistrationAjax(submitData,successFunc);
    }); // registration submit
});
