<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Will return all relevant meta for an event
 *
 * @param   $id string
 * @since   1.0
 * @return array
 */
function rtec_get_event_meta( $id = '' ) {
	global $rtec_options;

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
	$event_meta['start_date'] = isset( $meta['_EventStartDate'][0] ) ? $meta['_EventStartDate'][0] : '';
	$event_meta['end_date'] = isset( $meta['_EventEndDate'][0] ) ? $meta['_EventEndDate'][0] : '';
	$event_meta['start_date_utc'] = isset( $meta['_EventStartDateUTC'][0] ) ? $meta['_EventStartDateUTC'][0] : '';
	$event_meta['end_date_utc'] = isset( $meta['_EventEndDateUTC'][0] ) ? $meta['_EventEndDateUTC'][0] : '';
	$event_meta['venue_id'] = isset( $meta['_EventVenueID'][0] ) ? $meta['_EventVenueID'][0] : '';
	$venue = rtec_get_venue( $post_obj->ID );
	$event_meta['venue_title'] = ! empty( $venue ) ? $venue : '(no location)';
	$event_meta['venue_address'] = isset( $venue_meta['_VenueAddress'][0] ) ? $venue_meta['_VenueAddress'][0] : '';
	$event_meta['venue_city'] = isset( $venue_meta['_VenueCity'][0] ) ? $venue_meta['_VenueCity'][0] : '';
	$event_meta['venue_state'] = isset( $venue_meta['_VenueStateProvince'][0] ) ? $venue_meta['_VenueStateProvince'][0] : '';
	$event_meta['venue_zip'] = isset( $venue_meta['_VenueZip'][0] ) ? $venue_meta['_VenueZip'][0] : '';

	$default_disabled = isset( $rtec_options['disable_by_default'] ) ? $rtec_options['disable_by_default'] : 0;
	$event_meta['registrations_disabled'] = isset( $meta['_RTECregistrationsDisabled'][0] ) ? ( (int)$meta['_RTECregistrationsDisabled'][0] === 1 ) : $default_disabled;
	$default_limit_registrations = isset( $rtec_options['limit_registrations'] ) ? $rtec_options['limit_registrations'] : false;
	$event_meta['limit_registrations'] = isset( $meta['_RTEClimitRegistrations'][0] ) ? ( (int)$meta['_RTEClimitRegistrations'][0] === 1 ) : $default_limit_registrations;
	$default_max_registrations = isset( $rtec_options['default_max_registrations'] ) ? (int)$rtec_options['default_max_registrations'] : 30;
	$event_meta['max_registrations'] = isset( $meta['_RTECmaxRegistrations'][0] ) ? (int)$meta['_RTECmaxRegistrations'][0] : $default_max_registrations;
	$default_deadline_type = isset( $rtec_options['default_deadline_type'] ) ? $rtec_options['default_deadline_type'] : 'start';
	$event_meta['deadline_type'] = isset( $meta['_RTECdeadlineType'][0] ) ? $meta['_RTECdeadlineType'][0] : $default_deadline_type;

	$event_meta['num_registered'] = isset( $meta['_RTECnumRegistered'][0] ) ? (int)$meta['_RTECnumRegistered'][0] : 0;

	$event_meta['registration_deadline'] = rtec_get_event_deadline_utc( $event_meta );

	return $event_meta;
}

/**
 * Calculates a deadline relative to timezone
 *
 * @param   $event_meta array
 * @since   1.5
 * @return  mixed   int if deadline, 'none' if no deadline
 */
function rtec_get_event_deadline_utc( $event_meta ) {
	global $rtec_options;

	$deadline_time = 'none';

	$WP_offset = get_option( 'gmt_offset' );

	if ( ! empty( $WP_offset ) ) {
		$tz_offset = $WP_offset * HOUR_IN_SECONDS;
	} else {
		$options = get_option( 'rtec_options' );

		$timezone = isset( $options['timezone'] ) ? $options['timezone'] : 'America/New_York';
		// use php DateTimeZone class to handle the date formatting and offsets
		$date_obj = new DateTime( date( 'm/d g:i a' ), new DateTimeZone( "UTC" ) );
		$date_obj->setTimeZone( new DateTimeZone( $timezone ) );
		$tz_offset = $date_obj->getOffset();
	}

	if ( $event_meta['deadline_type'] === 'start' ) {

		if ( $event_meta['start_date'] !== '' ) {
			$deadline_multiplier = isset( $rtec_options['registration_deadline'] ) ? sanitize_text_field( $rtec_options['registration_deadline'] ) : 0;
			$deadline_unit = isset( $rtec_options['registration_deadline_unit'] ) ? sanitize_text_field( $rtec_options['registration_deadline_unit'] ) : 0;
			$offset_start_time = strtotime( $event_meta['start_date'] ) - $tz_offset;
			$deadline_time = $offset_start_time - ($deadline_multiplier * $deadline_unit);
		}

	}

	if ( $event_meta['deadline_type'] === 'end' ) {
		$deadline_time = strtotime( $event_meta['end_date'] ) - $tz_offset;
	}

	return $deadline_time;
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
 * @param   $event_id   mixed  id of the event
 * @since   1.1
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
 * @param   $submission_data
 * @param   $from_form          bool
 * @since   1.3
 *
 * @return  mixed
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

/**
 * Returns the appropriate translation/custom/default text
 *
 * @param   $custom         string  the custom translation of text
 * @param   $translation    string  the translation or default of text
 * @since   1.4
 *
 * @return  string                  the appropriate text
 */
function rtec_get_text( $custom, $translation ) {
	global $rtec_options;
	$text = $translation;

	if ( isset( $rtec_options['message_source'] ) && $rtec_options['message_source'] === 'custom' ) {
		$text = isset( $custom ) ? $custom : $translation;
	}

	return $text;

}

/**
 * Generates the registration form with a shortcode
 *
 * @param   $atts        array  settings for the form
 * @since   1.5
 */
add_shortcode( 'rtec-registration-form', 'rtec_the_registration_form_shortcode' );
function rtec_the_registration_form_shortcode( $atts ) {
	$post_id = isset( $atts['event'] ) ? (int)$atts['event'] : false;
	$atts['doing_shortcode'] = true;

	if ( $post_id !== get_the_ID() ) {

		if ( function_exists( 'rtec_the_registration_form' ) && $post_id !== false ) {
			$html = rtec_the_registration_form( $atts );

			return $html;
		} else {

			if ( current_user_can( 'edit_posts' ) ) {
				echo '<div class="rtec-yellow-message">';
				echo '<span>This message is only visible to logged-in editors:<br /><strong>Please enter a valid event ID in the shortcode to show a registration form here.</strong></span>';
				echo '<span>For example: </span><code>[rtec-registration-form event=321]</code>';
				echo '</div>';
			}

		}

	} else {

		if ( current_user_can( 'edit_posts' ) ) {
			echo '<div class="rtec-yellow-message">';
			echo '<span>This message is only visible to logged-in editors:<br /><strong>Shortcode not used. There is already a registration form on this page for this event.</strong></span>';
			echo '</div>';
		}

	}


}