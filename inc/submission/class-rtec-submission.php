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
	 * @since 1.0
	 */
    public $validate_check = array();

	/**
	 * RTEC_Submission constructor.
	 *
	 * @param $post $_POST data
	 * @since 1.0
	 */
    public function __construct( $post )
    {
        $this->submission = $post;

        $this->validate_data();
    }

    public function attendance_limit_not_reached( $num_registered = 0 )
    {
	    $options = get_option( 'rtec_options' );

	    if ( $options['limit_registrations'] ) {
	    	$registrations_left = $options['default_max_registrations'] - (int)$num_registered;

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

    public function validate_data() {
        // get form options from the db
        $options = get_option( 'rtec_options' );
        $submission = $this->submission;

        // for each submitted form field
        foreach ( $submission as $input_key => $input_value ) {
        	// check spam honeypot, error if not empty
        	if ( $input_key === 'rtec_user_address' && ! empty( $input_value ) ) {
        		$this->errors[] = 'user_address';
	        }
            // if the form field is a required first, last, email, or other
            if ( $input_key === 'rtec_first' && $options['first_require'] ) {
            	
                if ( ( strlen( $input_value ) > 40 ) ||
                   ( strlen( $input_value ) < 2 ) ) {
                    $this->errors[] = 'first';
                }
                
            } elseif ( $input_key === 'rtec_last' && $options['last_require'] ) {
            	
                if ( ( strlen( $input_value ) > 40 ) ||
                   ( strlen( $input_value ) < 2 ) ) {
                    $this->errors[] = 'last';
                }
                
            } elseif ( $input_key === 'rtec_email' && $options['email_require'] ) {
            	
                if ( ! is_email( $input_value ) ) {
                    $this->errors[] = 'email';
                }
                
            } elseif ( $input_key === 'rtec_phone' && $options['phone_require'] ) {
	            $stripped_input = preg_replace( '/[^0-9]/', '', $input_value );
	            $valid_counts_arr = isset( $options['phone_valid_counts'] ) ? explode( ',' , $options['phone_valid_counts'] ) : array( 7, 10 );
	            $valid_length_count = 0;

	            foreach ( $valid_counts_arr as $valid_count ) {
	            	if ( strlen( $stripped_input ) === $valid_count ) {
	            		$valid_length_count++;
		            }
	            }

	            if ( $valid_length_count < 1 ) {
		            $this->errors[] = 'phone';
	            }

            } elseif ( $input_key === 'rtec_other' && $options['other_require'] ) {
            	
                if ( empty( $input_value ) ) {
                    $this->errors[] = 'other';
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

        if ( isset( $rtec_options['confirmation_message'] ) ) {
            $raw_body = $rtec_options['confirmation_message'];
            $search = array( '{venue}', '{venue-address}', '{venue-city}', '{venue-state}', '{venue-zip}', '{event-title}', '{event-date}', '{first}', '{last}', '{email}', '{phone}', '{other}', '{ical-url}', '{nl}' );
            $replace = array( $this->submission['rtec_venue_title'], $this->submission['rtec_venue_address'], $this->submission['rtec_venue_city'], $this->submission['rtec_venue_state'], $this->submission['rtec_venue_zip'], $this->submission['rtec_title'], $date_str, isset( $this->submission['rtec_first'] ) ? $this->submission['rtec_first'] : '', isset( $this->submission['rtec_last'] ) ? $this->submission['rtec_last'] : '', isset( $this->submission['rtec_email'] ) ? $this->submission['rtec_email'] : '', isset( $this->submission['rtec_phone'] ) ? rtec_format_phone_number( $this->submission['rtec_phone'] ) : '', isset( $this->submission['rtec_other'] ) ? $this->submission['rtec_other'] : '', $this->submission['ical_url'], "\n" );

            $body = str_replace( $search, $replace, $raw_body );
        } else {
            $body = 'You are registered!'."\n\n";
            $body .= sprintf( 'Event: %1$s at %2$s on %3$s'. "\n",
                esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );
            $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
            $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';
            $body .= sprintf ( 'Registered Name: %1$s %2$s', $first, $last ) . "\n";

	        if ( ! empty( $this->submission['rtec_phone'] ) ) {
		        $phone = esc_html( $this->submission['rtec_phone'] );
		        $body .= sprintf ( 'Phone: %1$s', $phone ) . "\n";
	        }
	        
            if ( ! empty( $this->submission['rtec_other'] ) ) {
                $other = esc_html( $this->submission['rtec_other'] );
                $body .= sprintf ( 'Other: %1$s', $other ) . "\n\n";
            }

	        if ( ! empty( $this->submission['rtec_venue_address'] ) ) {
		        $body .= 'The event will be held at this location:'. "\n\n";
		        $body .= sprintf( '%1$s'. "\n", esc_html( $this->submission['rtec_venue_address'] ) );
		        $body .= sprintf( '%1$s, %2$s %3$s'. "\n\n", esc_html( $this->submission['rtec_venue_city'] ), esc_html( $this->submission['rtec_venue_state'] ), esc_html( $this->submission['rtec_venue_zip'] ) );
	        }

	        $body .= 'See you there!';
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

        if ( ! empty ( $rtec_options['confirmation_from'] ) && ! empty ( $rtec_options['confirmation_from_address'] ) ) {
            $confirmation_from_address = is_email( $rtec_options['confirmation_from_address'] ) ? $rtec_options['confirmation_from_address'] : get_option( 'admin_email' );
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

        if ( ! empty ( $rtec_options['confirmation_subject'] ) ) {
            return $this->strip_malicious( $rtec_options['confirmation_subject'] );
        }

        return 'Thank You';
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

	    if ( $use_custom_notification ) {
		    $raw_body = $rtec_options['notification_message'];
		    $search = array( '{venue}', '{venue-address}', '{venue-city}', '{venue-state}', '{venue-zip}', '{event-title}', '{event-date}', '{first}', '{last}', '{email}', '{phone}', '{other}', '{ical-url}', '{nl}' );
		    $replace = array( $this->submission['rtec_venue_title'], $this->submission['rtec_venue_address'], $this->submission['rtec_venue_city'], $this->submission['rtec_venue_state'], $this->submission['rtec_venue_zip'], $this->submission['rtec_title'], $date_str, isset( $this->submission['rtec_first'] ) ? $this->submission['rtec_first'] : '', isset( $this->submission['rtec_last'] ) ? $this->submission['rtec_last'] : '', isset( $this->submission['rtec_email'] ) ? $this->submission['rtec_email'] : '', isset( $this->submission['rtec_phone'] ) ? rtec_format_phone_number( $this->submission['rtec_phone'] ) : '', isset( $this->submission['rtec_other'] ) ? $this->submission['rtec_other'] : '', $this->submission['ical_url'], "\n" );

		    $body = str_replace( $search, $replace, $raw_body );
	    } else {
		    $first_label = isset( $rtec_options['first_label'] ) ? esc_html( $rtec_options['first_label'] ) : __( 'First', 'rtec' );
		    $last_label = isset( $rtec_options['last_label'] ) ? esc_html( $rtec_options['last_label'] ) : __( 'Last', 'rtec' );
		    $email_label = isset( $rtec_options['email_label'] ) ? esc_html( $rtec_options['email_label'] ) : __( 'Email', 'rtec' );
		    $phone_label = isset( $rtec_options['phone_label'] ) ? esc_html( $rtec_options['phone_label'] ) : __( 'Phone', 'rtec' );
		    $other_label = isset( $options['other_label'] ) ? esc_html( $options['other_label'] ) : __( 'Other', 'rtec' );

		    $body .= sprintf( 'The following submission was made for: %1$s at %2$s on %3$s'. "\n",
			    esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );
		    $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
		    $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';

		    if ( ! empty( $this->submission['rtec_first'] ) ) {
			    $body .= sprintf( '%s: %s', $first_label, $first ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_last'] ) ) {
			    $body .= sprintf( '%s: %s', $last_label, $last ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_email'] ) ) {
			    $email = esc_html( $this->submission['rtec_email'] );
			    $body .= sprintf( '%s: %s', $email_label, $email ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_phone'] ) ) {
			    $phone = rtec_format_phone_number( esc_html( $this->submission['rtec_phone'] ) );
			    $body .= sprintf( '%s: %s', $phone_label, $phone ) . "\n";
		    }

		    if ( ! empty( $this->submission['rtec_other'] ) ) {
			    $other = esc_html( $this->submission['rtec_other'] );
			    $body .= sprintf( '%s: %s', $other_label, $other ) . "\n";
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

        $recipients = explode( ',', $rtec_options['recipients'] );
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
        return 'New Registration';
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