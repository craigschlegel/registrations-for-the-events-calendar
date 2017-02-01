<?php

// create a custom WP_Query object just for events
$id = (int)$_GET['id'];
$show = isset( $_GET['show'] ) ? (int)$_GET['show'] : 0;

?>
<h1><?php _e( 'Single Event Details', 'registrations-for-the-events-calendar' ); ?></h1>
<a href="<?php echo esc_url( 'edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations' ); ?>"><?php _e( 'Back to Overview', 'registrations-for-the-events-calendar' ); ?></a>

<input type="hidden" value="<?php echo esc_attr( $id ); ?>" name="event_id">

    <div class="rtec-wrapper rtec-single">
        <?php
                $db = new RTEC_Db_Admin();
                global $rtec_options;

                $data = array(
                    'fields' => 'registration_date, id, last_name, first_name, email, phone, other, custom',
                    'id' => $id,
                    'order_by' => 'registration_date'
                );

                $registrations = $db->retrieve_entries( $data, ( $show === 1 ), 300 );

                // set post meta
                $meta = get_post_meta( $id );

                $event_meta['post_id'] = $id;
                $event_meta['title'] = get_the_title( $id );
                $event_meta['start_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventStartDate'][0] ) );
                $event_meta['end_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventEndDate'][0] ) );
                $default_disabled = isset( $rtec_options['disable_by_default'] ) ? $rtec_options['disable_by_default'] : false;
                $event_meta['disabled'] = isset( $meta['_RTECregistrationsDisabled'][0] ) ? $meta['_RTECregistrationsDisabled'][0] : $default_disabled;
                $event_meta['num_registered'] = isset( $meta['_RTECnumRegistered'][0] ) ? $meta['_RTECnumRegistered'][0] : 0;

                // set venue meta
                $venue_meta = isset( $meta['_EventVenueID'][0] ) ? get_post_meta( $meta['_EventVenueID'][0] ) : array();
                $venue = rtec_get_venue( $id );
                $event_meta['venue_title'] = ! empty( $venue ) ? $venue : '(no location)';
                $bg_color_style = rtec_get_attendance_bg_color( $event_meta['num_registered'] );

                $labels = rtec_get_event_columns( ( $show === 1 ) );
                ?>

                <div class="rtec-single-event" data-rtec-event-id="<?php echo esc_attr( $id ); ?>">

                    <div class="rtec-event-meta">
                        <h3><?php echo get_the_title( $id ); ?></h3>
                        <p><?php echo esc_html( $event_meta['start_date'] ); ?> to <span class="rtec-end-time"><?php echo esc_html( $event_meta['end_date'] ); ?></span></p>
                        <p class="rtec-venue-title"><?php echo esc_html( $event_meta['venue_title'] ); ?></p>

                        <?php
                        $max_registrations_text = '';
                        if ( isset( $rtec_options['limit_registrations'] ) && $rtec_options['limit_registrations'] == true ) {
                            $max_registrations_text = ' &#47; ' . $rtec_options['default_max_registrations'];;
                        }
                        ?>
                        <p class="rtec-reg-info" style="<?php echo $bg_color_style; ?>"><?php echo $event_meta['num_registered'] . $max_registrations_text; ?></p>

                    </div>

                    <table class="widefat wp-list-table fixed striped posts rtec-registrations-data">
                        <thead>
                            <tr>
                                <td scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'registrations-for-the-events-calendar' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </td>
                                <th><?php _e( 'Registration Date', 'registrations-for-the-events-calendar' ) ?></th>
                            <?php foreach ( $labels as $label ) : ?>
                                <?php if ( ! empty( $label ) ) : ?>
                                <th><?php echo esc_html( $label ); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </tr>
                        </thead>
                        <?php if ( ! empty( $registrations ) ) : ?>
                        <tbody>
                            <?php foreach( $registrations as $registration ): ?>
                                <?php $custom_fields = rtec_get_parsed_custom_field_data( $registration['custom'] ); ?>
                                <tr class="rtec-reg-row" data-rtec-id="<?php echo esc_attr( (int)$registration['id'] ); ?>">
                                    <td scope="row" class="check-column rtec-checkbox">
                                        <label class="screen-reader-text" for="rtec-select-<?php echo esc_attr( (int)$registration['id'] ); ?>">Select <?php echo esc_html( $registration['first_name'] ) . ' ' . esc_html( $registration['last_name'] ); ?></label>
                                        <input type="checkbox" value="<?php echo esc_attr( (int)$registration['id'] ); ?>" id="rtec-select-<?php echo esc_attr( (int)$registration['id'] ) ?>" class="rtec-registration-select check-column">
                                        <div class="locked-indicator"></div>
                                    </td>
                                    <td class="rtec-data-cell rtec-reg-date" data-rtec-submit="<?php echo esc_attr( $registration['registration_date'] ); ?>"><?php echo esc_html( date_i18n( 'F jS, g:i a', strtotime( $registration['registration_date'] )+ $tz_offset ) ); ?></td>
                                    <td class="rtec-data-cell rtec-reg-last"><?php echo esc_html( str_replace( '\\', '', $registration['last_name'] ) ); ?></td>
                                    <td class="rtec-data-cell rtec-reg-first"><?php echo esc_html( str_replace( '\\', '', $registration['first_name'] ) ); ?></td>
                                    <td class="rtec-data-cell rtec-reg-email"><?php echo esc_html( str_replace( '\\', '', $registration['email'] ) ); ?></td>
                                    <td class="rtec-data-cell rtec-reg-phone"><?php echo esc_html( rtec_format_phone_number( $registration['phone'] ) ); ?></td>
                                    <td class="rtec-data-cell rtec-reg-other"><?php echo esc_html( str_replace( '\\', '', $registration['other'] ) ); ?></td>
                                    <?php if ( $show === 1 ) {
                                        echo '<td class="rtec-data-cell rtec-reg-custom-all">';
                                        $custom_array = maybe_unserialize( $registration['custom'] );
                                        if ( is_array( $custom_array ) ) {
                                            foreach ( $custom_array as $key => $value ) {
                                                echo '<p>' . esc_html( $key ) . ': ' . str_replace( '\\', '', esc_html( $value ) ) . '</p>';
                                            }
                                        } else {
                                            echo $registration['custom'];
                                        }
                                        echo '</td>';
                                    } elseif ( is_array( $custom_fields ) ) {
                                        foreach ( $custom_fields as $key => $value ) {
                                            if ( ! empty( $key ) ) { ?>
                                                <td class="rtec-data-cell rtec-reg-custom" data-rtec-key="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( str_replace( '\\', '', $value ) ); ?></td>
                                            <?php }
                                        }
                                    } ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if ( count( $registrations ) > 14 ) : ?>
                        <tfoot>
                            <tr>
                                <td scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'registrations-for-the-events-calendar' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </td>
                                <th><?php _e( 'Registration Date', 'registrations-for-the-events-calendar' ) ?></th>
                                <?php foreach ( $labels as $label ) : ?>
                                    <?php if ( ! empty( $label ) ) : ?>
                                        <th><?php echo esc_html( $label ); ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                        <?php else: ?>
                        <tbody>
                            <tr>
                                <td colspan="6" align="center"><?php _e( 'No Registrations Yet', 'registrations-for-the-events-calendar' ); ?></td>
                            </tr>
                        </tbody>
                        <?php endif; // registrations not empty?>
                    </table>
                    <div class="rtec-event-actions rtec-clear">
                        <div class="tablenav">
                            <button class="button action rtec-admin-secondary-button rtec-delete-registration">- <?php _e( 'Delete Selected', 'registrations-for-the-events-calendar'  ); ?></button>
                            <button class="button action rtec-admin-secondary-button rtec-edit-registration"><?php _e( 'Edit Selected', 'registrations-for-the-events-calendar'  ); ?></button>
                            <button class="button action rtec-admin-secondary-button rtec-add-registration">+ <?php _e( 'Add New', 'registrations-for-the-events-calendar'  ); ?></button>

                            <form method="post" id="rtec_csv_export_form" action="">
                                <?php wp_nonce_field( 'rtec_csv_export', 'rtec_csv_export_nonce' ); ?>
                                <input type="hidden" name="rtec_id" value="<?php echo esc_attr( $id ); ?>" />
                                <input type="submit" name="rtec_event_csv" class="button action rtec-admin-secondary-button" value="<?php _e( 'Export (.csv)', 'registrations-for-the-events-calendar' ); ?>" />
                            </form>
                            <?php if ( $show === 1 ) : ?>
                            <a href="<?php echo esc_url( 'edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=' . $id ); ?>" class="rtec-admin-secondary-button button action"><?php _e( 'View Current Custom Data', 'registrations-for-the-events-calendar' ); ?></a>
                            <?php else : ?>
                            <a href="<?php echo esc_url( 'edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=' . $id . '&show=1' ); ?>" class="rtec-admin-secondary-button button action"><?php _e( 'View All Custom Data', 'registrations-for-the-events-calendar' ); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div> <!-- rtec-single-event -->

    </div> <!-- rtec-single-wrapper -->