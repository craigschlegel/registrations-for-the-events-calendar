<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/16/2016
 * Time: 1:43 PM
 */

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class Form
{
    private $event_meta = array();
    
    private $show_fields = array();

    private $required_fields = array();

    private $input_fields_data = array();

    private $submission_data = array();

    private $errors = array();
    
    private $max_registrations;

    public function __construct( $fields, $submission_data, $errors )
    {
        // get form options from the db
        $options = get_option( 'rtec_general', array() );

        foreach ( $fields as $field ) {
            // create an array of all to be shown
            if ( $options[$field . '_show'] == true ) {
                $this->show_fields[] = $field;
            }
            // create an array of all to be required
            if ( $options[$field . '_require'] == true ) {
                $this->required_fields[] = $field;
            }
        }

        $this->submission_data = $submission_data;
        $this->errors = $errors;
    }

    public function setEventMeta( $id = '' )
    {
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
        $event_meta['currency_symbol'] = $meta['_EventCurrencySymbol'][0];
        $event_meta['cost'] = $meta['_EventCost'][0];
        $event_meta['venue_title'] = $venue_meta["_VenueVenue"][0];
        
        $this->event_meta = $event_meta;
    }
    
    public function setInputFieldsData( $options )
    {
        $input_fields_data = array();
        $show_fields = $this->show_fields;
        $required_fields = $this->required_fields;
        
        $standard_field_types = array( 'first', 'last', 'email' );
        
        foreach ( $standard_field_types as $type ) {

            if ( in_array( $type, $show_fields ) ) {
                $input_fields_data[$type]['name'] = $type;
                $input_fields_data[$type]['require'] = in_array( $type, $required_fields );
                $input_fields_data[$type]['error_message'] = $options[$type . '_error'];

                switch( $type ) {
                    case 'first':
                        $input_fields_data['first']['label'] = 'First';
                        break;
                    case 'last':
                        $input_fields_data['last']['label'] = 'Last';
                        break;
                    case 'email':
                        $input_fields_data['email']['label'] = 'Email';
                        break;
                }
            }

        }

        // the "other" fields is handled slightly differently
        if ( in_array( 'other', $show_fields ) ) {
            $input_fields_data['other']['name'] = 'other';
            $input_fields_data['other']['require'] = $options['other_require'];
            $input_fields_data['other']['error_message'] = $options['other_error'];
            $input_fields_data['other']['label'] = $options['other_label'];
        }

        $this->input_fields_data = $input_fields_data;
    }

    public function setMaxRegistrations( $default_max_registrations )
    {
        $this->max_registrations = $default_max_registrations;
    }

    public function getMaxRegistrations()
    {
        return $this->max_registrations;
    }

    public function getBeginningFormHtml( $options )
    {
        $button_text = isset( $options['register_text'] ) ? esc_attr( $options['register_text'] ) : 'Register';
        $spots_remaining_text = isset( $options['spots_remaining_text'] ) ? esc_attr( $options['spots_remaining_text'] ) : 'Spots Remaining';
        $html = '';

        $html .= '<button type="button" id="rtec-form-toggle-button" class="rtec-register-button">' . $button_text . '<span class="tribe-bar-toggle-arrow"></span></button>';

        $html .= '<div class="rtec-form-wrapper">';
        
            $html .= $this->getAttendanceHtml( $spots_remaining_text );

            $html .= '<form method="post" action="" id="rtec-form" class="rtec-form">';

        return $html;
    }

    public function getAttendanceHtml( $spots_remaining_text )
    {
        $html = '';
        
        if ( $this->getMaxRegistrations() !== 'i' ) {
            $html .= '<div class="rtec-attendance">';
                $html .= '<span><span class="rtec-spots-remaining"></span> '.$spots_remaining_text.'</span>';
            $html .= '</div>';
        }
        
        return $html;

    }

    public function getHiddenInputFieldsHtml()
    {
        $html = '';

        $event_meta = $this->event_meta;

        $html .= '<input type="hidden" name="rtec_email_submission" value="1" />';
        $html .= '<input type="hidden" name="rtec_title" value="'. $event_meta['title'] . '" />';
        $html .= '<input type="hidden" name="rtec_venue_title" value="'. $event_meta['venue_title'] . '" />';
        $html .= '<input type="hidden" name="rtec_date" value="'. $event_meta['start_date'] . '" />';
        $html .= '<input type="hidden" name="rtec_event_id" value="' . $event_meta['post_id'] . '" />';

        return $html;
    }

    public function getRegularInputFieldsHtml( $post = '' )
    {
        $html = '<div class="rtec-form-fields-wrapper">';

        foreach ( $this->input_fields_data as $field ) {
            // check to see if there was an error and fill in
            // previous data
            $value = '';
            $error_html = '';

            if ( in_array( $field['name'], $this->errors ) ) {
                $error_html = '<p>' . $field['error_message'] . '</p>';
            }

            if ( isset( $this->submission_data['rtec_' . $field['name']] ) ) {
                $value = $this->submission_data['rtec_' . $field['name']];
            }

            $html .= '<div class="rtec-form-field rtec-'. $field['name'] . '">';
                $html .= '<label for="rtec_' . $field['name'] . '" class="rtec_text_label">' . $field['label'] . '</label>';
                $html .= '<input type="text" name="rtec_' . $field['name'] . '" value="'. $value . '" id="rtec_' . $field['name'] . '" />';
                $html .= $error_html;
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function getEndingFormHtml( $options )
    {
        $button_text = isset( $options['submit_text'] ) ? esc_attr( $options['submit_text'] ) : 'Submit';
        $html = '';

                $html .= '<input type="submit" class="rtec-submit-button" name="rtec_submit" value="' . $button_text . '"/>';

            $html .= '</form>';
        $html .= '</div>';
        return $html;
    }
}