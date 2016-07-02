jQuery(document).ready(function($){

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

                $.ajax({
                    url: rtecAdminScript.ajax_url,
                    type: 'post',
                    data: {
                        action: 'rtec_delete_registrations',
                        registrations_to_be_deleted: idsToRemove,
                        rtec_nonce : rtecAdminScript.rtec_nonce
                    },
                    success: function () {
                        // remove deleted entries
                        $('.rtec-being-removed').each(function () {
                            $(this).remove();
                        });
                        // remove spinner
                        $('.rtec-table-changing').remove();
                        $('.rtec-single table tbody').fadeTo("fast", 1);
                        idsToRemove = [];
                    }
                }); // ajax call
            } else {
                idsToRemove = [];
            } // if user confirms delete registrations
        } // if registrations to be deleted is not empty
    }); // delete submit click

    $('.rtec-edit-registration').click( function() {
        var editCount = 0;

        if (! $('.rtec-submit-edit').length) {
            $('.rtec-registration-select').each(function() {
                if ($(this).is(':checked') && editCount < 1) {
                    var $closestRegRow = $(this).closest('.rtec-reg-row'),
                        date = $closestRegRow.find('.rtec-reg-date').text(),
                        lastName = $closestRegRow.find('.rtec-reg-last').text(),
                        firstName = $closestRegRow.find('.rtec-reg-first').text(),
                        email = $closestRegRow.find('.rtec-reg-email').text(),
                        other = $closestRegRow.find('.rtec-reg-other').text();

                    editCount = 1;

                    if (! $('.rtec-submit-edit').length) {
                        $closestRegRow.find('.rtec-reg-date').html('<button data-rtec-val="'+date+'" class="button-primary rtec-submit-edit">Submit Edit</button>');
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
            var $editingClosestRegRow = $('.rtec-editing').closest('.rtec-reg-row');

            $editingClosestRegRow.find('.rtec-reg-date').html($editingClosestRegRow.find('.rtec-reg-date button').attr('data-rtec-val'));
            $editingClosestRegRow.find('.rtec-reg-last').html($editingClosestRegRow.find('.rtec-reg-last input').attr('data-rtec-val'));
            $editingClosestRegRow.find('.rtec-reg-first').html($editingClosestRegRow.find('.rtec-reg-first input').attr('data-rtec-val'));
            $editingClosestRegRow.find('.rtec-reg-email').html($editingClosestRegRow.find('.rtec-reg-email input').attr('data-rtec-val'));
            $editingClosestRegRow.find('.rtec-reg-other').html($editingClosestRegRow.find('.rtec-reg-other input').attr('data-rtec-val'));

            $('.rtec-editing').removeClass('rtec-editing');

            $('.rtec-edit-registration').text('Edit Selected');

        }

    }); // edit registration click

    $('body').on('click', '.rtec-submit-edit', function () {
        var $table = $(this).closest('table');
        // start spinner to show user that request is processing
        $('.rtec-single table tbody')
            .after('<div class="rtec-table-changing spinner is-active"></div>')
            .fadeTo("slow", .2);

        // submit the entry with ajax
        $.ajax({
            url: rtecAdminScript.ajax_url,
            type: 'post',
            data : {
                action : 'rtec_update_registration',
                rtec_id: $table.find('.rtec-editing').val(),
                rtec_other: $table.find('input[name=other]').val(),
                rtec_first: $table.find('input[name=first]').val(),
                rtec_email: $table.find('input[name=email]').val(),
                rtec_last: $table.find('input[name=last]').val(),
                rtec_nonce : rtecAdminScript.rtec_nonce
            },
            success : function() {
                //reload the page on success to show the added registration
                location.reload();
            }
        }); // ajax call
    }); // registration submit

    $('.rtec-add-registration').click( function() {
        $table = $(this).closest('.tablenav').prev();
        var $nav = $table.next();
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

    $('body').on('click', '.rtec-submit-new', function () {
        // start spinner to show user that request is processing
        $('.rtec-single table tbody')
            .after('<div class="rtec-table-changing spinner is-active"></div>')
            .fadeTo("slow", .2);

        // submit the entry with ajax
        $.ajax({
            url: rtecAdminScript.ajax_url,
            type: 'post',
            data : {
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
            success : function() {
                //reload the page on success to show the added registration
                location.reload();
            }
        }); // ajax call
    }); // registration submit
});
