<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class RTEC_Form
{
	/**
	 * @var RTEC_Form
	 * @since 1.0
	 */
    private static $instance;

	/**
	 * @var array
	 * @since 1.0
	 */
    private $event_meta;

	/**
	 * @var array
	 * @since 1.0
	 */
    private $show_fields = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    private $required_fields = array();

	/**
	 * @var array
	 * @since 1.3
	 */
	private $custom_fields = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    private $input_fields_data = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    private $submission_data = array();

	/**
	 * @var array
	 * @since 1.0
	 */
    private $errors = array();

	/**
	 * @var int
	 * @since 1.0
	 */
    private $max_registrations;

	/**
	 * @var array
	 * @since 1.1
	 */
	private $ical_url;

	/**
	 * @var array
	 * @since 1.1
	 */
	private $recaptcha = array();
    
    /**
     * Get the one true instance of RTEC_Form.
     *
     * @since  1.0
     * @return object $instance
     */
    static public function instance() 
    {
        if ( !self::$instance ) {
            self::$instance = new RTEC_Form();
        }
        return self::$instance;
    }

	/**
	 * Set included and required fields for this form
	 *
	 */
	public function set_inc_and_req_fields() {
		global $rtec_options;

		$fields = array( 'first', 'last', 'email', 'phone', 'other' );
		// phone should be false by default
		if ( ! isset( $rtec_options['phone_show'] ) ) {
			$rtec_options['phone_show'] = false;
			$rtec_options['phone_require'] = false;
		}

		foreach ( $fields as $field ) {

			// prevent errors from popping up by defaulting all settings to true
			if ( ! isset( $rtec_options[$field . '_show'] ) ) {
				$rtec_options[$field . '_show'] = true;
			}
			// create an array of all to be shown
			if ( $rtec_options[$field . '_show'] == true ) {
				$this->show_fields[] = $field;
			}

			// prevent errors from popping up by defaulting all settings to true
			if ( ! isset( $rtec_options[$field . '_require'] ) ) {
				$rtec_options[$field . '_require'] = true;
			}
			// create an array of all to be required
			if ( $rtec_options[$field . '_require'] == true ) {
				$this->required_fields[] = $field;
			}
		}

		// recaptcha field calculations for spam check
		if ( isset( $rtec_options['recaptcha_require'] ) && $rtec_options['recaptcha_require'] )  {
			$this->recaptcha = array(
				'value_1' => rand(2,5),
				'value_2' => rand(2,5)
			);
			$this->recaptcha['sum'] = (int)$this->recaptcha['value_1'] + (int)$this->recaptcha['value_2'];
		}
	}

	/**
	 * Set any custom field data set up by user
	 *
	 * @since 1.3
	 */
    public function set_custom_fields() 
    {
    	global $rtec_options;
	    
	    if ( isset( $rtec_options['custom_field_names'] ) ) {
	    	$custom_field_names = explode( ',', $rtec_options['custom_field_names'] );
	    } else {
		    $custom_field_names = array();
	    }

	    $rtec_options['custom_field_names'] = $custom_field_names;

	    $this->custom_fields = $rtec_options;
    }

	/**
	 * Set user input errors for the form
	 *
	 * @param array $errors names of fields that have validation errors
	 * @since 1.0
	 */
	public function set_errors( $errors )
	{
		$this->errors = $errors;
	}

	/**
	 * @param array $submission submitted data from user
	 * @since 1.0
	 */
	public function set_submission_data( $submission )
	{
		$this->submission_data = $submission;
	}

	/**
	 * @param string $id    optional manual input of post ID
	 * @since 1.0
	 */
	public function set_event_meta( $id = '' )
	{
		$this->event_meta = rtec_get_event_meta( $id );
	}

	/**
	 * @param string $url    url retrieved using tribe_get_single_ical_link
	 * @since 1.1
	 */
	public function set_ical_url( $url )
	{
		$this->ical_url = $url;
	}

	/**
	 * @param string $id    optional manual input of post ID
	 * @since 1.1
	 *
	 * @return array
	 */
	public function get_event_meta( $id = '' )
	{
		if ( ! isset( $this->event_meta ) ) {
			$this->event_meta = rtec_get_event_meta( $id );
			return $this->event_meta;
		} else {
			return $this->event_meta;
		}
	}

	/**
	 * @since 1.1
	 *
	 * @return bool
	 */
	public function registrations_are_disabled()
	{
		return ( $this->event_meta['registrations_disabled'] === '1' || $this->event_meta['registrations_disabled'] === true );
	}

	/**
	 * @since 1.1
	 *
	 * @return bool
	 */
	public function registration_deadline_has_passed()
	{
		global $rtec_options;

		$WP_offset = get_option( 'gmt_offset' );

		if ( ! empty( $WP_offset ) ) {
			$tz_offset = $WP_offset * HOUR_IN_SECONDS;
		} else {
			$timezone = isset( $rtec_options['timezone'] ) ? $rtec_options['timezone'] : 'America/New_York';
			// use php DateTimeZone class to handle the date formatting and offsets
			$date_obj = new DateTime( date( 'm/d g:i a' ), new DateTimeZone( "UTC" ) );
			$date_obj->setTimeZone( new DateTimeZone( $timezone ) );
			$utc_offset = $date_obj->getOffset();
			$tz_offset = $utc_offset;
		}

		$deadline_multiplier = isset( $rtec_options['registration_deadline'] ) ? sanitize_text_field( $rtec_options['registration_deadline'] ) : 0;
		$deadline_unit = isset( $rtec_options['registration_deadline_unit'] ) ? sanitize_text_field( $rtec_options['registration_deadline_unit'] ) : 0;
		$deadline_time = strtotime( $this->event_meta['start_date'] ) - $deadline_multiplier * $deadline_unit;

		return( $deadline_time < ( time() + $tz_offset ) );
	}

	/**
	 * Combine required and included fields to use in a loop later
	 *
	 * @since 1.0
	 */
    public function set_input_fields_data()
    {
        global $rtec_options;

        $input_fields_data = array();
        $show_fields = $this->show_fields;
        $required_fields = $this->required_fields;
        
        $standard_field_types = array( 'first', 'last', 'email', 'phone' );
        
        foreach ( $standard_field_types as $type ) {
            if ( in_array( $type, $show_fields ) ) {
                $input_fields_data[$type]['name'] = $type;
                $input_fields_data[$type]['require'] = in_array( $type, $required_fields );
	            $input_fields_data[$type]['valid_count'] = isset( $rtec_options[$type . '_valid_count'] ) ? ' data-rtec-valid-count="' . $rtec_options[$type . '_valid_count'].'"' : '';
	            $input_fields_data[$type]['error_message'] = rtec_get_text( $rtec_options[$type . '_error'], __( 'Error', 'registrations-for-the-events-calendar' ) );

                switch( $type ) {
                    case 'first':
                        $input_fields_data['first']['label'] = rtec_get_text( $rtec_options['first_label'], __( 'First', 'registrations-for-the-events-calendar' ) );
                        break;
                    case 'last':
                        $input_fields_data['last']['label'] = rtec_get_text( $rtec_options['last_label'], __( 'Last', 'registrations-for-the-events-calendar' ) );
                        break;
                    case 'email':
                        $input_fields_data['email']['label'] = rtec_get_text( $rtec_options['email_label'], __( 'Email', 'registrations-for-the-events-calendar' ) );
                        break;
	                case 'phone':
		                $input_fields_data['phone']['label'] = rtec_get_text( $rtec_options['phone_label'], __( 'Phone', 'registrations-for-the-events-calendar' ) );
		                break;
                }
            }
        }

        // the "other" fields is handled slightly differently
        if ( in_array( 'other', $show_fields ) ) {
            $input_fields_data['other']['name'] = 'other';
            $input_fields_data['other']['require'] = isset( $rtec_options['other_require'] ) ? $rtec_options['other_require'] : true;
            $input_fields_data['other']['error_message'] = rtec_get_text( $rtec_options['other_error'], __( 'Error', 'registrations-for-the-events-calendar' ) );
            $input_fields_data['other']['label'] = rtec_get_text( $rtec_options['other_label'], __( 'Other', 'registrations-for-the-events-calendar' ) );
	        $input_fields_data['other']['valid_count'] = isset( $rtec_options['other_valid_count'] ) ? ' data-rtec-valid-count="' . $rtec_options['other_valid_count'].'"' : '';
        }

        $this->input_fields_data = $input_fields_data;
    }

	/**
	 * @since 1.0
	 */
    public function set_max_registrations()
    {
	    global $rtec_options;

        $this->max_registrations = isset( $rtec_options['default_max_registrations'] ) ? $rtec_options['default_max_registrations'] : 30;
    }

	/**
	 * @since 1.0
	 * @return int  the maximum registrations
	 */
    public function get_max_registrations()
    {
    	if ( !isset( $this->max_registrations ) ) {
    		$this->set_max_registrations();
	    }

        return $this->max_registrations;
    }

	/**
	 * Are there still registration spots available?
	 *
	 * @since 1.0
	 * @return bool
	 */
    public function registrations_available()
    {
    	global $rtec_options;

    	$max_registrations = $this->get_max_registrations();
	    if ( isset( $rtec_options['limit_registrations'] ) && ! $rtec_options['limit_registrations'] ) {
	    	return true;
	    }

    	if ( ( $max_registrations - $this->event_meta['num_registered'] ) > 0 ) {
    		return true;
	    } else {
	    	return false;
	    }
    }

	/**
	 * Message if registrations are closed
	 *
	 * @since 1.0
	 * @return string   the html for registrations being closed
	 */
	public function registrations_closed_message()
	{
		global $rtec_options;

		$message = isset( $rtec_options['registrations_closed_message'] ) ? $rtec_options['registrations_closed_message'] : __( 'Registrations are closed for this event', 'registrations-for-the-events-calendar' );

		return '<p class="rtec-success-message tribe-events-notices">' . esc_html( $message ) . '</p>';
	}

	/**
	 * The html that creates the feed is broken into parts and pieced together
	 *
	 * @since 1.0
	 * @return string
	 */
    public function get_beginning_html()
    {
	    global $rtec_options;

        $button_text = rtec_get_text( $rtec_options['register_text'], __( 'Register', 'registrations-for-the-events-calendar' ) );
	    $button_bg_color = isset( $rtec_options['button_bg_color'] ) ? esc_attr( $rtec_options['button_bg_color'] ) : '';
	    $button_styles = isset( $button_bg_color ) && ! empty( $button_bg_color ) ? 'background-color: ' . $button_bg_color . ';' : '';
	    $button_hover_class = ! empty( $button_bg_color ) ? ' rtec-custom-hover' : '';
	    $button_classes = ! empty( $button_hover_class ) ? $button_hover_class : '';
	    $form_bg_color = isset( $rtec_options['form_bg_color'] ) && ! empty( $rtec_options['form_bg_color'] ) ? 'background-color: ' . esc_attr( $rtec_options['form_bg_color'] ) . ';' : '';
	    $width_unit = isset( $rtec_options['width_unit'] ) ? esc_attr( $rtec_options['width_unit'] ) : '%';
        $width = isset( $rtec_options['width'] ) ? 'width: ' . esc_attr( $rtec_options['width'] ) . $width_unit . ';' : '';
        $data = ' data-rtec-success-message="' . rtec_get_text( esc_attr( $rtec_options['success_message'] ), __( 'Success! Please check your email inbox for a confirmation message', 'registrations-for-the-events-calendar' ) ) . '"';

	    $html = '<div id="rtec" class="rtec"' . $data . '>';
            $html .= '<button type="button" id="rtec-form-toggle-button" class="rtec-register-button rtec-js-show' . $button_classes . '" style="' . $button_styles . '">' . esc_html( $button_text ). '<span class="tribe-bar-toggle-arrow"></span></button>';
            $html .= '<h3 class="rtec-js-hide">' . esc_html( $button_text ) . '</h3>';
            $html .= '<div class="rtec-form-wrapper rtec-js-hide rtec-toggle-on-click"' . ' style="'. $width . $form_bg_color . '">';

            if ( ! empty( $this->errors ) ) {
                $html .= '<div class="rtec-screen-reader" role="alert">';
                $html .= __( 'There were errors with your submission. Please try again.', 'registrations-for-the-events-calendar' );
                $html .= '</div>';
            }

            if ( ! isset( $rtec_options['include_attendance_message'] ) || $rtec_options['include_attendance_message'] ) {
                $html .= $this->get_attendance_html();
            }
                $html .= '<form method="post" action="" id="rtec-form" class="rtec-form">';

        return $html;
    }

	/**
	 * The html that creates the feed is broken into parts and pieced together
	 *
	 * @since 1.0
	 * @return string
	 */
    public function get_attendance_html()
    {
	    global $rtec_options;

	    $attendance_message_type = isset( $rtec_options['attendance_message_type'] ) ? $rtec_options['attendance_message_type'] : 'up';

	    // a "count down" type of message won't work if there isn't a limit so we check to see if that's true here
	    if ( isset( $rtec_options['limit_registrations'] ) && ! $rtec_options['limit_registrations'] ) {
		    $attendance_message_type = 'up';
	    }

        $html = '';

            if ( $attendance_message_type === 'up' ) {
                $display_num = $this->event_meta['num_registered'];
                $text_before = rtec_get_text( $rtec_options['attendance_text_before_up'], __( 'Join', 'registrations-for-the-events-calendar' ) );
                $text_after = rtec_get_text( $rtec_options['attendance_text_after_up'], __( 'others!', 'registrations-for-the-events-calendar' ) );
            } else {
                $display_num = $this->get_max_registrations() - $this->event_meta['num_registered'];
	            $text_before = rtec_get_text( $rtec_options['attendance_text_before_down'], __( 'Only', 'registrations-for-the-events-calendar' ) );
	            $text_after = rtec_get_text( $rtec_options['attendance_text_after_down'], __( 'spots left', 'registrations-for-the-events-calendar' ) );
            }

            $text_string = sprintf( '%s %s %s', $text_before, (string)$display_num, $text_after );
            if ( $display_num == '1' && $attendance_message_type === 'up' ) {
	            $text_string = rtec_get_text( $rtec_options['attendance_text_one_up'], __( 'Join one other person', 'registrations-for-the-events-calendar' ) );
            } elseif ( $display_num == '1' && $attendance_message_type === 'down' ) {
			    $text_string = rtec_get_text( $rtec_options['attendance_text_one_down'], __( 'Only one spot left!', 'registrations-for-the-events-calendar' ) );
		    }

            if ( $display_num < 1 ) {
                $text_string = rtec_get_text( $rtec_options['attendance_text_none_yet'], __( 'Be the first!', 'registrations-for-the-events-calendar' ) );
            }

            $html .= '<div class="rtec-attendance tribe-events-notices">';
                $html .= '<p>' . esc_html( $text_string ) . '</p>';
            $html .= '</div>';

        return $html;
    }

	/**
	 * Data about the event is also included
	 *
	 * @since 1.0
	 * @return string
	 */
    public function get_hidden_fields_html()
    {
        $html = '';

        $event_meta = $this->event_meta;

        $html .= wp_nonce_field( 'rtec_form_nonce', '_wpnonce', true, false );
        $html .= '<input type="hidden" name="rtec_email_submission" value="1" />';
        $html .= '<input type="hidden" name="rtec_title" value="'. $event_meta['title'] . '" />';
        $html .= '<input type="hidden" name="rtec_venue_title" value="'. $event_meta['venue_title'] . '" />';
	    $html .= '<input type="hidden" name="rtec_venue_address" value="'. $event_meta['venue_address'] . '" />';
	    $html .= '<input type="hidden" name="rtec_venue_city" value="'. $event_meta['venue_city'] . '" />';
	    $html .= '<input type="hidden" name="rtec_venue_state" value="'. $event_meta['venue_state'] . '" />';
	    $html .= '<input type="hidden" name="rtec_venue_zip" value="'. $event_meta['venue_zip'] . '" />';
	    $html .= '<input type="hidden" name="ical_url" value="'. $this->ical_url . '" />';
	    $html .= '<input type="hidden" name="rtec_date" value="'. $event_meta['start_date'] . '" />';
        $html .= '<input type="hidden" name="rtec_event_id" value="' . $event_meta['post_id'] . '" />';
	    $html .= '<input type="hidden" name="rtec_num_registered" value="' . $event_meta['num_registered'] . '" />';

        return $html;
    }

	/**
	 * Return html for custom text fields
	 *
	 * @since 1.3
	 * @return string
	 */
	private function get_custom_fields_html() {
		$html = '';
		$custom_fields = $this->custom_fields;
		$custom_field_names = $this->custom_fields['custom_field_names'];

		foreach ( $custom_field_names as $field ) {

			if ( $custom_fields[$field . '_show'] ) {
				// check to see if there was an error and fill in
				// previous data
				$value = '';
				$label = $custom_fields[$field . '_label'];
				$type = 'text';

				if ( $custom_fields[$field . '_require'] ) {
					$required_data = ' aria-required="true"';
					$label .= '&#42;';
				} else {
					$required_data = ' aria-required="false"';
				}

				$error_html = '';

				if ( in_array( $field, $this->errors ) ) {
					$required_data .= ' aria-invalid="true"';
					$error_html = '<p class="rtec-error-message" role="alert">' . esc_html( $custom_fields[$field . '_error'] ) . '</p>';
				} else {
					$required_data .= ' aria-invalid="false"';
				}

				if ( isset( $this->submission_data['rtec_' . $field] ) ) {
					$value = $this->submission_data['rtec_' . $field];
				}

				$html .= '<div class="rtec-form-field rtec-'. esc_attr( $field ). '" data-rtec-error-message="'.$custom_fields[$field . '_error'].'">';
				$html .= '<label for="rtec_' . esc_attr( $field ) . '" class="rtec_text_label">' . esc_html( $label ) . '</label>';
				$html .= '<div class="rtec-input-wrapper">';
				$html .= '<input type="' . esc_attr( $type ) . '" name="rtec_' . esc_attr( $field ) . '" value="'. esc_attr( $value ) . '" id="rtec_' . esc_attr( $field ) . '"' . $required_data . ' />';
				$html .= $error_html;
				$html .= '</div>';
				$html .= '</div>';
			} // if show

		} // foreach

		return $html;
	}

	/**
	 * Return html for a recaptcha robot detection field
	 *
	 * @since 1.1
	 * @return string
	 */
	private function get_recaptcha_html() {
		global $rtec_options;

		$recaptcha_error_message = rtec_get_text( $rtec_options['recaptcha_error'], __( 'Please try again', 'registrations-for-the-events-calendar' ) );
		$recaptcha_label = rtec_get_text( $rtec_options['recaptcha_label'], __( 'What is', 'registrations-for-the-events-calendar' ) );

		$required_data = ' aria-required="true"';

		$error_html = '';

		if ( in_array( 'recaptcha', $this->errors ) ) {
			$required_data .= ' aria-invalid="true"';
			$error_html = '<p class="rtec-error-message" role="alert">' . esc_html( $recaptcha_error_message ) . '</p>';
		} else {
			$required_data .= ' aria-invalid="false"';
		}

		$html = '<input type="hidden" name="rtec_recaptcha_sum" value="'.( $this->recaptcha['value_1'] + $this->recaptcha['value_2'] ).'" class="rtec-recaptcha-sum" />';
		$html .= '<div class="rtec-form-field rtec-recaptcha" data-rtec-error-message="'. esc_attr( $recaptcha_error_message ) . '">';
			$html .= '<label for="rtec_recaptcha" class="rtec_text_label">'. esc_html( $recaptcha_label ).' '.$this->recaptcha['value_1'].' &#43; '.$this->recaptcha['value_2'].'&#42;</label>';
			$html .= '<div class="rtec-input-wrapper">';
				$html .= '<input type="text" name="rtec_recaptcha_input" id="rtec_recaptcha"' . $required_data . ' />';
				$html .= esc_html( $error_html );
			$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * The html that creates the feed is broken into parts and pieced together
	 *
	 * @since 1.0
	 * @return string
	 */
    public function get_regular_fields()
    {
        $html = '<div class="rtec-form-fields-wrapper">';

        foreach ( $this->input_fields_data as $field ) {
            // check to see if there was an error and fill in
            // previous data
            $value = '';
            $type = 'text';
            $label = $field['label'];

            if ( in_array( $field['name'], $this->required_fields ) ) {
                $required_data = ' aria-required="true"';
                $label .= '&#42;';
            } else {
                $required_data = ' aria-required="false"';
            }

            $error_html = '';

            if ( in_array( $field['name'], $this->errors ) ) {
                $required_data .= ' aria-invalid="true"';
                $error_html = '<p class="rtec-error-message" role="alert">' . esc_html( $field['error_message'] ) . '</p>';
            } else {
                $required_data .= ' aria-invalid="false"';
            }

            if ( $field['name'] === 'email' ) {
                $type = 'email';
            } elseif ( $field['name'] === 'phone' ) {
	            $type = 'tel';
            }

            if ( isset( $this->submission_data['rtec_' . $field['name']] ) ) {
                $value = $this->submission_data['rtec_' . $field['name']];
            }

            $html .= '<div class="rtec-form-field rtec-'. esc_attr( $field['name'] ) . '" data-rtec-error-message="'. esc_attr( $field['error_message'] ).'"'.$field['valid_count'].'>';
                $html .= '<label for="rtec_' . esc_attr( $field['name'] ) . '" class="rtec_text_label">' . esc_html( $label ) . '</label>';
	            $html .= '<div class="rtec-input-wrapper">';
	                $html .= '<input type="' . esc_attr( $type ) . '" name="rtec_' . esc_attr( $field['name'] ) . '" value="'. esc_attr( $value ) . '" id="rtec_' . esc_attr( $field['name'] ) . '"' . $required_data . ' />';
                    $html .= $error_html;
	            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= $this->get_custom_fields_html();

        if ( ! empty( $this->recaptcha ) ) {
	        $html .= $this->get_recaptcha_html();
        }

        $html .= '</div>'; // rtec-form-fields-wrapper

	    return $html;
    }

	/**
	 * Backup in case javascript is unavailable
	 *
	 * @since 1.0
	 * @return string
	 */
    public static function get_success_message_html() {
		global $rtec_options;

        $success_html = '<p class="rtec-success-message tribe-events-notices">';
        $success_html .= rtec_get_text( $rtec_options['success_message'], __( 'Success! Please check your email inbox for a confirmation message', 'registrations-for-the-events-calendar' ) );
        $success_html .= '</p>';

	    return $success_html;
    }

	/**
	 * The html that creates the feed is broken into parts and pieced together
	 *
	 * @since 1.0
	 * @return string
	 */
    public function get_ending_html()
    {
	    global $rtec_options;

        $button_text = rtec_get_text( $rtec_options['submit_text'], __( 'Submit', 'registrations-for-the-events-calendar' ) );
	    $button_bg_color = isset( $rtec_options['button_bg_color'] ) ? esc_attr( $rtec_options['button_bg_color'] ) : '';
	    $button_styles = isset( $button_bg_color ) && ! empty( $button_bg_color ) ? 'background-color: ' . $button_bg_color . ';' : '';
	    $button_hover_class = ! empty( $button_bg_color ) ? ' rtec-custom-hover' : '';
	    $button_classes = ! empty( $button_hover_class ) ? $button_hover_class : '';
	    $html = '';
				    $html .= '<div class="rtec-form-field rtec-user-address" style="display: none;">';
				    $html .= '<label for="rtec_user_address" class="rtec_text_label">Address</label>';
					    $html .= '<div class="rtec-input-wrapper">';
						    $html .= '<input type="text" name="rtec_user_address" value="" id="rtec_user_address" />';
	                        $html .= '<p>' . __( 'If you are a human, do not fill in this field', 'registrations-for-the-events-calendar' ) .'</p>';
					    $html .= '</div>';
				    $html .= '</div>';
                    $html .= '<div class="rtec-form-buttons">';
                        $html .= '<input type="submit" class="rtec-submit-button' . $button_classes . '" name="rtec_submit" value="' . $button_text . '" style="' . $button_styles . '"/>';
                    $html .= '</div>';
                $html .= '</form>';
                $html .= '<div class="rtec-spinner">';
                    $html .= '<img title="Tribe Loading Animation Image" alt="Tribe Loading Animation Image" class="tribe-events-spinner-medium" src="' . plugins_url() . '/the-events-calendar/src/resources/images/tribe-loading.gif' . '">';
                $html .= '</div>';
            $html .= '</div>'; // rtec-form-wrapper
        $html .= '</div>'; // rtec

        return $html;
    }

	/**
	 * Assembles the html in the proper order and returns it
	 *
	 * @since 1.0
	 * @return string   complete html for the form
	 */
	public function get_form_html()
	{
		$html = '';
		$html .= $this->get_beginning_html();
		$html .= $this->get_hidden_fields_html();
		$html .= $this->get_regular_fields();
		$html .= $this->get_ending_html();

		return $html;
	}
}
RTEC_Form::instance();