<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

//If the user is preserving the settings then don't delete them
$options = get_option( 'rtec_options', array() );

if ( ! $options['preserve_db'] ) {
	// clean up options from the database
	delete_option( 'rtec_options' );
	delete_option( 'rtec_db_version' );
	delete_transient('rtec_new_registrations');

	global $wpdb;

	// delete the registrations table
	$wpdb->query( "DROP TABLE IF EXISTS " . esc_sql( $wpdb->prefix ). "rtec_registrations" );

	$args = array(
		'post_type'   => 'tribe_events'
	);

	// loop through events post types and delete meta data added by this plugin
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) :
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$event_id = get_the_ID();

			delete_post_meta( $event_id, '_RTECnumRegistered' );

		endwhile;
	endif;
	// reset WP_Query
	wp_reset_postdata();
}


