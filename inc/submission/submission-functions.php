<?php
function rtec_process_submission() {
	$rtec = RTEC();
	$submission = $rtec->submission->instance();
	$db = $rtec->db->instance();
	$submission->sanitize_submission();
	if ( $submission->email_given() ) {
		//$confirmation_success = $submission->send_confirmation_email();
	}
	//$notification_success = $submission->send_notification_email();
	$data = $submission->get_db_data();
	$db->insert_entry( $data );
	if ( ! empty( $data['rtec_event_id'] ) ) {
		$change = 1;
		$db->update_num_registered_meta( $data['rtec_event_id'], $change );
	}
}