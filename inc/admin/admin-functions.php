<?php
/**
 * New registrations are counted and added as alerts to the menu items
 * 
 * @return false|int    false if no new registrations, else the count
 * @since 1.0
 */
function rtec_get_existing_new_reg_count() {

	$existing_new_reg_data = get_transient( 'rtec_new_registrations' );

	if ( $existing_new_reg_data ) {
		$new_registrations_count = $existing_new_reg_data;
	} else {
		$db = new RTEC_Db_Admin();
		$new_registrations_count = $db->check_for_new();

		if ( ! $existing_new_reg_data ) {
			set_transient( 'rtec_new_registrations', $new_registrations_count, 60 * 15 );
		}

	}

	return $new_registrations_count;
}

/**
 * Creates the alert next to the menu item
 * 
 * @since 1.0
 */
function rtec_registrations_bubble() {

	$new_registrations_count = rtec_get_existing_new_reg_count();

	if ( $new_registrations_count > 0 ) {
		global $menu;

		foreach ( $menu as $key => $value ) {
			if ( $menu[$key][2] === RTEC_TRIBE_MENU_PAGE ) {
				$menu[$key][0] .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>' . $new_registrations_count . '</span></span>';
				return;
			}
		}
	} elseif ( get_transient( 'rtec_new_messages' ) === 'yes' ) {
		global $menu;

		foreach ( $menu as $key => $value ) {
			if ( $menu[$key][2] === RTEC_TRIBE_MENU_PAGE ) {
				$menu[$key][0] .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>New Plugin!</span></span>';
				return;
			}
		}
	}

}
add_action( 'admin_menu', 'rtec_registrations_bubble' );

/**
 * Used to remove registrations from the dashboard
 * 
 * @since 1.0
 */
function rtec_delete_registrations()
{
	$nonce = $_POST['rtec_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	$registrations_to_be_deleted = array();

	foreach ( $_POST['registrations_to_be_deleted'] as $registration ) {
		$registrations_to_be_deleted[] = sanitize_text_field( $registration );
	}

	$db = new RTEC_Db_Admin();

	$db->remove_records( $registrations_to_be_deleted );
	$ids = $db->get_event_post_ids();

	foreach ( $ids as $id ) {
		$reg_count = $db->get_registration_count( $id );

		update_post_meta( $id, '_RTECnumRegistered', $reg_count );
	}

	die();
}
add_action( 'wp_ajax_rtec_delete_registrations', 'rtec_delete_registrations' );

/**
 * Used to manually add a registration from the dashboard
 * 
 * @since 1.0
 */
function rtec_add_registration()
{
	$nonce = $_POST['rtec_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}
	
	$data = array();

	foreach( $_POST as $key => $value ) {
		$data[$key] = sanitize_text_field( $value );
	}

	if ( ( time() - strtotime( $data['rtec_end_time'] ) ) > 0 ) {
		$data['rtec_status'] = 'p';
	} else {
		$data['rtec_status'] = 'c';
	}
	
	$new_reg = new RTEC_Db_Admin();

	$new_reg->insert_entry( $data );

	$ids = $new_reg->get_event_post_ids();

	foreach ( $ids as $id ) {
		$reg_count = $new_reg->get_registration_count( $id );
		update_post_meta( $id, '_RTECnumRegistered', $reg_count );
	}

	die();
}
add_action( 'wp_ajax_rtec_add_registration', 'rtec_add_registration' );

/**
 * Makes alterations to existing registrations in the dashboard
 * 
 * @since 1.0
 */
function rtec_update_registration()
{
	$nonce = $_POST['rtec_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}
	
	$data = array();
	foreach( $_POST as $key => $value ) {
		$data[$key] = esc_sql( $value );
	}
	
	$edit_reg = new RTEC_Db_Admin();
	$edit_reg->update_entry( $data );

	die();
}
add_action( 'wp_ajax_rtec_update_registration', 'rtec_update_registration' );


/**
 * Some CSS and JS needed in the admin area as well
 * 
 * @since 1.0
 */
function rtec_admin_scripts_and_styles() {
	wp_enqueue_style( 'rtec_admin_styles', RTEC_PLUGIN_URL . 'css/rtec-admin-styles.css', array(), RTEC_VERSION );
	wp_enqueue_script( 'rtec_admin_scripts', RTEC_PLUGIN_URL . '/js/rtec-admin-scripts.js', array( 'jquery' ), RTEC_VERSION, false );
	wp_localize_script( 'rtec_admin_scripts', 'rtecAdminScript',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'rtec_nonce' => wp_create_nonce( 'rtec_nonce' )
		)
	);
}
add_action( 'admin_enqueue_scripts', 'rtec_admin_scripts_and_styles' );

/**
 * Add links to the plugin action links
 *
 * @since 1.0
 */
function rtec_plugin_action_links( $links ) {
	$links[] = '<a href="'. esc_url( get_admin_url( null, 'edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=form' ) ) .'">Settings</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . RTEC_PLUGIN_BASENAME, 'rtec_plugin_action_links' );

/**
 * Add links to setup and pro versions
 *
 * @since 1.0
 */
function rtec_plugin_meta_links( $links, $file ) {
	$plugin = RTEC_PLUGIN_BASENAME;
	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="https://www.roundupwp.com/products/registrations-for-the-events-calendar/setup/" target="_blank">Setup Instructions</a>', '<a href="https://www.roundupwp.com/products/registrations-for-the-events-calendar-pro/" target="_blank">Buy Pro</a>' )
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'rtec_plugin_meta_links', 10, 2 );
