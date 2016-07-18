<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 *
 */
class RTEC_Db
{

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
	 * @return $instance
	 */
	static public function instance() {
		if ( !self::$instance ) {
			self::$instance = new RTEC_Db();
		}
		return self::$instance;
	}

	public function insert_entry( $data )
	{
		global $wpdb;
		$now = date( "Y-m-d H:i:s" );
		$event_id = isset( $data['rtec_event_id'] ) ? $data['rtec_event_id'] : '';
		$registration_date = isset( $data['rtec_entry_date'] ) ? $data['rtec_entry_date'] : $now;
		$last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
		$first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
		$email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
		$venue = isset( $data['rtec_venue_title'] ) ? $data['rtec_venue_title'] : '';
		$other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';
		$status = isset( $data['rtec_status'] ) ? $data['rtec_status'] : 'n';
		$wpdb->query( $wpdb->prepare( "INSERT INTO $this->table_name
          ( event_id, registration_date, last_name, first_name, email, venue, other, status ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )",
			$event_id, $registration_date, $last, $first, $email, $venue, $other, $status ) );
	}

	public function update_num_registered_meta( $id, $num )
	{
		update_post_meta( $id, '_RTECnumRegistered', (int)$num );
	}
}
RTEC_Db::instance();