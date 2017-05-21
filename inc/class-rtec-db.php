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
		$last = isset( $data['rtec_last'] ) ? str_replace( "'", '`', $data['rtec_last'] ) : '';
		$first = isset( $data['rtec_first'] ) ? str_replace( "'", '`', $data['rtec_first'] ) : '';
		$email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
		$venue = isset( $data['rtec_venue_title'] ) ? $data['rtec_venue_title'] : '';
		$phone = isset( $data['rtec_phone'] ) ? preg_replace( '/[^0-9]/', '', $data['rtec_phone'] ) : '';
		$other = isset( $data['rtec_other'] ) ? str_replace( "'", '`', $data['rtec_other'] ) : '';
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

	/**
	 * Generates the registration form with a shortcode
	 *
	 * @param   $email        string  registrants entered
	 * @param   $event_id     int     post id of the event to compare
	 *
	 * @return  bool  true if email is a duplicate
	 *
	 * @since   1.6
	 */
	function check_for_duplicate_email( $email, $event_id ) {
		global $wpdb;

		$results = $wpdb->get_row( $wpdb->prepare( "SELECT email FROM $this->table_name 
		WHERE event_id=%d AND email=%s;", $event_id, $email), ARRAY_A );

		if ( isset( $results ) ) {
			return 1;
		} else {
			return 0;
		}
	}
	/**
	 * Gets all entries that meet a set of parameters
	 *
	 * @param $data array       parameters for what entries to retrieve
	 * @param $full boolean     whether to return the full custom field
	 * @param $limit string     limit if any for registrations retrieved
	 *
	 * @return mixed bool/array false if no results, registrations if there are
	 * @since 1.0
	 * @since 1.3   expanded to work with custom fields and dynamic entries
	 * @since 1.4   added the ability to limit entries retrieved
	 * @since 1.6   moved to RTEC_Db class for use in the front-end registration display
	 */
	public function retrieve_entries( $data, $full = false, $limit = 'none' )
	{
		global $wpdb;

		$fields = $data['fields'];
		if ( ! is_array( $fields ) ) {
			$fields = explode( ',', str_replace( ' ', '', $fields ) );
		}

		$standard_fields = array( 'id', 'event_id', 'registration_date', 'last_name', 'first_name', 'last', 'first', 'email', 'venue', 'other', 'custom', 'phone', 'status' );
		$request_fields = array();
		$custom_flag = 0;

		$limit_string = $limit === 'none' ? '' : ' LIMIT ' . (int)$limit;

		foreach ( $fields as $field ) {

			if ( in_array( $field, $standard_fields ) ) {
				if ( $field === 'first' || $field === 'last' ) {
					$field .= '_name';
				}
				$request_fields[] = $field;
			} else {
				$custom_flag++;
			}

		}

		if ( $custom_flag > 0 ) {
			$request_fields[] = 'custom';
		}

		$fields = implode( ',' , $request_fields );
		$where_clause = $this->build_escaped_where_clause( $data['where'] );
		$order_by = isset( $data['order_by'] ) ? $data['order_by'] : 'last_name';
		$type = ARRAY_A;

		$sql = sprintf(
			"
            SELECT %s
            FROM %s
            WHERE $where_clause
            ORDER BY %s DESC%s;
            ",
			esc_sql( $fields ),
			esc_sql( $this->table_name ),
			esc_sql( $order_by ),
			esc_sql( $limit_string )
		);

		$results = $wpdb->get_results( $sql, $type );

		if ( $custom_flag > 0 ) {
			$i = 0;

			foreach ( $results as $result ) {

				if ( isset( $result['custom'] ) ) {
					if ( ! $full ) {
						$results[$i]['custom'] = rtec_get_parsed_custom_field_data( $result['custom'] );
					} else {
						$results[$i]['custom'] = $result['custom'];
					}
				}

				$i++;
			}

		}

		return $results;
	}

	/**
	 * retrieves registrations from the database based on what data is being shown
	 *
	 * @param   $event_meta   array  the meta for the event
	 *
	 * @return  array                associative array of all registrations that have been reviewed
	 *
	 * @since   1.6
	 */
	public function get_registrants_data( $event_meta )
	{
		$retrieve_fields = array( 'first_name', 'last_name' );
		$args = array(
			'fields' => $retrieve_fields,
			'where' => array(
				array( 'event_id', $event_meta['post_id'], '=', 'int' ),
				array( 'status', '"n"', '!=', 'string' )
			),
			'order_by' => 'registration_date'
		);

		$registrants = $this->retrieve_entries( $args, false, 300, $arrange = 'DESC' );

		if ( isset( $registrants[0] ) ) {
			return $registrants;
		} else {
			return array();
		}
	}

	/**
	 * Used to build an escaped where clause for special queries
	 *
	 * @param $where    array of arrays of settings for a where clause 'AND'
	 *
	 * @return string   escaped where clause
	 *
	 * @since 1.6
	 */
	protected function build_escaped_where_clause( $where )
	{
		$i = 1;
		$size = count( $where );
		$where_clause = '';
		if ( ! empty( $where ) ) {
			foreach ( $where as $item ) {
				if ( $item[2] === '=' ) {
					if ( $item[3] === 'string' ) {
						$where_clause .= esc_sql( $item[0] ) . ' '. esc_sql( $item[2] ) .' "' . esc_sql( $item[1] ) . '"';
					} else {
						$where_clause .= esc_sql( $item[0] ) . ' '. esc_sql( $item[2] ) .' ' . esc_sql( $item[1] );
					}
				} elseif ( $item[2] === '!=' ) {
					$where_clause .= esc_sql( $item[0] ) . ' NOT IN (' . $item[1] . ')';
				}

				if ( $size > $i ) {
					$where_clause .= ' AND ';
				}
				$i++;
			}
		}

		return $where_clause;
	}
}
RTEC_Db::instance();