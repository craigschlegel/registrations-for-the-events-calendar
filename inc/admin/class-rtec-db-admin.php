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

        if ( $wpdb->get_var("show tables like '$table_name'" ) != $table_name ) {
            $sql = "CREATE TABLE " . $table_name . " (
                id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                event_id SMALLINT NOT NULL,
                registration_date DATETIME NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                first_name VARCHAR(40) NOT NULL,
                email VARCHAR(60) NOT NULL,
                venue VARCHAR(100) NOT NULL,
                other VARCHAR(100) DEFAULT '' NOT NULL,
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
     * @param $data array   information to be updated
     * @since 1.0
     */
    public function update_entry( $data )
    {
        global $wpdb;

        $id = isset( $data['rtec_id'] ) ? $data['rtec_id'] : '';
        $last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
        $first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
        $email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
        $other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';

        if ( ! empty( $id ) ) {
            $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name
                SET last_name=%s, first_name=%s, email=%s, other=%s
                WHERE id=%d",
                $last, $first, $email, $other, $id ) );
        }

    }

    /**
     * Gets all entries that meet a set of parameters
     * 
     * @param $data array       parameters for what entries to retrieve
     *
     * @return mixed bool/array false if no results, registrations if there are
     * @since 1.0
     */
    public function retrieve_entries( $data )
    {
        global $wpdb;

        $fields = $data['fields'];

        if ( is_array( $fields ) ) {
            $fields = implode( ',' , $fields );
        }

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

        $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name
        WHERE $where IN($registrations_to_be_deleted)" ) );

        return true;
    }

    /**
     * One a registration has been seen, status changes from (n)ew to (c)urrent
     * 
     * @return bool
     * @since 1.0
     */
    public function update_statuses() 
    {
        global $wpdb;

        $current = 'c';
        $new = 'n';
        $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name SET status=%s WHERE status=%s", $current, $new ) );
        set_transient( 'rtec_new_registrations', 0, 60 * 15 );

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

        return $wpdb->query( $wpdb->prepare("SELECT status
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

        $result = $wpdb->get_results( $wpdb->prepare("SELECT event_id, COUNT(*) AS num_registered
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
}