<?php

require_once RTEC_URL . '/RegistrationsTEC/Database.php';

// create a custom WP_Query object just for events

$id = (int)$_GET['id'];

?>
<h2><?php _e( 'Single Event Details', 'rtec' ); ?></h2>
<a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations">Back to Overview</a>

<input type="hidden" value="<?php echo $id; ?>" name="event_id">

    <div class="rtec-wrapper rtec-single">
        <?php
                $db = new RegistrationsTEC\Database();

                $data = array(
                    'fields' => 'registration_date, id, last_name, first_name, email, other',
                    'id' => $id,
                    'order_by' => 'registration_date'
                );

                $registrations = $db->retrieveEntries( $data );

                // set post meta
                $meta = get_post_meta( $id );

                $event_meta['post_id'] = $id;
                $event_meta['title'] = get_the_title( $id );
                $event_meta['start_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventStartDate'][0] ) );
                $event_meta['end_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventEndDate'][0] ) );


                // set venue meta
                $venue_meta = get_post_meta( $meta['_EventVenueID'][0] );
                $event_meta['venue_title'] = $venue_meta["_VenueVenue"][0];

                $options = get_option( 'rtec_general' );
                $other_label = isset( $options['other_label'] ) ? esc_html( $options['other_label'] ) : _( 'Other', 'rtec' );
                ?>

                <div class="rtec-single-event" data-rtec-event-id="<?php echo $id; ?>">

                    <div class="rtec-event-meta">
                        <h3><?php echo get_the_title( $id ); ?></h3>
                        <p><?php echo $event_meta['start_date']; ?> to <?php echo $event_meta['end_date']; ?></p>
                        <p class="rtec-venue-title"><?php echo $event_meta['venue_title']; ?></p>
                    </div>

                    <table class="widefat wp-list-table fixed striped posts rtec-registrations-data">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'rtec' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </th>
                                <th><?php _e( 'Registration Date', 'rtec' ) ?></th>
                                <th><?php _e( 'Last Name', 'rtec' ) ?></th>
                                <th><?php _e( 'First Name', 'rtec' ) ?></th>
                                <th><?php _e( 'Email', 'rtec' ) ?></th>
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
                                    <td class="rtec-reg-date"><?php echo date_i18n( 'F jS, g:i a', strtotime( $registration['registration_date'] ) ); ?></td>
                                    <td class="rtec-reg-last"><?php echo $registration['last_name']; ?></td>
                                    <td class="rtec-reg-first"><?php echo $registration['first_name']; ?></td>
                                    <td class="rtec-reg-email"><?php echo $registration['email']; ?></td>
                                    <td class="rtec-reg-other"><?php echo $registration['other']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col" class="manage-column column-rtec check-column">
                                    <label class="screen-reader-text" for="rtec-select-all-1"><?php _e( 'Select All', 'rtec' ); ?></label>
                                    <input type="checkbox" id="rtec-select-all-1">
                                </th>
                                <th><?php _e( 'Registration Date', 'rtec' ) ?></th>
                                <th><?php _e( 'Last Name', 'rtec' ) ?></th>
                                <th><?php _e( 'First Name', 'rtec' ) ?></th>
                                <th><?php _e( 'Email', 'rtec' ) ?></th>
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
                    <div class="tablenav">
                        <button class="button action rtec-admin-secondary-button rtec-delete-registration">- <?php _e( 'Delete Selected', 'rtec'  ); ?></button>
                        <button class="button action rtec-admin-secondary-button rtec-edit-registration"><?php _e( 'Edit Selected', 'rtec'  ); ?></button>
                        <button class="button action rtec-admin-secondary-button rtec-add-registration">+ <?php _e( 'Add New Registration', 'rtec'  ); ?></button>
                    </div>
                </div> <!-- rtec-single-event -->

    </div> <!-- rtec-single-wrapper -->