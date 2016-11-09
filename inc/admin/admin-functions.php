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


function rtec_update_event_options() {
	$nonce = $_POST['rtec_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	$event_id = (int)$_POST['event_options_data'][0]['value'];
	$checkbox_fields = explode( ',' , $_POST['event_options_data']['1']['value'] );
	$meta_fields = array();
	foreach ( $checkbox_fields as $checkbox_field ) {
		$meta_fields[$checkbox_field] = 0;
	}

	foreach ( $_POST['event_options_data'] as $event_datum ) {
		if ( $event_datum['name'] !== 'rtec_checkboxes' && $event_datum['name'] !== 'rtec_event_id' ) {
			$meta_fields[$event_datum['name']] = $event_datum['value'];
		}
	}

	if ( isset( $event_id ) && is_array( $meta_fields ) ){
		//require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-db.php';

		$db = new RTEC_Db();
		$db->update_event_meta( $event_id, $meta_fields );
		echo '1';
	} else {
		var_dump( $meta_fields );
	}

	die();
}
add_action( 'wp_ajax_rtec_update_event_options', 'rtec_update_event_options' );

/**
 * Adds the meta box for the plugins individual event options
 *
 * @since 1.1
 */
function rtec_meta_boxes_init(){
	add_meta_box( 'rtec-event-details',
		'Registrations for The Events Calendar',
		'rtec_meta_boxes_html',
		'tribe_events',
		'normal',
		'high'
	);
}
add_action( 'admin_init', 'rtec_meta_boxes_init' );

/**
 * Generates the html for the plugin's individual event options meta boxes
 *
 * @since 1.1
 */
function rtec_meta_boxes_html(){
	global $post;
	$meta = get_post_meta( $post->ID, '_RTECregistrationsDisabled' );
	$meta_output = isset( $meta[0] ) ? $meta[0] : 0;
	?>
	<div id="eventDetails" class="inside eventForm">
		<table cellspacing="0" cellpadding="0" id="EventInfo">
			<tbody>
			<tr>
				<td colspan="2" class="tribe_sectionheader">
					<div class="tribe_sectionheader" style="">
						<h4><?php _e( 'Event Registration Options', 'rtec' ); ?></h4>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="eventtable">
						<tbody>
						<tr>
							<td class="tribe-table-field-label"><?php _e( 'Disable Registrations:', 'rtec' ); ?></td>
							<td>
								<input type="checkbox" id="rtec-disable-checkbox" name="_RTECregistrationsDisabled" <?php if( $meta_output == '1' ) { echo 'checked'; } ?> value="1"/>
							</td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * This saves the meta when the event post is updated
 *
 * @since 1.1
 */
function rtec_save_meta(){
	global $post;
	$registrations_disabled_status = 0;

	if ( isset( $_POST['_RTECregistrationsDisabled'] ) ){
		$registrations_disabled_status = $_POST['_RTECregistrationsDisabled'];
	}

	if ( isset( $post->ID ) ) {
		update_post_meta( $post->ID, '_RTECregistrationsDisabled', $registrations_disabled_status );
	}
}
add_action( 'save_post', 'rtec_save_meta' );

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

/**
 * Add phone column if custom table does not have it
 *
 * @since 1.1
 */
function rtec_db_update_check() {
	$db_ver = get_option( 'rtec_db_version', 0 );
	if ( $db_ver < 1.1 ) {
		update_option( 'rtec_db_version', RTEC_DBVERSION );

		$db = new RTEC_Db_Admin();
		$db->maybe_add_column_to_table( 'phone' );
	}
}
add_action( 'plugins_loaded', 'rtec_db_update_check' );