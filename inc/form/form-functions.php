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
	$rtec = RTEC();
	$form = $rtec->form->instance();
	$db = $rtec->db_frontend->instance();

	$doing_shortcode = isset( $atts['doing_shortcode'] ) ? $atts['doing_shortcode'] : false;
	$return_html = '';

	if ( $doing_shortcode ) {
		$event_id = isset( $atts['event'] ) ? (int)$atts['event'] : '';
		$return_html = '';
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

	if ( $event_meta['show_registrants_data'] && ! $doing_shortcode && ! $form->registrations_are_disabled() ) {

		$attendee_list_fields = array();
		$attendee_list_fields = apply_filters( 'rtec_attendee_list_fields', $attendee_list_fields );

		$registrants_data = $db->get_registrants_data( $event_meta, $attendee_list_fields );

		do_action( 'rtec_the_attendee_list', $registrants_data );
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

			if ( $doing_shortcode ) {
				$return_html .= $form->get_form_html( $fields_atts );
			} else {
				echo $form->get_form_html( $fields_atts );
			}
		} else {
			$submission->custom_fields_label_name_pairs = $form->get_custom_fields_label_name_pairs();
			$submission->process_valid_submission( $raw_data );

			$message = $form->get_success_message_html();
			if ( $doing_shortcode ) {
				$return_html .= $message;
			} else {
				echo $message;
			}
		}

	} elseif ( ! $form->registrations_are_disabled() ) {

		if ( $form->registrations_available() && ! $form->registration_deadline_has_passed() ) {
			$form->set_max_registrations();

			if ( $doing_shortcode ) {
				$return_html .= $form->get_form_html( $fields_atts );

				return $return_html;
			} else {
				echo $form->get_form_html( $fields_atts );
			}

		} else {
			$return_html .= $form->registrations_closed_message();

			if ( $doing_shortcode === true ) {
				return $return_html;
			} else {
				echo $return_html;
			}

		}

	}
}

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
 */
function rtec_form_location_init()
{
	$options = get_option( 'rtec_options' );
	$location = isset( $options['template_location'] ) ? $options['template_location'] : 'tribe_events_single_event_before_the_content';
	add_action( $location, 'rtec_the_registration_form' );
}
add_action( 'plugins_loaded', 'rtec_form_location_init', 1 );

function rtec_the_default_attendee_list( $registrants_data )
{
	$rtec = RTEC();
	$form = $rtec->form->instance();

	$form::get_registrants_data_html( $registrants_data );
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
add_action( 'wp_footer', 'rtec_custom_js' );

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

	$options = get_option( 'rtec_options' );
	$check_for_duplicates = isset( $options['check_for_duplicates'] ) ? $options['check_for_duplicates'] : false;
	wp_localize_script( 'rtec_scripts', 'rtec', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'checkForDuplicates' => $check_for_duplicates,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rtec_scripts_and_styles' );