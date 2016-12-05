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
 * @since 1.3 changed so only the current event's count is recalculated
 */
function rtec_delete_registrations()
{
	$nonce = $_POST['rtec_nonce'];
	$id = $_POST['rtec_event_id'];

	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	$registrations_to_be_deleted = array();

	foreach ( $_POST['registrations_to_be_deleted'] as $registration ) {
		$registrations_to_be_deleted[] = sanitize_text_field( $registration );
	}

	$db = new RTEC_Db_Admin();

	$db->remove_records( $registrations_to_be_deleted );

	$reg_count = $db->get_registration_count( $id );

	update_post_meta( $id, '_RTECnumRegistered', $reg_count );

	die();
}
add_action( 'wp_ajax_rtec_delete_registrations', 'rtec_delete_registrations' );

/**
 * Used to manually add a registration from the dashboard
 * 
 * @since 1.0
 * @since 1.3 changed so only the current event's count is recalculated
 */
function rtec_add_registration()
{
	$nonce = $_POST['rtec_nonce'];
	$id = $_POST['rtec_event_id'];

	if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	$data = array();

	foreach( $_POST as $key => $value ) {
		if ( $key === 'rtec_custom' ) {
			$data[$key] = json_decode( str_replace( '\"', '"', sanitize_text_field( $_POST['rtec_custom'] ) ), true );
		} else {
			$data[$key] = sanitize_text_field( $value );
		}
	}

	if ( ( time() - strtotime( $data['rtec_end_time'] ) ) > 0 ) {
		$data['rtec_status'] = 'p';
	} else {
		$data['rtec_status'] = 'c';
	}
	
	$new_reg = new RTEC_Db_Admin();
	$new_reg->insert_entry( $data, false );

	$reg_count = $new_reg->get_registration_count( $id );
	update_post_meta( $id, '_RTECnumRegistered', $reg_count );

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

	$custom_data = json_decode( str_replace( '\"', '"', sanitize_text_field( $_POST['rtec_custom'] ) ), true );
	$data = array();

	foreach( $_POST as $key => $value ) {
		$data[$key] = esc_sql( $value );
	}
	
	$edit_reg = new RTEC_Db_Admin();
	$edit_reg->update_entry( $data, $custom_data );

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

function rtec_event_csv() {
	if ( isset( $_POST['rtec_event_csv'] ) && current_user_can( 'edit_posts' ) ) {

		$nonce = $_POST['rtec_csv_export_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'rtec_csv_export' ) ) {
			die ( 'You did not do this the right way!' );
		}
		global $rtec_options;

		$db = new RTEC_Db_Admin();
		$id = (int)$_POST['rtec_id'];

		$data = array(
			'fields' => 'last_name, first_name, email, phone, other',
			'id' => $id,
			'order_by' => 'registration_date'
		);

		$registrations = $db->retrieve_entries( $data );

		$meta = get_post_meta( $id );

		$event_meta['post_id'] = $id;
		$event_meta['title'] = get_the_title( $id );
		$event_meta['start_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventStartDate'][0] ) );
		$event_meta['end_date'] = date_i18n( 'F jS, g:i a', strtotime( $meta['_EventEndDate'][0] ) );
		$venue = rtec_get_venue( $id );
		$last_label = isset( $rtec_options['last_label'] ) ? esc_html( $rtec_options['last_label'] ) : __( 'Last', 'rtec' );
		$first_label = isset( $rtec_options['first_label'] ) ? esc_html( $rtec_options['first_label'] ) : __( 'First', 'rtec' );
		$email_label = isset( $rtec_options['email_label'] ) ? esc_html( $rtec_options['email_label'] ) : __( 'Email', 'rtec' );
		$phone_label = isset( $rtec_options['phone_label'] ) ? esc_html( $rtec_options['phone_label'] ) : __( 'Phone', 'rtec' );
		$other_label = isset( $rtec_options['other_label'] ) ? esc_html( $rtec_options['other_label'] ) : __( 'Other', 'rtec' );

		$event_meta_string = array(
			array( $event_meta['title'] ) ,
			array( $event_meta['start_date'] ) ,
			array( $event_meta['end_date'] ) ,
			array( $venue ),
			array( $last_label, $first_label, $email_label, $phone_label, $other_label )
		);

		$file_name = str_replace( ' ', '-', substr( $event_meta['title'], 0, 10 ) ) . date_i18n( 'm.d', strtotime( $meta['_EventStartDate'][0] ) ) . '-' . time();

		// output headers so that the file is downloaded rather than displayed
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $file_name . '.csv' );

		// create a file pointer connected to the output stream
		$output = fopen( 'php://output', 'w' );
		foreach ( $event_meta_string as $meta ) {
			if ( function_exists( 'mb_convert_variables' ) ) {
				mb_convert_variables( 'UTF-8', 'UTF-8', $meta );
			}
			fputcsv( $output, $meta );
		}

		foreach ( $registrations as $fields ) {
			if ( function_exists( 'mb_convert_variables' ) ) {
				mb_convert_variables( 'UTF-8', 'UTF-8', $fields );
			}
			fputcsv( $output, $fields );
		}

		fclose( $output );

		die();
	}
}
add_action( 'admin_init', 'rtec_event_csv' );

function rtec_get_event_columns( $full = false ) {
	global $rtec_options;

	$first_label = isset( $rtec_options['first_label'] ) ? esc_html( $rtec_options['first_label'] ) : __( 'First', 'rtec' );
	$last_label = isset( $rtec_options['last_label'] ) ? esc_html( $rtec_options['last_label'] ) : __( 'Last', 'rtec' );
	$email_label = isset( $rtec_options['email_label'] ) ? esc_html( $rtec_options['email_label'] ) : __( 'Email', 'rtec' );
	$phone_label = isset( $rtec_options['phone_label'] ) ? esc_html( $rtec_options['phone_label'] ) : __( 'Phone', 'rtec' );
	$other_label = isset( $rtec_options['other_label'] ) ? esc_html( $rtec_options['other_label'] ) : __( 'Other', 'rtec' );

	$labels = array( $last_label, $first_label, $email_label, $phone_label, $other_label );

	if ( ! $full ) {
		// add custom labels
		if ( isset( $rtec_options['custom_field_names'] ) ) {
			$custom_field_names = explode( ',', $rtec_options['custom_field_names'] );
		} else {
			$custom_field_names = array();
		}

		foreach ( $custom_field_names as $field ) {
			$labels[] = $rtec_options[$field . '_label'];
		}
	} else {
		$labels[] = 'custom';
	}


	return $labels;
}

function rtec_get_current_columns( $num_columns ) {
	global $rtec_options;

	$standard_columns = array( 'last', 'last_name', 'first', 'first_name', 'email', 'phone', 'other' );

	// add custom labels
	if ( isset( $rtec_options['custom_field_names'] ) ) {
		$custom_columns = explode( ',', $rtec_options['custom_field_names'] );
	} else {
		$custom_columns = array();
	}

	$columns = array_merge( $standard_columns, $custom_columns );

	$needed_column_names = array();
	$i = 0;
	while( isset( $columns[$i] ) && ( count( $needed_column_names ) < $num_columns ) ) {
		if ( isset( $rtec_options[$columns[$i].'_show'] ) && ( $rtec_options[$columns[$i].'_show'] !== false ) ) {
			if ( $columns[$i] === 'first' || $columns[$i] === 'last' ){
				$needed_column_names[$columns[$i].'_name'] = $rtec_options[$columns[$i].'_label'];
			} else {
				$needed_column_names[$columns[$i]] = $rtec_options[$columns[$i].'_label'];
			}
		}
		$i++;
	}

	return $needed_column_names;
}

function rtec_get_parsed_custom_field_data( $raw_data ) {
	global $rtec_options;

	$custom_data = maybe_unserialize( $raw_data );

	if ( isset( $rtec_options['custom_field_names'] ) ) {
		$custom_field_names = explode( ',', $rtec_options['custom_field_names'] );
	} else {
		$custom_field_names = array();
	}

	$parsed_data = array();
	foreach ( $custom_field_names as $field ) {

		if ( isset( $custom_data[$rtec_options[$field . '_label']] ) ) {
			$parsed_data[$rtec_options[$field . '_label']] = $custom_data[$rtec_options[$field . '_label']];
		} else {
			$parsed_data[$rtec_options[$field . '_label']] = '';
		}

	}

	return $parsed_data;
}

/**
 * Check db version and update if necessary
 *
 * @since 1.1   added check and add for "phone" column
 * @since 1.3   added check and add for index on event_id and add "custom" column,
 *              raise character limit for "other" column
 */
function rtec_db_update_check() {
	$db_ver = get_option( 'rtec_db_version', 0 );

	// adds "phone" column to database
	if ( $db_ver < 1.1 ) {
		update_option( 'rtec_db_version', RTEC_DBVERSION );

		$db = new RTEC_Db_Admin();
		$db->maybe_add_column_to_table( 'phone' );
	}

	// adds "custom" column
	if ( $db_ver < 1.2 ) {
		update_option( 'rtec_db_version', RTEC_DBVERSION );

		$db = new RTEC_Db_Admin();
		$db->maybe_add_index( 'event_id', 'event_id' );
		$db->maybe_add_column_to_table( 'custom' );
		$db->maybe_update_column( "VARCHAR(1000) NOT NULL", 'other' );
	}

}
add_action( 'plugins_loaded', 'rtec_db_update_check' );

