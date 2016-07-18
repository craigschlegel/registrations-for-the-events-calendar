<?php
function rtec_activate() {
	
	RTEC_Db_Admin::create_table();

	$options = get_option( 'rtec_options' );

	if ( empty( $options  ) ) {
		$defaults = array(
			'first_show' => true,
			'first_require' => true,
			'first_error' => 'Please enter your first name',
			'last_show' => true,
			'last_require' => true,
			'last_error' => 'Please enter your last name',
			'email_show' => true,
			'email_require' => false,
			'email_error' => 'Please enter a valid email address',
			'other_show' => false,
			'other_require' => false,
			'other_error' => 'There is an error with your entry'
		);

		// get form options from the db
		update_option( 'rtec_options', $defaults );
	}

	$db = new RTEC_Db_Admin();

	$ids = $db->get_event_post_ids();

	foreach ( $ids as $id ) {
		$reg_count = $db->get_registration_count( $id );
		update_post_meta( $id, '_RTECnumRegistered', $reg_count );
	}
}
register_activation_hook( __FILE__, 'rtec_activate' );