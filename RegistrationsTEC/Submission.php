<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/2016
 * Time: 9:33 AM
 */

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class Submission
{
    public $submission = array();

    public $errors = array();

    protected $required_fields = array();

    public $validate_check = array();

    public function __construct( $post )
    {
        $sanitized_post = array();

        foreach ( $post as $post_key => $raw_post_value ) {
            $sanitized_post[$post_key] = sanitize_text_field( $raw_post_value );
        }

        $this->submission = $sanitized_post;
    }

    public function validateSubmissionData() {
        // get form options from the db
        $options = get_option( 'rtec_general' );
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

            $this->submission[$input_key] = $this->stripMaliciousHeaders( $input_value );
        }
    }
    
    public function sanitizeSubmissionData() 
    {
        $submission = $this->submission;

        // for each submitted form field
        foreach ( $submission as $input_key => $input_value ) {

            // sanitize the input value
            $new_val = sanitize_text_field( $input_value );
            
            // strip potentially malicious header strings
            $new_val  = $this->stripMaliciousHeaders( $new_val  );
            
            // assign the sanitized value
            $this->submission[$input_key] = $new_val;
        }
    }

    private function stripMaliciousHeaders( $value )
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

    public function emailAddressGiven()
    {
        if ( ! empty( $this->submission['rtec_email'] ) ) {
            return true;
        }
        return false;
    }

    public function getConfirmationEmailMessage( $options )
    {
        $body = $options['confirmation_message']. "\n\n";

        $date_str = date_i18n( 'F j, Y', strtotime( $this->submission['rtec_date'] ) );

        $body .= sprintf( 'Event: %1$s at %2$s on %3$s'. "\n",
            esc_html( $this->submission['rtec_title'] ) , esc_html( $this->submission['rtec_venue_title'] ) , $date_str );

        $first = ! empty( $this->submission['rtec_first'] ) ? esc_html( $this->submission['rtec_first'] ) . ' ' : ' ';
        $last = ! empty( $this->submission['rtec_last'] ) ? esc_html( $this->submission['rtec_last'] ) : '';
        $body .= sprintf ( 'Registered Name: %1$s%2$s', $first, $last ) . "\n";

        if ( ! empty( $this->submission['rtec_other'] ) ) {
            $other = esc_html( $this->submission['rtec_other'] );
            $body .= sprintf ( 'Other: %1$s', $other ) . "\n";
        }

        return $body;
    }

    public function getConfirmationEmailHeader( $email_options )
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

    public function getConfirmationEmailRecipient()
    {
        return $this->submission['rtec_email'];
    }

    public function getConfirmationEmailSubject( $email_options )
    {
        $options = $email_options;

        if ( ! empty ( $options['confirmation_subject'] ) ) {
            return $this->stripMaliciousHeaders( $options['confirmation_subject'] );
        }

        return 'Thank You';
    }

    public function getNotificationEmailMessage()
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

    public function getNotificationEmailHeader( $email_options )
    {
        $options = $email_options;

        if ( ! empty ( $options['notification_from'] ) && ! empty ( $options['confirmation_from_address'] ) ) {
            $notification_from_address = is_email( $options['confirmation_from_address'] ) ? $options['confirmation_from_address'] : get_option( 'admin_email' );
            $email_from = $this->stripMaliciousHeaders( $options['notification_from'] ) . ' <' . $notification_from_address . '>';
            $headers = 'From: ' . $email_from;
        } else {
            $headers = '';
        }

        return $headers;
    }

    public function getNotificationEmailRecipient( $email_options )
    {
        $options = $email_options;

        $recipients = explode( ',', $options['recipients'] );
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

    public function getNotificationEmailSubject()
    {
        return 'New Submission';
    }
    
    public function sendEmail( $header, $message, $recipient, $subject )
    {
        //echo '<pre>';
        return wp_mail( $header,$message,$recipient,$subject );
        //echo '</pre>';
    }
    
    public function getDbData()
    {
        $data = array();
        
        foreach ( $this->submission as $key => $value ) {
            $data[$key] = $value;
        }

        return $data;
    }
}