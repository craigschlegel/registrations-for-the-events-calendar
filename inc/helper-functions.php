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
	$venue = rtec_get_venue( $post_obj->ID );
	$event_meta['venue_title'] = ! empty( $venue ) ? $venue : '(no location)';
	$event_meta['venue_address'] = isset( $venue_meta['_VenueAddress'][0] ) ? $venue_meta['_VenueAddress'][0] : '';
	$event_meta['venue_city'] = isset( $venue_meta['_VenueCity'][0] ) ? $venue_meta['_VenueCity'][0] : '';
	$event_meta['venue_state'] = isset( $venue_meta['_VenueStateProvince'][0] ) ? $venue_meta['_VenueStateProvince'][0] : '';
	$event_meta['venue_zip'] = isset( $venue_meta['_VenueZip'][0] ) ? $venue_meta['_VenueZip'][0] : '';

	$event_meta['num_registered'] = isset( $meta['_RTECnumRegistered'][0] ) ? $meta['_RTECnumRegistered'][0] : 0;
	$event_meta['registrations_disabled'] = isset( $meta['_RTECregistrationsDisabled'][0] ) ? $meta['_RTECregistrationsDisabled'][0] : 0;

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

/**
 * Retrieves venue title using TEC function. Checks to make sure it exists first
 *
 * @param mixed $event_id   id of the event
 * @since 1.1
 *
 * @return string           venue title
 */
function rtec_get_venue( $event_id = NULL ) {
	if ( function_exists( 'tribe_get_venue' ) ) {
		$venue = tribe_get_venue( $event_id );

		return $venue;
	} else {
		return '';
	}
}

/**
 * Takes the custom data array and converts to serialized data for
 * adding to the db
 *
 * @param $submission_data
 * @param bool $from_form
 *
 * @return mixed
 */
function rtec_serialize_custom_data( $submission_data, $from_form = true ) {
	$options = get_option( 'rtec_options', array() );

	if ( isset( $options['custom_field_names'] ) ) {
		$custom_field_names = explode( ',', $options['custom_field_names'] );
	} else {
		$custom_field_names = array();
	}

	$custom_data = array();
	if ( $from_form ) {
		foreach ( $custom_field_names as $field ) {

			if ( isset( $submission_data['rtec_' . $field] ) ) {
				$custom_data[$options[$field . '_label']] = $submission_data['rtec_' . $field];
			}

		}
	} else {
		$custom_data = $submission_data['rtec_custom'];
	}

	return maybe_serialize( $custom_data );
}