<h2><?php _e( 'Overview', 'rtec' ); ?></h2>
<?php if ( ! isset( $options['default_max_registrations'] ) ) : ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <?php esc_attr_e( 'Hey! First time using the plugin? You can start configuring on the' , 'rtec' ); ?>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=form">"Form" tab</a><br />
            <?php esc_attr_e( 'Or check out our setup directions' , 'rtec' ); ?>
            <a href="http://roundupwp.com/products/registrations-for-the-events-calendar/setup/" target="_blank">on our website</a>
        </p>
    </div>
<?php endif; ?>

<?php if ( empty( $tz_offset )) : ?>
<form method="post" action="options.php">
    <?php settings_fields( 'rtec_options' ); ?>
    <?php do_settings_sections( 'rtec_timezone' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" /><br />
    <hr>
</form>
<?php endif; ?>

<div class="rtec-wrapper rtec-overview">
<?php
$db = new RTEC_Db_Admin();

$events = tribe_get_events( array(
    'posts_per_page' => 100,
    'start_date' => date( '2000-1-1 0:0:0' )
) );

foreach ( $events as $event ) :

        $data = array(
            'fields' => 'registration_date, last_name, first_name, email, status',
            'id' => $event->ID,
            'order_by' => 'registration_date'
        );

        $registrations = $db->retrieve_entries( $data );

        // set post meta
        $meta = get_post_meta( $event->ID );

        $event_meta['post_id'] = $event->ID;
        $event_meta['title'] = $event->post_title;
        $event_meta['start_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventStartDate'][0] ) );
        $event_meta['end_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventEndDate'][0] ) );
		$event_meta['disabled'] = isset( $meta['_RTECregistrationsDisabled'][0] ) ? $meta['_RTECregistrationsDisabled'][0] : 0;

        // set venue meta
        $venue_meta = isset( $meta['_EventVenueID'][0] ) ? get_post_meta( $meta['_EventVenueID'][0] ) : array();
        $event_meta['venue_title'] = isset( $venue_meta["_VenueVenue"][0] ) ? $venue_meta["_VenueVenue"][0] : '(no location)';
?>
    
    <div class="rtec-single-event">
    
        <div class="rtec-event-meta">
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=<?php echo $event->ID; ?>"><h3><?php echo $event_meta['title']; ?></h3></a>
            <p><?php echo $event_meta['start_date']; ?> to <?php echo $event_meta['end_date']; ?></p>
            <p><?php echo $event_meta['venue_title']; ?></p>
        </div>
	    <div class="rtec-event-options postbox closed">
		    <button type="button" class="handlediv button-link" aria-expanded="false"><span class="screen-reader-text">Toggle panel: Information</span><span class="toggle-indicator" aria-hidden="true"></span></button>
		    <span class="hndle"><span>Event Options</span></span>
	    </div>
	    <div class="rtec-event-options rtec-hidden-options postbox">
		    <form class="rtec-event-options-form" action="">
			    <input type="hidden" name="rtec_event_id" value="<?php echo $event_meta['post_id']; ?>" />
			    <input type="hidden" name="rtec_checkboxes" value="_RTECregistrationsDisabled" />
			    <input type="checkbox" id="rtec-disable-<?php echo $event_meta['post_id']; ?>" name="_RTECregistrationsDisabled" <?php if( $event_meta['disabled'] == '1' ) { echo 'checked'; } ?> value="1"/>
			    <label for="rtec-disable-<?php echo $event_meta['post_id']; ?>"><?php _e( 'Disable registrations for this event', 'rtec' ); ?></label>
			    <div class="clear"></div>
			    <button class="button action rtec-admin-secondary-button rtec-update-event-options"><?php _e( 'Update', 'rtec'  ); ?></button>
			    <div class="clear"></div>
		    </form>
	    </div>
        <table class="widefat rtec-registrations-data">
            <thead>
                <tr>
                    <th><?php _e( 'Registration Date', 'rtec' ) ?></th>
                    <th><?php _e( 'Last Name', 'rtec' ) ?></th>
                    <th><?php _e( 'First Name', 'rtec' ) ?></th>
                    <th><?php _e( 'Email', 'rtec' ) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $registrations ) ) : ?>
    
            <?php foreach( $registrations as $registration ): ?>
                <tr>
                    <td class="rtec-first-data">
                        <?php if ( $registration['status'] == 'n' ) {
                            echo '<span class="rtec-notice-new">' . _( 'new' ) . '</span>';
                        }
                        echo date_i18n( 'm/d g:i a', strtotime( $registration['registration_date'] ) + $tz_offset ); ?>
                    </td>
                    <td><?php echo $registration['last_name']; ?></td>
                    <td><?php echo $registration['first_name']; ?></td>
                    <td><?php echo $registration['email']; ?></td>
                </tr>
            <?php endforeach; ?>
    
            <?php else: ?>
    
                <tr>
                    <td colspan="4" align="center"><?php _e( 'No Registrations Yet', 'rtec' ); ?></td>
                </tr>
    
            <?php endif; // registrations not empty?>
    
            </tbody>
        </table>
	    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=<?php echo $event->ID; ?>" class="rtec-admin-secondary-button button action"><?php _e( 'Detailed View', 'rtec' ); ?></a>
    </div> <!-- rtec-single-event -->

<?php endforeach; // end loop ?>
</div> <!-- rtec-wrapper -->

<?php $db->update_statuses();