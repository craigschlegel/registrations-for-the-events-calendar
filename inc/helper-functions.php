<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

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
	$venue_meta = isset( $meta['_EventVenueID'][0] ) ? get_post_meta( $meta['_EventVenueID'][0] ) : array();

	$event_meta['post_id'] = isset( $post_obj->ID ) ? $post_obj->ID : '';
	$event_meta['title'] = ! empty( $id ) ? get_the_title( $id ) : get_the_title();
	$event_meta['start_date'] = isset( $post_obj->EventStartDate ) ? $post_obj->EventStartDate : '';
	$event_meta['end_date'] = isset( $post_obj->EventEndDate ) ? $post_obj->EventEndDate : '';
	$event_meta['venue_id'] = isset( $meta['_EventVenueID'][0] ) ? $meta['_EventVenueID'][0] : '';
	$event_meta['venue_title'] = isset( $venue_meta['_VenueVenue'][0] ) ? $venue_meta['_VenueVenue'][0] : '(no location)';
	$event_meta['num_registered'] = isset( $meta['_RTECnumRegistered'][0] ) ? $meta['_RTECnumRegistered'][0] : 0;

	return $event_meta;
}

/**
 * Converts raw phone number strings into a properly formatted one
 *
 * @param $raw_number string    telephone number from database with no
 * @since 1.1
 *
 * @return string               telephone number formatted for display
 */
function rtec_format_phone_number( $raw_number ) {
	switch ( strlen( $raw_number ) ) {
		case 11:
			return preg_replace( '/([0-9]{3})([0-9]{4})([0-9]{4})/', '($1) $2-$3', $raw_number );
			break;
		case 7:
			return preg_replace( '/([0-9]{3})([0-9]{4})/', '$1-$2', $raw_number );
			break;
		default:
			return preg_replace( '/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $raw_number );
			break;
	}
}