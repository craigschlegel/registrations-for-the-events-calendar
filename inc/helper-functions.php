<?php

/**
 * Will return all relevant meta for an event
 *
 * @param string $id
 * @since 1.0
 * @return array
 */
function rtec_get_event_meta( $id = '' ) {
	$event_meta = array();

	// construct post object
	if ( ! empty( $id ) ) {
		$post_obj = get_post( $id );
	} else {
		$post_obj = get_post();
	}

	// set post meta
	$meta = get_post_meta( $post_obj->ID );

	// set venue meta
	$venue_meta = get_post_meta( $meta['_EventVenueID'][0] );

	$event_meta['post_id'] = $post_obj->ID;
	$event_meta['title'] = ! empty( $id ) ? get_the_title( $id ) : get_the_title() ;
	$event_meta['start_date'] = $post_obj->EventStartDate;
	$event_meta['end_date'] = $post_obj->EventEndDate;
	$event_meta['venue_id'] = $meta['_EventVenueID'][0];
	$event_meta['venue_title'] = $venue_meta["_VenueVenue"][0];
	$event_meta['num_registered'] = $meta['_RTECnumRegistered'][0];

	return $event_meta;
}