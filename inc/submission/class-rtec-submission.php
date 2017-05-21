<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class RTEC_Submission
{
	/**
	 * @var RTEC_Submission
	 * @since 1.0
	 */
    private static $instance;

	/**
	 * @var array
	 * @since 1.0
	 */
    public $submission = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    public $errors = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    protected $required_fields = array();

	/**
	 * @var array
	 * @since 1.3
	 */
	protected $custom_required_fields = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    public $validate_check = array();

	/**
	 * @var array
	 * @since 1.0
	 */
	private $event_meta = array();

	/**
	 * Validates the initial data
	 *
	 * @param $post $_POST data
	 * @since 1.0
	 */
    public function validate_input( $post )
    {
	    $this->submission = $post;

	    $this->validate_data();
    }

	/**
	 * Compares the allowed number of registrations with the current number
	 *
	 * @param int $num_registered
	 *
	 * @since 1.2
	 * @return bool
	 */
    public function attendance_limit_not_reached( $num_registered = 0 )
    {
	    $options = get_option( 'rtec_options' );

	    if ( $this->event_meta['limit_registrations'] ) {
	    	$registrations_left = $this->event_meta['max_registrations'] - (int)$num_registered;

		    if ( $registrations_left > 0 ) {
		    	return true;
		    } else {
		    	return false;
		    }

	    } else {
	    	return true;
	    }
    }

	/**
	 * Compares existing emails registered for this event with the submitted one
	 *
	 * @param string $email
	 *
	 * @since 1.6
	 * @return bool
	 */
	public function registrant_check_for_duplicate_email( $email ) {
		require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-db.php';

		$email = is_email( $email ) ? $email : false;
		$event_id = (int)$this->submission['rtec_event_id'];

		$is_duplicate = 'not';

		if ( false !== $email ) {
			$db = New RTEC_Db();
			$is_duplicate = $db->check_for_duplicate_email( $email, $event_id );
		}

		return $is_duplicate;
	}
    
    /**
     * Get the one true instance of EDD_Register_Meta.
     *
     * @since  1.0
     * @return object $instance
     */
    static public function instance() {
        if ( !self::$instance ) {
            self::$instance = new RTEC_Submission( $_POST );
        }
        
        return self::$instance;
    }

	/**
	 * Custom fields are set slightly differently so this method exists
	 *
	 * @since  1.3
	 */
    private function set_custom_fields_required()
    {
		global $rtec_options;

	    if ( isset( $rtec_options['custom_field_names'] ) ) {

	    	if ( ! is_array( $rtec_options['custom_field_names'] ) ) {
			    $custom_field_names = explode( ',', $rtec_options['custom_field_names'] );
		    } else {
			    $custom_field_names = $rtec_options['custom_field_names'];
		    }

	    } else {
		    $custom_field_names = array();
	    }

	    $required = array();
	    foreach ( $custom_field_names as $field ) {

		    if ( isset( $rtec_options[$field . '_require'] ) && $rtec_options[$field . '_require']  ) {
			    $required[] = 'rtec_' . $field;
		    }

	    }

	    $this->custom_required_fields = $required;
    }

	/**
	 * Validates the data submitted by the user
	 *
	 * @since 1.3
	 */
    public function validate_data() {
        // get form options from the db
        $options = get_option( 'rtec_options' );
        $submission = $this->submission;
	    $this->set_custom_fields_required();

        // for each submitted form field
        foreach ( $submission as $input_key => $input_value ) {
        	// check spam honeypot, error if not empty
        	if ( $input_key === 'rtec_user_address' && ! empty( $input_value ) ) {
        		$this->errors[] = 'user_address';
	        }

	        if ( $input_key === 'rtec_email' && isset( $options['check_for_duplicates'] ) && $options['check_for_duplicates'] === true ) {

		        if ( $this->registrant_check_for_duplicate_email( $input_value ) ) {
			        $this->errors[] = 'email_duplicate';
		        }

	        }
            // if the form field is a required first, last, email, or other
            if ( $input_key === 'rtec_first' && $options['first_require'] ) {

                if ( ( strlen( $input_value ) > 1000 ) ||
                   ( strlen( $input_value ) < 1 ) ) {

                    $this->errors[] = 'first';
                }
                
            } elseif ( $input_key === 'rtec_last' && $options['last_require'] ) {

                if ( ( strlen( $input_value ) > 1000 ) ||
                   ( strlen( $input_value ) < 1 ) ) {
                    $this->errors[] = 'last';
                }
                
            } elseif ( $input_key === 'rtec_email' && $options['email_require'] ) {
            	
                if ( ! is_email( $input_value ) ) {
                    $this->errors[] = 'email';
                }
                
            } elseif ( $input_key === 'rtec_phone' && $options['phone_require'] ) {
	            $stripped_input = preg_replace( '/[^0-9]/', '', $input_value );

	            if ( isset( $options['phone_valid_count'] ) && $options['phone_valid_count'] === '' ) {
		            $valid_length_count = strlen( $stripped_input );
	            } else {
		            $valid_counts_arr = isset( $options['phone_valid_count'] ) ? explode( ',' , $options['phone_valid_count'] ) : array( 7, 10 );
		            $valid_length_count = 0;

		            foreach ( $valid_counts_arr as $valid_count ) {

			            if ( strlen( $stripped_input ) === (int)$valid_count ) {
				            $valid_length_count++;
			            }

		            }

	            }

	            if ( $valid_length_count < 1 ) {
		            $this->errors[] = 'phone';
	            }

            } elseif ( $input_key === 'rtec_other' && $options['other_require'] ) {
            	
                if ( empty( $input_value ) ) {
                    $this->errors[] = 'other';
                }
                
            } elseif ( in_array( $input_key, $this->custom_required_fields, true ) ) {

	            if ( empty( $input_value ) ) {
		            $this->errors[] = str_replace( 'rtec_', '', $input_key );
	            }

            }
        }

	    if ( isset( $options['recaptcha_require'] ) && $options['recaptcha_require'] ) {

	    	if ( ! isset( $submission['rtec_recaptcha_sum'] ) || ! isset( $submission['rtec_recaptcha_input'] ) ) {
			    $this->errors[] = 'recaptcha';
		    } elseif ( $submission['rtec_recaptcha_sum'] !== $submission['rtec_recaptcha_input'] ) {
			    $this->errors[] = 'recaptcha';
		    }

	    }

	    $this->event_meta = rtec_get_event_meta( (int)$this->submission['rtec_event_id'] );
    }

	/**
	 * Check if there are validation errors from the submitted data
	 * 
	 * @since 1.0
	 * @return bool
	 */
    public function has_errors()
    {
        return ! empty( $this->errors );
    }

	/**
	 * The fields that have errors
	 * 
	 * @since 1.0
	 * @return array
	 */
    public function get_errors() 
    {
        return $this->errors;
    }

	/**
	 * data from the submission
	 * 
	 * @since 1.0
	 * @return array
	 */
    public function get_data()
    {
        return $this->submission;
    }

	/**
	 * Removes anything that might cause problems
	 * 
	 * @since 1.0
	 */
    public function sanitize_submission() 
    {
        $submission = $this->submission;
        // for each submitted form field
        foreach ( $submission as $input_key => $input_value ) {
        	if ( $input_key === 'ical_url' ) {
        		// the ical has url so escaped
		        $new_val = esc_url( $input_value );
	        } else {
		        // sanitize the input value
		        $new_val = sanitize_text_field( $input_value );
	        }

            // strip potentially malicious header strings
            $new_val = $this->strip_malicious( $new_val );
	        // replace single quotes
	        $new_val = str_replace( "'", '`', $new_val );
            // assign the sanitized value
            $this->submission[$input_key] = $new_val;
        }

    }

	/**
	 * Meant to be called only after submission has been validated
	 *
	 * @since 1.0
	 */
    public function process_valid_submission() {
    	global $rtec_options;

	    $disable_confirmation = isset( $rtec_options['disable_confirmation'] ) ? $rtec_options['disable_confirmation'] : false;
	    $disable_notification = isset( $rtec_options['disable_notification'] ) ? $rtec_options['disable_notification'] : false;

	    $this->sanitize_submission();

	    $confirmation_success = true;
	    if ( $this->email_given() && ! $disable_confirmation ) {
		    $confirmation_success = $this->send_confirmation_email();
	    }
	    
	    if ( ! $disable_notification ) {
		    $notification_success = $this->send_notification_email();
	    }
	    
	    $data = $this->get_db_data();

	    require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-db.php';
	    $db = new RTEC_Db();

	    $db->insert_entry( $data );

	    if ( ! empty( $data['rtec_event_id'] ) ) {
		    $change = 1;
		    $db->update_num_registered_meta( $data['rtec_event_id'], $data['rtec_num_registered'], $change );
	    }

	    if ( $this->email_given() && ! $disable_confirmation && ! $confirmation_success ) {
		    return 'email';
	    }

	    return 'success';

    }

	/**
	 * Removes anything that could potentially be malicious
	 * 
	 * @param $value
	 * @since 1.0
	 * @return string
	 */
    private function strip_malicious( $value )
    {
        $malicious = array( 'to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:' );
        
	    foreach ( $malicious as $m ) {

            if( stripos( $value, $m ) !== false ) {
                return 'untrusted';
            }

        }
        $value = str_replace( array( '\r', '\n', '%0a', '%0d'), ' ' , $value);
	    
        return trim( $value );
    }

	/**
	 * Did the user supply an email?
	 * 
	 * @since 1.0
	 * @return bool
	 */
    public function email_given()
    {
        if ( ! empty( $this->submission['rtec_email'] ) ) {
            return true;
        }

        return false;
    }

	/**
	 * Used by some features to add dynamic fields to emails, etc..
	 *
	 * @param $text string  text from email that needs to replace dynamic fields
	 *
	 * @since 1.3
	 * @return string   text with dynamic fields inserted
	 */
    private function find_and_replace( $text )
    {
	    global $rtec_options;

	    $working_text = $text;
	    $date_format = isset( $rtec_options['custom_date_format'] ) ? $rtec_options['custom_date_format'] : 'F j, Y';
	    $date_str = date_i18n( $date_format, strtotime( $this->submission['rtec_date'] ) );
	    $first = isset( $this->submission['rtec_first'] ) ? $this->submission['rtec_first'] : '';
	    $last = isset( $this->submission['rtec_last'] ) ? $this->submission['rtec_last'] : '';
	    $email = isset( $this->submission['rtec_email'] ) ? $this->submission['rtec_email'] : '';
	    $phone = isset( $this->submission['rtec_phone'] ) ? rtec_format_phone_number( $this->submission['rtec_phone'] ) : '';
	    $other = isset( $this->submission['rtec_other'] ) ? $this->submission['rtec_other'] : '';

	    $search_replace = array(
	    	'{venue}' => $this->submission['rtec_venue_title'],
		    '{venue-address}' => $this->submission['rtec_venue_address'],
		    '{venue-city}' => $this->submission['rtec_venue_city'],
		    '{venue-state}' => $this->submission['rtec_venue_state'],
		    '{venue-zip}' => $this->submission['rtec_venue_zip'],
		    '{event-title}' => $this->submission['rtec_title'],
		    '{event-date}' => $date_str,
		    '{first}' => $first,
		    '{last}' => $last,
		    '{email}' => $email,
		    '{phone}' => $phone,
		    '{other}' => $other,
		    '{ical-url}' => $this->submission['ical_url'],
		    '{nl}' =>"\n"
	    );

	    // add custom
	    if ( isset( $rtec_options['custom_field_names'] ) ) {

	    	if ( is_array( $rtec_options['custom_field_names'] ) ) {
			    $custom_field_names = $rtec_options['custom_field_names'];
		    } else {
			    $custom_field_names = explode( ',', $rtec_options['custom_field_names'] );
		    }

	    } else {
		    $custom_field_names = array();
	    }

	    foreach ( $custom_field_names as $field ) {
		    if ( isset( $rtec_options[ $field . '_label' ] ) && ! empty( $rtec_options[ $field . '_label' ] ) ) {
			    $search_replace[ '{' . $rtec_options[ $field . '_label' ] . '}' ] = isset( $this->submission[ 'rtec_' . $field ] ) ? $this->submission[ 'rtec_' . $field ] : '';
		    }
	    }

	    foreach ( $search_replace as $search => $replace ) {
		    $working_text = str_replace( $search, $replace, $working_text );
	    }

	    return $working_text;
    }

	/**
	 * Email message sent to user
	 * 
	 * @since 1.0
	 * @since 1.1   updated some of the fields that can be dynamically set from user
	 * @since 1.2   allow custom date formats in message
	 * @return mixed|string
	 */
    private function get_conf_message()
    {
	    global $rtec_options;

	    $date_format = isset( $rtec_options['custom_date_format'] ) ? $rtec_options['custom_date_format'] : 'F j, Y';
	    $date_str = date_i18n( $date_format, strtotime( $this->submission['rtec_date'] ) );

        if ( isset( $rtec_options['confirmation_message'] ) && $rtec_options['message_source'] !== 'translate' ) {
            $body = $this->find_and_replace( $rtec_options['confirmation_message'] );
        } else {
            $body = __( 'You are registered!', 'registrations-for-the-events-calendar' ) . "\n\n";
            $body .= sprintf( __( 'Event', 'registrations-for-the-events-calendar' ) .': %1$s at %2$s on %3$s'. "\n",
                esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );
            $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) : '';
            $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';
            $body .= sprintf ( __( 'Name', 'registrations-for-the-events-calendar' ) .': %1$s %2$s', $first, $last ) . "\n";

	        if ( ! empty( $this->submission['rtec_phone'] ) ) {
		        $phone = esc_html( $this->submission['rtec_phone'] );
		        $body .= sprintf (  __( 'Phone', 'registrations-for-the-events-calendar' ) .': %1$s', $phone ) . "\n";
	        }
	        
            if ( ! empty( $this->submission['rtec_other'] ) ) {
                $other = esc_html( $this->submission['rtec_other'] );
                $body .= sprintf ( __( 'Other', 'registrations-for-the-events-calendar' ) .': %1$s', $other ) . "\n\n";
            }

	        if ( ! empty( $this->submission['rtec_venue_address'] ) ) {
		        $body .= __( 'The event will be held at this location', 'registrations-for-the-events-calendar' ) . ':' . "\n\n";
		        $body .= sprintf( '%1$s'. "\n", esc_html( $this->submission['rtec_venue_address'] ) );
		        $body .= sprintf( '%1$s, %2$s %3$s', esc_html( $this->submission['rtec_venue_city'] ), esc_html( $this->submission['rtec_venue_state'] ), esc_html( $this->submission['rtec_venue_zip'] ) );
	        }

	        $body .=  "\n\n" . __( 'Thank You!', 'registrations-for-the-events-calendar' );
        }

        return $body;
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    private function get_conf_header()
    {
	    global $rtec_options;

	    $confirmation_from_address = rtec_get_confirmation_from_address( $this->event_meta['post_id'] );
	    $confirmation_from_address = is_email( $confirmation_from_address ) ? $confirmation_from_address : get_option( 'admin_email' );

	    if ( ! empty ( $rtec_options['confirmation_from'] ) && ! empty ( $rtec_options['confirmation_from_address'] ) ) {
            $email_from = $this->strip_malicious( $rtec_options['confirmation_from'] ) . ' <' . $confirmation_from_address . '>';
            $headers = 'From: ' . $email_from;
        } else {
            $headers = '';
        }

        return $headers;
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    private function get_conf_recipient()
    {
        return $this->submission['rtec_email'];
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    private function get_conf_subject()
    {
        global $rtec_options;

        if ( ! empty ( $rtec_options['confirmation_subject'] ) && $rtec_options['message_source'] !== 'translate' ) {
        	$subject = $this->strip_malicious( $this->find_and_replace( $rtec_options['confirmation_subject'] ) );
        } else {
        	$subject = $this->find_and_replace( '{event-title}' );
        }

	    return $subject;
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    public function send_confirmation_email() {
        $confirmation_header = $this->get_conf_header();
        $confirmation_message = $this->get_conf_message();
        $confirmation_recipient = $this->get_conf_recipient();
        $confirmation_subject = $this->get_conf_subject();

	    return wp_mail( $confirmation_recipient, $confirmation_subject, $confirmation_message, $confirmation_header );
    }

	/**
	 * @since 1.0
	 * @since 1.2   now accepts custom notification messages and custom date formats
	 * @return string
	 */
    public function get_not_message()
    {
	    global $rtec_options;

	    $body = '';
	    $date_format = isset( $rtec_options['custom_date_format'] ) ? $rtec_options['custom_date_format'] : 'F j, Y';
	    $date_str = date_i18n( $date_format, strtotime( $this->submission['rtec_date'] ) );
	    $use_custom_notification = isset( $rtec_options['use_custom_notification'] ) ? $rtec_options['use_custom_notification'] : false;

	    if ( $use_custom_notification && $rtec_options['message_source'] !== 'translate' ) {
		    $body = $this->find_and_replace( $rtec_options['notification_message'] );
	    } else {
		    $first_label = rtec_get_text( $rtec_options['first_label'], __( 'First', 'registrations-for-the-events-calendar' ) );
		    $last_label = rtec_get_text( $rtec_options['last_label'], __( 'Last', 'registrations-for-the-events-calendar' ) );
		    $email_label = rtec_get_text( $rtec_options['email_label'], __( 'Email', 'registrations-for-the-events-calendar' ) );

		    $body .= sprintf( 'The following submission was made for: %1$s at %2$s on %3$s'. "\n",
			    esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );
		    $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
		    $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';

		    if ( ! empty( $this->submission['rtec_first'] ) ) {
			    $body .= sprintf( '%s: %s', esc_html( $first_label ), esc_html( $first ) ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_last'] ) ) {
			    $body .= sprintf( '%s: %s', esc_html( $last_label ), esc_html( $last ) ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_email'] ) ) {
			    $email = esc_html( $this->submission['rtec_email'] );
			    $body .= sprintf( '%s: %s', esc_html( $email_label ), esc_html( $email ) ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_phone'] ) ) {
			    $phone_label = rtec_get_text( $rtec_options['phone_label'], __( 'Phone', 'registrations-for-the-events-calendar' ) );
			    $phone = rtec_format_phone_number( esc_html( $this->submission['rtec_phone'] ) );
			    $body .= sprintf( '%s: %s', esc_html( $phone_label ), esc_html( $phone ) ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_other'] ) ) {
			    $other_label = rtec_get_text( $rtec_options['other_label'], __( 'Other', 'registrations-for-the-events-calendar' ) );
			    $other = esc_html( $this->submission['rtec_other'] );
			    $body .= sprintf( '%s: %s', esc_html( $other_label ), esc_html( $other ) ) . "\n";
		    }

		    if ( isset( $rtec_options['custom_field_names'] ) ) {

		    	if ( ! is_array( $rtec_options['custom_field_names'] ) ) {
				    $rtec_options['custom_field_names'] = explode( ',', $rtec_options['custom_field_names'] );
			    }

			    foreach ( $rtec_options['custom_field_names'] as $field ) {

				    if ( ! empty( $this->submission[ 'rtec_' . $field ] ) ) {
					    $custom = esc_html( $this->submission[ 'rtec_' . $field ] );
					    $body .= sprintf( '%s: %s', esc_html( $rtec_options[ $field . '_label' ] ), esc_html( $custom ) ) . "\n";
				    }

			    }
		    }
	    }

        return $body;
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    public function get_not_header()
    {
		global $rtec_options;

        if ( ! empty ( $rtec_options['notification_from'] ) && ! empty ( $rtec_options['confirmation_from_address'] ) ) {
            $notification_from_address = is_email( $rtec_options['confirmation_from_address'] ) ? $rtec_options['confirmation_from_address'] : get_option( 'admin_email' );
            $email_from = $this->strip_malicious( $rtec_options['notification_from'] ) . ' <' . $notification_from_address . '>';
            $headers = 'From: ' . $email_from;
        } else {
            $headers = '';
        }

        return $headers;
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    public function get_not_recipient()
    {
	    global $rtec_options;

	    $raw_recipients = rtec_get_notification_email_recipients( $this->event_meta['post_id'] );
        $recipients = isset( $raw_recipients ) ? explode( ',', str_replace( ' ', '', $raw_recipients ) ) : array( get_option( 'admin_email' ) );
        $valid_recipients = array();

        foreach ( $recipients as $recipient ) {

            if ( is_email( $recipient ) ) {
                $valid_recipients[] = $recipient;
            }

        }

        if ( ! empty( $valid_recipients ) ) {
            return $valid_recipients;
        } else {
        	return get_option( 'admin_email' );
        }
    }

	/**
	 * @since 1.0
	 * @return string
	 */
    public function get_not_subject()
    {
	    global $rtec_options;

	    if ( isset( $rtec_options['notification_subject'] ) && ! empty ( $rtec_options['notification_subject'] ) && $rtec_options['message_source'] !== 'translate' ) {
		    $subject = $this->strip_malicious( $this->find_and_replace( $rtec_options['notification_subject'] ) );

		    return $subject;
	    }

	    return __( 'New Registration', 'registrations-for-the-events-calendar' );
    }

	/**
	 * @since 1.0
	 * @return bool
	 */
    public function send_notification_email() 
    {
        $notification_header = $this->get_not_header();
        $notification_message = $this->get_not_message();
        $notification_recipient = $this->get_not_recipient();
        $notification_subject = $this->get_not_subject();

	    return wp_mail( $notification_recipient, $notification_subject, $notification_message, $notification_header );
    }

	/**
	 * @since 1.0
	 * @return array
	 */
    public function get_db_data()
    {
        $data = array();
        foreach ( $this->submission as $key => $value ) {
            $data[$key] = $value;
        }

        return $data;
    }
}
RTEC_Submission::instance();