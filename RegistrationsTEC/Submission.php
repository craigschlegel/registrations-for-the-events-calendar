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
    protected $submission = array();

    protected $errors = array();

    protected $required_fields = array();

    public $validate_check = array();

    public function __construct( $post )
    {
        $sanitized_post = array();

        foreach ( $post as $post_key => $raw_post_value) {
            $sanitized_post[$post_key] = sanitize_text_field( $raw_post_value );
        }

        $this->submission = $sanitized_post;
    }

    public function validateSubmissionData() {
        // get form options from the db
        $options = get_option('rtec_general');
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
        $body .= sprintf( 'Event: %1$s at %2$s on %3$s'. "\n",
            esc_html( $this->submission['title'] ) , esc_html( $this->submission['venue_title'] ) , esc_html( $this->submission['date'] ) );

        $first = ! empty( $this->submission['first'] ) ? esc_html( $this->submission['first'] ) . ' ' : '';
        $last = ! empty( $this->submission['last'] ) ? esc_html( $this->submission['last'] ) : '';
        $body .= sprintf ( 'Registered Name: %1$s %2$s', $first, $last ) . "\n";

        $other = ! empty( $this->submission['other'] ) ? esc_html( $this->submission['other'] ) . ' ' : '';
        $body .= sprintf ( 'Other: %1$s', $other ) . "\n";

        return $body;

        //wp_mail( $recipient, $subj, $body, $headers );
    }

    public function getConfirmationEmailHeader() {
        $options = get_option( 'rtec_email' );

        $email_from = $options['customer_from'].' <'.$options['customer_from_address'].'>';
        $headers = 'From: ' . $email_from;
    }
}