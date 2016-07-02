<?php
$options = get_option( 'rtec_general' );

if ( ! isset( $options['default_max_registrations'] ) ) {
    _e ( 'Hey! First time using the plugin? You can start configuring on the "General" tab', 'rtec' );
}

$args = array(
    'post_type'   => 'tribe_events',
    'post_status' => 'published',
    'posts_per_page' => 100
);

// create a custom WP_Query object just for events
$the_query = new WP_Query( $args );
?>
<h2>Overview</h2>

<div class="rtec-wrapper rtec-overview">
<?php
if ( $the_query->have_posts() ) :
    while ( $the_query->have_posts() ) : $the_query->the_post();

        $id = get_the_ID();

        require_once RTEC_URL . '/RegistrationsTEC/Database.php';
        
        $db = new RegistrationsTEC\Database();

        $data = array(
            'fields' => 'registration_date, last_name, first_name, email, status',
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
?>
    
    <div class="rtec-single-event">
    
        <div class="rtec-event-meta">
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=<?php echo $id; ?>"><h3><?php the_title(); ?></h3></a>
            <p><?php echo $event_meta['start_date']; ?> to <?php echo $event_meta['end_date']; ?></p>
            <p><?php echo $event_meta['venue_title']; ?></p>
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
                        echo date_i18n( 'm/d g:i a', strtotime( $registration['registration_date'] ) ); ?>
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
        <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=single&id=<?php echo $id; ?>" class="rtec-admin-secondary-button button action"><?php _e( 'More...', 'rtec' ); ?></a>
    
    </div> <!-- rtec-single-event -->

<?php endwhile; endif; // end loop ?>
</div> <!-- rtec-wrapper -->

<?php $db->updateStatuses();