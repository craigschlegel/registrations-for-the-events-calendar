<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class RTEC_Submission
{
    private static $instance;
    public $submission = array();

    public $errors = array();

    protected $required_fields = array();

    public $validate_check = array();

    public function __construct( $post )
    {
        $this->submission = $post;
        
        $this->validate_data();
    }
    /**
     * Get the one true instance of EDD_Register_Meta.
     *
     * @since  1.0
     * @return $instance
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
            // if the form field is a required first, last, email, or other
            if ( $input_key === 'rtec_first' && $options['first_require'] ) {
                if ( preg_match( '~[0-9]~', $input_value ) ||
                   ( strlen( $input_value ) > 40 ) ||
                   ( strlen( $input_value ) < 2 ) ) {
                    $this->errors[] = 'first';
                }
            } elseif ( $input_key === 'rtec_last' && $options['last_require'] ) {
                if ( preg_match( '~[0-9]~', $input_value ) ||
                   ( strlen( $input_value ) > 40 ) ||
                   ( strlen( $input_value ) < 2 ) ) {
                    $this->errors[] = 'last';
                }
            } elseif ( $input_key === 'rtec_email' && $options['email_require'] ) {
                if ( ! is_email( $input_value ) ) {
                    $this->errors[] = 'email';
                }
            } elseif ( $input_key === 'rtec_other' && $options['other_require'] ) {
                if ( empty( $input_value ) ) {
                    $this->errors[] = 'other';
                }
            }
        }
    }
    public function has_errors()
    {
        return ! empty( $this->errors );
    }
    public function get_errors() 
    {
        return $this->errors;
    }
    public function get_data()
    {
        return $this->submission;
    }
    
    public function sanitize_submission() 
    {
        $submission = $this->submission;
        // for each submitted form field
        foreach ( $submission as $input_key => $input_value ) {
            // sanitize the input value
            $new_val = sanitize_text_field( $input_value );
            // strip potentially malicious header strings
            $new_val  = $this->strip_malicious( $new_val  );
            // assign the sanitized value
            $this->submission[$input_key] = $new_val;
        }
    }

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

    public function email_given()
    {
        if ( ! empty( $this->submission['rtec_email'] ) ) {
            return true;
        }
        return false;
    }

    private function get_conf_message( $options )
    {
        $body = $options['confirmation_message'];

        $date_str = date_i18n( 'F jS, g:i a', strtotime( $this->submission['rtec_date'] ) );

        if ( isset( $options['confirmation_message'] ) ) {
            $raw_body = $options['confirmation_message'];
            $search = array( '{venue}', '{event-title}', '{event-date}', '{first}', '{last}', '{email}', '{other}', '{nl}' );
            $replace   = array( $this->submission['rtec_venue_title'], $this->submission['rtec_title'], $date_str, $this->submission['rtec_first'], $this->submission['rtec_last'], $this->submission['rtec_email'], $this->submission['rtec_other'], "\n" );

            $body = str_replace( $search, $replace, $raw_body );
        } else {
            $body = 'You are registered!'."\n\n";
            $body .= sprintf( 'Event: %1$s at %2$s on %3$s'. "\n",
                esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );

            $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
            $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';
            $body .= sprintf ( 'Registered Name: %1$s%2$s', $first, $last ) . "\n";

            if ( ! empty( $this->submission['rtec_other'] ) ) {
                $other = esc_html( $this->submission['rtec_other'] );
                $body .= sprintf ( 'Other: %1$s', $other ) . "\n";
            }
        }

        return $body;
    }

    private function get_conf_header( $email_options )
    {
        $options = $email_options;

        if ( ! empty ( $options['confirmation_from'] ) && ! empty ( $options['confirmation_from_address'] ) ) {
            $confirmation_from_address = is_email( $options['confirmation_from_address'] ) ? $options['confirmation_from_address'] : get_option( 'admin_email' );
            $email_from = $this->stripMaliciousHeaders( $options['confirmation_from'] ) . ' <' . $confirmation_from_address . '>';
            $headers = 'From: ' . $email_from;
        } else {
            $headers = '';
        }

        return $headers;
    }

    private function get_conf_recipient()
    {
        return $this->submission['rtec_email'];
    }

    private function get_conf_subject( $email_options )
    {
        $options = $email_options;

        if ( ! empty ( $options['confirmation_subject'] ) ) {
            return $this->stripMaliciousHeaders( $options['confirmation_subject'] );
        }

        return 'Thank You';
    }
    
    public function send_confirmation_email() {
        $confirmation_header = $this->get_conf_header();
        $confirmation_message = $this->get_conf_message();
        $confirmation_recipient = $this->get_conf_recipient();
        $confirmation_subject = $this->get_conf_subject();
        return wp_mail( $confirmation_recipient, $confirmation_subject, $confirmation_message, $confirmation_header );
    }

    public function get_not_message()
    {
        $body = '';
        $date_str = date_i18n( 'F j, Y', strtotime( $this->submission['rtec_date'] ) );
        $body .= sprintf( 'The following submission was made for: %1$s at %2$s on %3$s'. "\n",
            esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );
        $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
        $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';
        $body .= sprintf ( 'Registered Name: %1$s%2$s', $first, $last ) . "\n";
        if ( ! empty( $this->submission['rtec_email'] ) ) {
            $email = esc_html( $this->submission['rtec_email'] );
            $body .= sprintf ( 'Email: %1$s', $email ) . "\n";
        }
        if ( ! empty( $this->submission['rtec_other'] ) ) {
            $other = esc_html( $this->submission['rtec_other'] );
            $body .= sprintf ( 'Other: %1$s', $other ) . "\n";
        }
        return $body;
    }

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
        }
    }

    public function get_not_subject()
    {
        return 'New Submission';
    }
    
    public function send_notification_email() 
    {
        $notification_header = $this->get_not_header();
        $notification_message = $this->get_not_message();
        $notification_recipient = $this->get_not_recipient();
        $notification_subject = $this->get_not_subject();
        return wp_mail( $notification_recipient, $notification_subject, $notification_message, $notification_header );
    }
    
    public function get_db_data()
    {
        $data = array();
        foreach ( $this->submission as $key => $value ) {
            $data[$key] = $value;
        }
        return $data;
    }
}
RTEC_Submission::instance( $_POST );