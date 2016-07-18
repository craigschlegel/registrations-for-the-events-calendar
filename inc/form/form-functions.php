<?php

function rtec_the_registration_form()
{
	$rtec = RTEC();
	$form = $rtec->form->instance();
	if ( $rtec->submission != NULL ) {
		$submission = $rtec->submission->instance();
		$submission->validate_data();
		if ( $submission->has_errors() ) {
			$form->set_errors( $submission->get_errors() );
			$form->set_submission_data( $submission->get_data() );
			$form->set_event_meta();
			$form->set_input_fields_data();
			$form->set_max_registrations();
			echo $form->get_form_html();
		} else {
			rtec_process_submission();
			$message = $form::get_success_message_html();
			echo $message;
		}
	} else {
		$form->set_event_meta();
		$form->set_input_fields_data();
		$form->set_max_registrations();
		echo $form->get_form_html();
	}
}
/**
 * To separate concerns and avoid potential problems with redirects, this function performs
 * a check to see if the registrationsTEC form was submitted and initiates form
 * before the template is loaded.
 */
function rtec_process_form_submission()
{
	$rtec = RTEC();
	$submission = $rtec->submission->instance();
	$submission->validate_data();
	if ( $submission->has_errors() ) {
		return false;
	} else {
		rtec_process_submission();
	}
	die();
}
add_action( 'wp_ajax_nopriv_rtec_process_form_submission', 'rtec_process_form_submission' );
add_action( 'wp_ajax_rtec_process_form_submission', 'rtec_process_form_submission' );

function rtec_form_location_init()
{
	$options = get_option( 'rtec_options' );
	$location = isset( $options['template_location'] ) ? $options['template_location'] : 'tribe_events_single_event_before_the_content';
	add_action( $location, 'rtec_the_registration_form' );
}
add_action( 'plugins_loaded', 'rtec_form_location_init', 1 );

/**
* outputs the custom js from the "Customize" tab on the Settings page
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

function rtec_scripts_and_styles() {
	wp_enqueue_style( 'rtec_styles', RTEC_PLUGIN_URL . '/css/rtec-styles.css', array(), RTEC_VERSION );
	wp_enqueue_script( 'rtec_scripts', RTEC_PLUGIN_URL . '/js/rtec-scripts.js', array( 'jquery' ), RTEC_VERSION, true );
	wp_localize_script( 'rtec_scripts', 'rtec', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' )
		)
	);
}
add_action( 'wp_enqueue_scripts', 'rtec_scripts_and_styles' );