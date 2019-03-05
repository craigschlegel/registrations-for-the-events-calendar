<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * global function for hooks to generate the form
 *
 * @since 1.0
 */
function rtec_the_registration_form( $atts = array() )
{
	if ( tribe_is_event() && is_single() ) {
		rtec_action_check_after_post();
	}

	$rtec = RTEC();
	global $rtec_options;
	$form = $rtec->form->instance();
	$db = $rtec->db_frontend->instance();

	$doing_shortcode = isset( $atts['doing_shortcode'] ) ? $atts['doing_shortcode'] : false;
	$should_return_html_not_echo = isset( $atts['return_html'] ) ? $atts['return_html'] : false;

	$return_html = '';

	if ( $doing_shortcode ) {
		$event_id = isset( $atts['event'] ) ? (int)$atts['event'] : '';
		$return_html = '';
		$rtec_options['visitors_can_edit_what_status'] = false;
	} else {
		$event_id = get_the_ID();
	}

	$form->build_form( $event_id );
	$fields_atts = $form->get_field_attributes();
	$event_meta = $form->get_event_meta();

	$args = array(
		'event_meta' => $event_meta,
		'user' => array()
	);
	do_action( 'rtec_before_display_form', $args );

	if ( $doing_shortcode ) {
		$return_html .= isset( $atts['showheader'] ) && $atts['showheader'] === 'true' ? $form->get_event_header_html() : '';
	}

	$form->set_display_type( $atts );

	$shortcode_attendee_disable = isset( $atts['attendeelist'] ) ? ($atts['attendeelist'] !== 'true') : true;

	if ( $event_meta['show_registrants_data'] && ( ! $doing_shortcode || ! $shortcode_attendee_disable ) && ! $form->registrations_are_disabled() ) {

		$attendee_list_fields = array();
		$attendee_list_fields = apply_filters( 'rtec_attendee_list_fields', $attendee_list_fields );

		$registrants_data = $db->get_registrants_data( $event_meta, $attendee_list_fields );
		ob_start();
		do_action( 'rtec_the_attendee_list', $registrants_data );
		$attendee_html = ob_get_contents();
		ob_get_clean();

		if ( $doing_shortcode || $should_return_html_not_echo ) {
			$return_html .= $attendee_html;
		} else {
		    echo $attendee_html;
        }
	}

	if ( $rtec->submission != NULL && $event_meta['post_id'] === (int)$_POST['rtec_event_id'] ) {

		$submission = $rtec->submission->instance();
		$submission->set_field_attributes( $fields_atts );
		$submission->custom_columns = $form->get_custom_column_keys();

		$raw_data = $submission->validate_input( $_POST );

		if ( $submission->has_errors() ) {
			$form->set_errors( $submission->get_errors() );
			$form->set_submission_data( $raw_data );
			$form->set_max_registrations();

			if ( $doing_shortcode || $should_return_html_not_echo ) {
				$return_html .= $form->get_form_html( $fields_atts );
				return $return_html;
			} else {
				echo $form->get_form_html( $fields_atts );
			}
		} else {
			$submission->custom_fields_label_name_pairs = $form->get_custom_fields_label_name_pairs();
			$submission->process_valid_submission( $raw_data );

			$message = $form->get_success_message_html();
			if ( $doing_shortcode || $should_return_html_not_echo ) {
				$return_html .= $message;
				return $return_html;
			} else {
				echo $message;
			}
		}

	} elseif ( ! $form->registrations_are_disabled() ) {

		if ( $form->registrations_available() && ! $form->registration_deadline_has_passed() ) {
			$form->set_max_registrations();

			if ( $doing_shortcode || $should_return_html_not_echo ) {
				$return_html .= $form->get_form_html( $fields_atts );

				return $return_html;
			} else {
				echo $form->get_form_html( $fields_atts );
			}

		} else {
			$return_html .= '<div id="rtec" class="rtec">';

			$return_html .= $form->registrations_closed_message();

			if ( ! $form->registration_deadline_has_passed() ) {
				ob_start();

				$form->already_registered_visitor_html();

				$return_html .= ob_get_contents();
				ob_get_clean();
            }

			$return_html .= '</div>';

			if ( $doing_shortcode === true || $should_return_html_not_echo ) {
				return $return_html;
			} else {
				echo $return_html;
			}

		}

	}
}

/**
 * add element to the page to set a flag that form needs to be moved with JavaScript
 *
 * @since 2.4
 */
function rtec_the_move_flag()
{
	global $rtec_options;
	$location = isset( $rtec_options['template_location'] ) ? $rtec_options['template_location'] : 'tribe_events_single_event_before_the_content';
	$using_custom_template = isset( $rtec_options['using_custom_template'] ) ? $rtec_options['using_custom_template'] : false;

	if ( $location !== 'shortcode' && class_exists( 'Tribe__Editor__Blocks__Abstract' ) && tribe_is_event() && is_single() && ! $using_custom_template ) {
		echo '<span style="display:none;" id="rtec-js-move-flag" data-location="'.$location.'"></span>';
	}
}
add_action( 'tribe_events_single_event_meta_primary_section_end', 'rtec_the_move_flag' );

/**
 * To separate concerns and avoid potential problems with redirects, this function performs
 * a check to see if the registrationsTEC form was submitted and initiates form
 * before the template is loaded.
 *
 * @since 1.0
 */
function rtec_process_form_submission()
{
	require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-submission.php';
	require_once RTEC_PLUGIN_DIR . 'inc/form/class-rtec-form.php';

	if ( isset( $_POST['lang'] ) && ! empty( $GLOBALS['sitepress'] ) && $GLOBALS['sitepress'] instanceof SitePress ) {
	    $lang = sanitize_text_field( $_POST['lang'] );
		global $sitepress;
		$sitepress->switch_lang( $lang, true );
	}

	$submission = new RTEC_Submission();
	$form = new RTEC_Form();

	$event_id = (int)$_POST['rtec_event_id'];

	$form->build_form( $event_id );
	$fields_atts = $form->get_field_attributes();

	$event_meta = $form->get_event_meta();

		if ( $submission->attendance_limit_not_reached( $event_meta ) ) {
			$submission->set_field_attributes( $fields_atts );
			$raw_data = $submission->validate_input( $_POST );

			if ( $submission->has_errors() ) {
				echo 'form';
			} else {
				$status = $submission->process_valid_submission( $raw_data );

				echo $status;
			}

		} else {
			echo 'full';
		}

	die();
}
add_action( 'wp_ajax_nopriv_rtec_process_form_submission', 'rtec_process_form_submission' );
add_action( 'wp_ajax_rtec_process_form_submission', 'rtec_process_form_submission' );

/**
 * Checks for duplicate emails if the option is enabled
 *
 * @since 1.6
 */
function rtec_registrant_check_for_duplicate_email() {
	require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-db.php';

	$email = is_email( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : false;
	$event_id = (int)$_POST['event_id'];

	$is_duplicate = 'not';

	if ( false !== $email ) {
		$db = New RTEC_Db();
		$is_duplicate = $db->check_for_duplicate_email( $email, $event_id );
	}

	if ( $is_duplicate == '1' ) {
		$options = get_option( 'rtec_options' );

		$message = isset( $options['error_duplicate_message'] ) ? $options['error_duplicate_message'] : 'You have already registered for this event';
		$message_text = rtec_get_text( $message, __( 'You have already registered for this event', 'registrations-for-the-events-calendar' ) );

		echo '<p class="rtec-error-message" id="rtec-error-duplicate" role="alert">' . esc_html( $message_text ) . '</p>';
	} else {
		echo $is_duplicate;
	}

	die();
}
add_action( 'wp_ajax_nopriv_rtec_registrant_check_for_duplicate_email', 'rtec_registrant_check_for_duplicate_email' );
add_action( 'wp_ajax_rtec_registrant_check_for_duplicate_email', 'rtec_registrant_check_for_duplicate_email' );

/**
 * Set the form location right away
 *
 * @since 1.0
 *
 * @since 2.4   logic added for The Events Calendar 4.7
 */
function rtec_form_location_init()
{
	$options = get_option( 'rtec_options' );
	$location = isset( $options['template_location'] ) ? $options['template_location'] : 'tribe_events_single_event_before_the_content';
	$using_custom_template = isset( $options['using_custom_template'] ) ? $options['using_custom_template'] : false;

	if ( $using_custom_template ) {
		if ( $location !== 'shortcode' ) {
			add_action( $location, 'rtec_the_registration_form' );
		}
	} elseif ( ! class_exists( 'Tribe__Editor__Blocks__Abstract' ) ) {
		if ( $location !== 'shortcode' ) {
			add_action( $location, 'rtec_the_registration_form' );
		}
	}

}
add_action( 'init', 'rtec_form_location_init', 1 );

/**
 *
 * @since 2.2
 */
function rtec_add_visitor_action_listener() {
	if ( isset( $_POST['rtec_visitor_submit'] ) ) {
		rtec_visitor_send_action_link();
	}
}
add_action( 'init', 'rtec_add_visitor_action_listener', 99 );

function rtec_check_action_before_post() {

	if ( ! is_admin() && isset( $_GET['action'] ) && $_GET['action'] === 'unregister' ) {
		global $rtec_options;
		$rtec = RTEC();
		$action = sanitize_text_field( $_GET['action'] );

        $verification_data = array(
            'email' => isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '',
            'token' => isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '',
            'action' => sanitize_text_field( $action )
        );

        $entry_exists = $rtec->db_frontend->maybe_verify_token( $verification_data );

        if ( $verification_data['action'] === 'unregister' && $entry_exists && $verification_data['token'] !== '' ) {

            $message = isset( $rtec_options['success_unregistration'] ) ? $rtec_options['success_unregistration'] : __( 'You have been unregistered.', 'registrations-for-the-events-calendar' );
            $o_message = rtec_get_text( $message, __( 'You have been unregistered.', 'registrations-for-the-events-calendar' ) );

            if ( method_exists ( 'Tribe__Notices' , 'set_notice' ) ) {
                Tribe__Notices::set_notice( 'unregistered', $o_message );
            }

        } else {

	        if ( method_exists ( 'Tribe__Notices' , 'set_notice' ) ) {
		        Tribe__Notices::set_notice( 'unregistered', __( 'No record found.', 'registrations-for-the-events-calendar' ) );
	        }
        }

    }

}
add_action( 'init', 'rtec_check_action_before_post' );

/**
 * Set the form location right away
 *
 * @since 2.4.3
 */
function rtec_use_footer_to_add_form() {
	global $rtec_options;
	$location = isset( $rtec_options['template_location'] ) ? $rtec_options['template_location'] : 'tribe_events_single_event_before_the_content';
	$using_custom_template = isset( $rtec_options['using_custom_template'] ) ? $rtec_options['using_custom_template'] : false;
	if ( $location !== 'shortcode' && class_exists( 'Tribe__Editor__Blocks__Abstract' ) && tribe_is_event() && is_single() && ! $using_custom_template ) {
		rtec_the_registration_form();
	}
}
add_action( 'wp_footer', 'rtec_use_footer_to_add_form', 1 );


function rtec_action_check_after_post() {
	if ( ! is_admin() && isset( $_GET['action'] ) && $_GET['action'] === 'unregister' ) {
		global $rtec_options;
		$rtec = RTEC();
		$action = sanitize_text_field( $_GET['action'] );

        $verification_data = array(
            'email' => isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '',
            'token' => isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '',
            'action' => $action
        );

		$entry_exists = $rtec->db_frontend->maybe_verify_token( $verification_data );

		if ( $verification_data['action'] === 'unregister' && $entry_exists && $verification_data['token'] !== '' ) {

            $event_id = get_the_ID();
            $record_was_deleted = $rtec->db_frontend->remove_record_by_action_key( $verification_data['token'] );

            if ( $record_was_deleted ) {
                $rtec->db_frontend->update_num_registered_meta_for_event( $event_id );
                $disable_notification = isset( $rtec_options['disable_notification'] ) ? $rtec_options['disable_notification'] : false;

                if ( ! $disable_notification ) {

                    require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-email.php';
                    $notification_message = new RTEC_Email();

                    $recipients = rtec_get_notification_email_recipients( $event_id );
                    $email = isset( $verification_data['email'] ) ? '(' . $verification_data['email'] . ')' : '';
                    $message                 = sprintf( __( 'A registrant %s has unregistered from this event %s.', 'registrations-for-the-events-calendar' ), $email, get_the_permalink() );
                    $args                    = array(
                        'template_type' => 'notification',
                        'content_type'  => 'plain',
                        'recipients'    => $recipients,
                        'subject'       => array(
                            'text' => __( 'Notification of Unregistration', 'registrations-for-the-events-calendar' ),
                            'data' => array()
                        ),
                        'body'          => array(
                            'message' => $message,
                            'data'    => array()
                        )
                    );
                    $notification_message->build_email( $args );
                    $success = $notification_message->send_email();
                }

            }

        }

	}
}

/**
 *
 * @since 2.2
 */
function rtec_visitor_send_action_link() {
	global $rtec_options;

	if ( ! is_email( $_POST['rtec-visitor_email'] ) || ! wp_verify_nonce( $_POST['rtec_nonce'], 'rtec_visitor_submit' ) ) {

		if ( method_exists ( 'Tribe__Notices' , 'set_notice' ) ) {
			Tribe__Notices::set_notice( 'tool_status', __( 'Please enter the email you registered with.', 'registrations-for-the-events-calendar' ) );
		}

	} else {
        $email = sanitize_text_field( $_POST['rtec-visitor_email'] );
        $event_id = (int)$_POST['event_id'];
        $rtec = RTEC();
        $args = array(
            'fields' => array( 'event_id', 'action_key' ),
            'where' => array(
                array( 'email', $email, '=', 'string' ),
                array( 'event_id', $event_id, '=', 'int' ),
            )
        );
        $matches = $rtec->db_frontend->retrieve_entries( $args, false, 1 );

        if ( isset( $matches[0]['action_key'] ) ) {

            $unregister_link_text = isset( $rtec_options['unregister_link_text'] ) ? esc_html( $rtec_options['unregister_link_text'] ) : __( 'Unregister from this event', 'registrations-for-the-events-calendar' );
            $unregister_link_text = rtec_get_text( $unregister_link_text, __( 'Unregister from this event', 'registrations-for-the-events-calendar' ) );

            $message = rtec_generate_unregister_link( (int)$event_id, $matches[0]['action_key'], $email, $unregister_link_text );
            $header_image = isset( $rtec_options['html_email_header_img'] ) ? $rtec_options['html_email_header_img'] : false;

            $args = array(
                'template_type'         => 'confirmation',
                'content_type'          => 'html',
                'custom_template_pairs' => array(),
                'recipients'            => $email,
                'subject'               => array(
                    'text' => get_the_title( $event_id ),
                    'data' => array()
                ),
                'body'                  => array(
                    'message'      => $message,
                    'data'         => array(),
                    'header_image' => $header_image
                )
            );
            require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-email.php';
            $unregister_email = new RTEC_Email();
            $unregister_email->build_email( $args, true, $event_id );

            $success = $unregister_email->send_email();

            if ( $success && method_exists ( 'Tribe__Notices' , 'set_notice' ) ) {
                Tribe__Notices::set_notice( 'tool_status', __( 'Check your email inbox for an unregister link.', 'registrations-for-the-events-calendar' ) );
            }

        } else {

            if ( method_exists ( 'Tribe__Notices' , 'set_notice' ) ) {
                Tribe__Notices::set_notice( 'tool_status', __( 'Please enter the email you registered with.', 'registrations-for-the-events-calendar' ) );
            }

        }
	}

	return '';
}

function rtec_the_default_attendee_list( $registrants_data )
{
	$rtec = RTEC();
	$form = $rtec->form->instance();

	$form->get_registrants_data_html( $registrants_data );
}
add_action( 'rtec_the_attendee_list', 'rtec_the_default_attendee_list', 10, 1 );

/**
* outputs the custom js from the "Customize" tab on the Settings page
 *
 * @since 1.0
*/
function rtec_custom_js() {
	$options = get_option( 'rtec_options' );
	$rtec_custom_js = isset( $options[ 'custom_js' ] ) ? $options[ 'custom_js' ] : '';

	if ( ! empty( $rtec_custom_js ) ) {
?>
<!-- Registrations For the Events Calendar JS -->
<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php echo stripslashes( $rtec_custom_js ) . "\r\n"; ?>
	});
</script>
<?php
	}
}
add_action( 'wp_footer', 'rtec_custom_js', 50 );

/**
 * outputs the custom css from the "Customize" tab on the Settings page
 *
 * @since 1.0
 */
function rtec_custom_css() {
	$options = get_option( 'rtec_options' );
	$rtec_custom_css = isset( $options[ 'custom_css' ] ) ? $options[ 'custom_css' ] : '';

	if ( ! empty( $rtec_custom_css ) ) {
		echo "<!-- Registrations For the Events Calendar CSS -->" . "\r\n";
		echo "<style type='text/css'>" . "\r\n";
		if ( ! empty( $rtec_custom_css ) ) {
			echo stripslashes( $rtec_custom_css ) . "\r\n";
		}
		echo "</style>" . "\r\n";
	}
}
add_action( 'wp_head', 'rtec_custom_css' );

/**
 * javascript and CSS files for the feed
 *
 * @since 1.0
 */
function rtec_scripts_and_styles() {
	wp_enqueue_style( 'rtec_styles', trailingslashit( RTEC_PLUGIN_URL ) . 'css/rtec-styles.css', array(), RTEC_VERSION );
	wp_enqueue_script( 'rtec_scripts', trailingslashit( RTEC_PLUGIN_URL ) . 'js/rtec-scripts.js', array( 'jquery' ), RTEC_VERSION, true );

	wp_register_script( 'rtec_recaptcha', 'https://www.google.com/recaptcha/api.js' );

	$options = get_option( 'rtec_options' );
	$check_for_duplicates = isset( $options['check_for_duplicates'] ) ? $options['check_for_duplicates'] : false;
	wp_localize_script( 'rtec_scripts', 'rtec', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'checkForDuplicates' => $check_for_duplicates,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rtec_scripts_and_styles' );