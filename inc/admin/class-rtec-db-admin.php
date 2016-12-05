<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

/**
 * Class RTEC_Db_Admin
 * 
 * Contains special methods that just apply to the admin area
 * @since 1.0
 */
class RTEC_Db_Admin extends RTEC_Db
{
	/**
	 * Used to create the registrations table on activation
	 *
	 * @since 1.0
	 */
    public static function create_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . RTEC_TABLENAME;
        $charset_collate = $wpdb->get_charset_collate();

        if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
            $sql = "CREATE TABLE " . $table_name . " (
                id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                event_id SMALLINT NOT NULL,
                registration_date DATETIME NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                first_name VARCHAR(40) NOT NULL,
                email VARCHAR(60) NOT NULL,
                venue VARCHAR(100) NOT NULL,
                phone VARCHAR(40) DEFAULT '' NOT NULL,
                other VARCHAR(100) DEFAULT '' NOT NULL,
                custom LONGTEXT DEFAULT '' NOT NULL,
                status CHAR(1) DEFAULT 'y' NOT NULL,
                UNIQUE KEY id (id)
            ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            add_option( 'rtec_db_version', RTEC_DBVERSION );

        }

    }

    /**
     * Used to make changes to existing registrations
     * 
     * @param $data array           information to be updated
     * @param $custom_data array    custom data to be updated
     * @since 1.0
     */
    public function update_entry( $data, $custom_data = '' )
    {
        global $wpdb;

        $id = isset( $data['rtec_id'] ) ? $data['rtec_id'] : '';
        $last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
        $first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
        $email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
	    $phone = isset( $data['rtec_phone'] ) ? $data['rtec_phone'] : '';
	    $other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';

	    $custom = $this->get_custom_data( $id );
	    $custom = $this->update_custom_data_for_db( $custom, $custom_data );

        if ( ! empty( $id ) ) {
            $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name
                SET last_name=%s, first_name=%s, email=%s, phone=%s, other=%s, custom=%s
                WHERE id=%d",
                $last, $first, $email, $phone, $other, $custom, $id ) );
        }

    }

    public function get_custom_data( $id )
    {
    	global $wpdb;

	    $results = $wpdb->get_results( $wpdb->prepare( "SELECT custom FROM $this->table_name
                WHERE id=%d", $id ), ARRAY_A );

	    return maybe_unserialize( $results[0]['custom'] );
    }

    public function update_custom_data_for_db( $db_custom, $new_custom )
    {
		if ( ! empty( $new_custom ) ) {
			foreach ( $new_custom as $key => $value ) {
				$db_custom[$key] = $value;
			}
		}

		return maybe_serialize( $db_custom );
    }

    /**
     * Gets all entries that meet a set of parameters
     * 
     * @param $data array       parameters for what entries to retrieve
     * @param $full boolean     whether to return the full custom field
     *
     * @return mixed bool/array false if no results, registrations if there are
     * @since 1.0
     */
    public function retrieve_entries( $data, $full = false )
    {
        global $wpdb;

        $fields = $data['fields'];
	    if ( ! is_array( $fields ) ) {
	    	$fields = explode( ',', str_replace( ' ', '', $fields ) );
	    }

	    $standard_fields = array( 'id', 'event_id', 'registration_date', 'last_name', 'first_name', 'last', 'first', 'email', 'venue', 'other', 'custom', 'phone', 'status' );
	    $request_fields = array();
	    $custom_flag = 0;

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
        $id = isset( $data['id'] ) ? $data['id'] : '';
        $order_by = isset( $data['order_by'] ) ? $data['order_by'] : 'last_name';
        $type = ARRAY_A;

        if ( is_numeric( $id ) ) {
            $sql = sprintf(
                "
                SELECT %s
                FROM %s
                WHERE event_id = %d
                ORDER BY %s DESC;
                ",
                esc_sql( $fields ),
                esc_sql( $this->table_name ),
                esc_sql( $id ),
                esc_sql( $order_by )
            );
        } else {
            $sql = sprintf(
                "
                SELECT %s
                FROM %s
                ORDER BY %s DESC;
                ",
                esc_sql( $fields ),
                esc_sql( $this->table_name ),
                esc_sql( $order_by )
            );
        }

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
     * Removes a set of records from the dashboard
     * 
     * @param $records array    ids or email of records to remove
     *
     * @return bool
     * @since 1.0
     */
    public function remove_records( $records ) {
        global $wpdb;

        $where = 'id';

        if ( ( is_array( $records ) && is_email( $records[0] ) ) || is_email( $records ) ) {
            $where = 'email';
        }

        if ( is_array( $records ) ) {
            $registrations_to_be_deleted = implode( ', ', $records);
        } else {
            $registrations_to_be_deleted = $records;
        }

	    $table_name = esc_sql( $this->table_name );
	    $registrations_to_be_deleted_string = esc_sql( $registrations_to_be_deleted );

        $wpdb->query( "DELETE FROM $table_name WHERE $where IN( $registrations_to_be_deleted_string )" );

        return true;
    }

    /**
     * One a registration has been seen, status changes from (n)ew to (c)urrent
     *
     * @param array $ids    event ids to be updated
     * 
     * @return bool
     * @since 1.0
     * @since 1.1 new parameter allows for specific ids
     */
    public function update_statuses( $ids = NULL )
    {
        global $wpdb;

        $current = 'c';
        $new = 'n';
	    if ( $ids != NULL ) {
	    	$id_string = implode( ', ', $ids );
		    $query = $wpdb->prepare( "UPDATE $this->table_name SET status=%s WHERE status=%s", $current, $new );
		    $query .=  "AND event_id IN ( " . $id_string . " )";
		    $wpdb->query( $query );
	    } else {
		    $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name SET status=%s WHERE status=%s", $current, $new ) );
	    }
        delete_transient( 'rtec_new_registrations' );

        return true;
    }

    /**
     * Used to create the alert for new registrations
     * 
     * @return false|int    false if no records, otherwise number of new registrations
     * @since 1.0
     */
    public function check_for_new()
    {
        global $wpdb;

        $new = 'n';

        return $wpdb->query( $wpdb->prepare( "SELECT status
        FROM $this->table_name WHERE status=%s", $new ) );
    }

    /**
     * Get a hard count of the number of registrations currently
     * in the database for the give id
     * 
     * @param $id int   post ID for the event
     *
     * @return int      number registered
     * @since 1.0
     */
    public function get_registration_count( $id )
    {
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare( "SELECT event_id, COUNT(*) AS num_registered
        FROM $this->table_name WHERE event_id = %d", $id ), ARRAY_A );

        return $result[0]['num_registered'];
    }

    /**
     * Manually set the number of registrations
     * 
     * @param $id int   post ID
     * @param $num int  new number to set the post meta as
     * @since 1.0
     */
    public function set_num_registered_meta( $id, $num )
    {
        update_post_meta( $id, '_RTECnumRegistered', (int)$num );
    }

    /**
     * Gets all of the post IDs with the Tribe Events post type
     * 
     * @return array    the ids
     * @since 1.0
     */
    public function get_event_post_ids() 
    {
        global $wpdb;

        $query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", RTEC_TRIBE_EVENTS_POST_TYPE );
        $event_ids = $wpdb->get_col( $query );

        return $event_ids;
    }

	/**
	 * Used to update the database to accommodate new columns added since release
	 *
	 * @param $column string    name of column to add if it doesn't exist
	 * @since 1.1
	 */
    public function maybe_add_column_to_table( $column )
    {
	    global $wpdb;

	    $table_name = esc_sql( $this->table_name );
	    $column_name = esc_sql( $column );

	    $results = $wpdb->query( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = '$column_name'" );

	    if ( $results == 0 ){
		    $wpdb->query( "ALTER TABLE $table_name ADD $column_name VARCHAR(40) DEFAULT '' NOT NULL" );
	    }
    }

	/**
	 * Used to add indices to registrations table
	 *
	 * @param $index string    name of index to add if it doesn't exist
	 * @param $column string        name of column to add index to
	 * @since 1.3
	 */
    public function maybe_add_index( $index, $column )
    {
	    global $wpdb;

	    $table_name = esc_sql( $this->table_name );
	    $column_name = esc_sql( $column );
	    $index_name = esc_sql( $index );

	    $results = $wpdb->get_results( "SELECT COUNT(1) indexExists FROM INFORMATION_SCHEMA.STATISTICS 
			WHERE table_schema=DATABASE() AND table_name = '$table_name' AND index_name = '$index_name'" );

	    if ( $results[0]->indexExists == '0' ){
		    $wpdb->query( "ALTER TABLE $table_name ADD INDEX $index_name ($column_name)" );
	    }
    }

	/**
	 * Used to add indices to registrations table
	 *
	 * @param $edit string    name of index to add if it doesn't exist
	 * @param $column string        name of column to add index to
	 * @since 1.3
	 */
	public function maybe_update_column( $edit, $column )
	{
		global $wpdb;

		$edit = esc_sql( $edit );

		$results = $wpdb->query( "ALTER TABLE $this->table_name MODIFY $column $edit" );

		echo $results;
	}
}