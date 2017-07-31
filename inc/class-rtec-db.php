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
	public function insert_entry( $data, $field_attributes, $from_form = true )
	{
		global $wpdb;

		$now = date( "Y-m-d H:i:s" );
		$event_id = isset( $data['event_id'] ) ? $data['event_id'] : '';
		$registration_date = isset( $data['entry_date'] ) ? $data['entry_date'] : $now;

		if ( isset( $data['last'] ) ) {
			$last = str_replace( "'", '`', $data['last'] );
		} else {
			$last = isset( $data['last_name'] ) ? str_replace( "'", '`', $data['last_name'] ) : '';
		}

		if ( isset( $data['first'] ) ) {
			$first = str_replace( "'", '`', $data['first'] );
		} else {
			$first = isset( $data['first_name'] ) ? str_replace( "'", '`', $data['first_name'] ) : '';
		}

		$email = isset( $data['email'] ) ? $data['email'] : '';
		$venue = isset( $data['venue'] ) ? $data['venue'] : '';
		$phone = isset( $data['phone'] ) ? preg_replace( '/[^0-9]/', '', $data['phone'] ) : '';
		$other = isset( $data['other'] ) ? str_replace( "'", '`', $data['other'] ) : '';
		$custom = rtec_serialize_custom_data( $data, $field_attributes, $from_form );
		$status = isset( $data['status'] ) ? $data['status'] : 'n';
		$wpdb->query( $wpdb->prepare( "INSERT INTO $this->table_name
          ( event_id, registration_date, last_name, first_name, email, venue, phone, other, custom, status ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
			$event_id, $registration_date, $last, $first, $email, $venue, $phone, $other, $custom, $status ) );

	}

	/**
	 * Get a hard count of the number of registrations currently
	 * in the database for the give id
	 *
	 * @param $event_id int   post ID for the event
	 *
	 * @return int      number registered
	 * @since 1.0
	 */
	public function get_registration_count( $event_id, $form_id = 1 )
	{
		global $wpdb;

		$num_registered = $wpdb->get_results( $wpdb->prepare( "SELECT event_id, COUNT(*) AS num_registered
            FROM $this->table_name WHERE event_id = %d", $event_id ), ARRAY_A );

		$count = isset( $num_registered[0] ) ? $num_registered[0]['num_registered'] : 0;

		$count = ! is_null( $count ) ? $count : 0;

		return $count;
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

	public function update_num_registered_meta_for_event( $event_id )
	{
		$event_meta = rtec_get_event_meta( $event_id );
		$num_registered_event = $this->get_registration_count( $event_id, $event_meta['form_id'] );

		update_post_meta( $event_id, '_RTECnumRegistered', $num_registered_event );
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

	public function retrieve_entries( $data, $full = false, $limit = 'none', $arrange = 'DESC' )
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

			if ( in_array( $field, $standard_fields, true ) ) {
				if ( $field === 'first' || $field === 'last' ) {
					$field .= '_name';
				}
				//if ( isset( $args['join']))
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

		if ( ! isset( $data['join'] ) ) {
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
		} else {

			$join_table = esc_sql( $wpdb->prefix . $data['join'][1] );
			$join_type = esc_sql( $data['join'][0] );
			$join_on = "$join_table.".esc_sql( $data['join'][2] ). " = $this->table_name.".esc_sql( $data['join'][2] );
			$sql = sprintf(
				"
                SELECT %s
                FROM %s
                $join_type JOIN $join_table ON $join_on
                ORDER BY %s DESC%s;
                ",
				esc_sql( $fields ),
				esc_sql( $this->table_name ),
				esc_sql( $order_by ),
				esc_sql( $limit_string )
			);
		}

		$results = $wpdb->get_results( $sql, $type );

		if ( $custom_flag > 0 && isset( $results[0] ) ) {
			$i = 0;

			$form = new RTEC_Form();

			$event_id = isset( $results[0]['event_id'] ) ? (int)$results[0]['event_id'] : (int)$_GET['id'];

			$form->build_form( $event_id );
			$fields_atts = $form->get_field_attributes();

			foreach ( $results as $result ) {

				if ( isset( $result['custom'] ) ) {
					if ( rtec_has_deprecated_data_structure( maybe_unserialize( $result['custom'] ) ) ) {
						$results[$i]['custom'] = rtec_get_parsed_custom_field_data( $result['custom'], $fields_atts );
					} else {
						$results[$i]['custom'] = rtec_get_parsed_custom_field_data_full_structure( $result['custom'] );
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