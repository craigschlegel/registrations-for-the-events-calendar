<?php

// create a custom WP_Query object just for events
$id = (int)$_GET['id'];

?>
<h1><?php _e( 'Single Event Details', 'rtec' ); ?></h1>
<a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations"><?php _e( 'Back to Overview', 'rtec' ); ?></a>

<input type="hidden" value="<?php echo $id; ?>" name="event_id">

    <div class="rtec-wrapper rtec-single">
        <?php
                $db = new RTEC_Db_Admin();
                global $rtec_options;

                $data = array(
                    'fields' => 'registration_date, id, last_name, first_name, email, phone, other',
                    'id' => $id,
                    'order_by' => 'registration_date'
                );

                $registrations = $db->retrieve_entries( $data );

                // set post meta
                $meta = get_post_meta( $id );

                $event_meta['post_id'] = $id;
                $event_meta['title'] = get_the_title( $id );
                $event_meta['start_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventStartDate'][0] ) );
                $event_meta['end_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventEndDate'][0] ) );

                // set venue meta
                $venue_meta = isset( $meta['_EventVenueID'][0] ) ? get_post_meta( $meta['_EventVenueID'][0] ) : array();
                $venue = rtec_get_venue( $id );
                $event_meta['venue_title'] = ! empty( $venue ) ? $venue : '(no location)';
                $options = get_option( 'rtec_general' );
                $first_label = isset( $rtec_options['first_label'] ) ? esc_html( $rtec_options['first_label'] ) : __( 'First', 'rtec' );
                $last_label = isset( $rtec_options['last_label'] ) ? esc_html( $rtec_options['last_label'] ) : __( 'Last', 'rtec' );
                $email_label = isset( $rtec_options['email_label'] ) ? esc_html( $rtec_options['email_label'] ) : __( 'Email', 'rtec' );
                $phone_label = isset( $rtec_options['phone_label'] ) ? esc_html( $rtec_options['phone_label'] ) : __( 'Phone', 'rtec' );
                $other_label = isset( $rtec_options['other_label'] ) ? esc_html( $rtec_options['other_label'] ) : __( 'Other', 'rtec' );
                ?>

                <div class="rtec-single-event" data-rtec-event-id="<?php echo $id; ?>">

                    <div class="rtec-event-meta">
                        <h3><?php echo get_the_title( $id ); ?></h3>
                        <p><?php echo $event_meta['start_date']; ?> to <span class="rtec-end-time"><?php echo $event_meta['end_date']; ?></span></p>
                        <p class="rtec-venue-title"><?php echo $event_meta['venue_title']; ?></p>
                    </div>

                    <table class="widefat wp-list-table fixed striped posts rtec-registrations-data">
                        <thead>
                            <tr>
                                <td scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'rtec' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </td>
                                <th><?php _e( 'Registration Date', 'rtec' ) ?></th>
                                <th><?php echo $last_label; ?></th>
                                <th><?php echo $first_label; ?></th>
                                <th><?php echo $email_label; ?></th>
                                <th><?php echo $phone_label; ?></th>
                                <th><?php echo $other_label; ?></th>
                            </tr>
                        </thead>
                        <?php if ( ! empty( $registrations ) ) : ?>
                        <tbody>
                            <?php foreach( $registrations as $registration ): ?>
                                <tr class="rtec-reg-row" data-rtec-id="<?php echo (int)$registration['id']; ?>">
                                    <td scope="row" class="check-column rtec-checkbox">
                                        <label class="screen-reader-text" for="rtec-select-<?php echo (int)$registration['id']; ?>">Select <?php echo esc_attr( $registration['first_name'] ) . ' ' . esc_attr( $registration['last_name'] ); ?></label>
                                        <input type="checkbox" value="<?php echo (int)$registration['id']; ?>" id="rtec-select-<?php echo (int)$registration['id']; ?>" class="rtec-registration-select check-column">
                                        <div class="locked-indicator"></div>
                                    </td>
                                    <td class="rtec-reg-date" data-rtec-submit="<?php echo $registration['registration_date']; ?>"><?php echo date_i18n( 'F jS, g:i a', strtotime( $registration['registration_date'] )+ $tz_offset ); ?></td>
                                    <td class="rtec-reg-last"><?php echo $registration['last_name']; ?></td>
                                    <td class="rtec-reg-first"><?php echo $registration['first_name']; ?></td>
                                    <td class="rtec-reg-email"><?php echo $registration['email']; ?></td>
                                    <td class="rtec-reg-phone"><?php echo rtec_format_phone_number( $registration['phone'] ); ?></td>
                                    <td class="rtec-reg-other"><?php echo $registration['other']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'rtec' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </td>
                                <th><?php _e( 'Registration Date', 'rtec' ) ?></th>
                                <th><?php echo $last_label; ?></th>
                                <th><?php echo $first_label; ?></th>
                                <th><?php echo $email_label; ?></th>
                                <th><?php echo $phone_label; ?></th>
                                <th><?php echo $other_label; ?></th>
                            </tr>
                        </tfoot>
                        <?php else: ?>
                        <tbody>
                            <tr>
                                <td colspan="6" align="center"><?php _e( 'No Registrations Yet', 'rtec' ); ?></td>
                            </tr>
                        </tbody>
                        <?php endif; // registrations not empty?>
                    </table>
                    <div class="rtec-event-actions rtec-clear">
                        <div class="tablenav">
                            <button class="button action rtec-admin-secondary-button rtec-delete-registration">- <?php _e( 'Delete Selected', 'rtec'  ); ?></button>
                            <button class="button action rtec-admin-secondary-button rtec-edit-registration"><?php _e( 'Edit Selected', 'rtec'  ); ?></button>
                            <button class="button action rtec-admin-secondary-button rtec-add-registration">+ <?php _e( 'Add New Registration', 'rtec'  ); ?></button>

                            <form method="post" id="rtec_csv_export_form" action="">
                                <?php wp_nonce_field( 'rtec_csv_export', 'rtec_csv_export_nonce' ); ?>
                                <input type="hidden" name="rtec_id" value="<?php echo $id; ?>" />
                                <input type="submit" name="rtec_event_csv" class="button action rtec-admin-secondary-button" value="<?php _e( 'Export Registrations (.csv)', 'rtec' ); ?>" />
                            </form>
                        </div>
                    </div>
                </div> <!-- rtec-single-event -->

    </div> <!-- rtec-single-wrapper -->