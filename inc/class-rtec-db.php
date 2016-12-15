<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Base class for accessing the database and custom table
 */
class RTEC_Db
{
	/**
	 * @var RTEC_Db
	 *
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * @var string RTEC database table name
	 */
	protected $table_name;

	/**
	 * Construct the necessary data needed to make queries
	 *
	 * Including the WordPress database object and the table name for
	 * registrations is needed to add registrations to the database
	 */
	public function __construct()
	{
		global $wpdb;

		$this->table_name = $wpdb->prefix . RTEC_TABLENAME;
	}

	/**
	 * Get the one true instance of EDD_Register_Meta.
	 *
	 * @since  1.0
	 * @return object $instance
	 */
	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new RTEC_Db();
		}

		return self::$instance;
	}

	/**
	 * Add a new entry to the custom registrations table
	 *
	 * @since 1.0
	 * @param $data
	 */
	public function insert_entry( $data, $from_form = true )
	{
		global $wpdb;

		$now = date( "Y-m-d H:i:s" );
		$event_id = isset( $data['rtec_event_id'] ) ? $data['rtec_event_id'] : '';
		$registration_date = isset( $data['rtec_entry_date'] ) ? $data['rtec_entry_date'] : $now;
		$last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
		$first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
		$email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
		$venue = isset( $data['rtec_venue_title'] ) ? $data['rtec_venue_title'] : '';
		$phone = isset( $data['rtec_phone'] ) ? preg_replace( '/[^0-9]/', '', $data['rtec_phone'] ) : '';
		$other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';
		$custom = rtec_serialize_custom_data( $data, $from_form );
		$status = isset( $data['rtec_status'] ) ? $data['rtec_status'] : 'n';
		$wpdb->query( $wpdb->prepare( "INSERT INTO $this->table_name
          ( event_id, registration_date, last_name, first_name, email, venue, phone, other, custom, status ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
			$event_id, $registration_date, $last, $first, $email, $venue, $phone, $other, $custom, $status ) );
	}

	/**
	 * Update the number of registrations in event meta directly
	 *
	 * @param int $id
	 * @param int $num
	 * @since 1.0
	 */
	public function update_num_registered_meta( $id, $current, $num )
	{
		$new = (int)$current + (int)$num;
		update_post_meta( $id, '_RTECnumRegistered', $new );
	}

	/**
	 * Update event meta
	 *
	 * @param int $id
	 * @param array $key_value_meta
	 * @since 1.1
	 */
	public function update_event_meta( $id, $key_value_meta )
	{
		foreach ( $key_value_meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
	}
}
RTEC_Db::instance();