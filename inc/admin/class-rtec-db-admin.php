<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

/**
 *
 */
class RTEC_Db_Admin extends RTEC_Db
{
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

    public function update_statuses() 
    {
        global $wpdb;
        $current = 'c';
        $new = 'n';
        $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name SET status=%s WHERE status=%s", $current, $new ) );
        set_transient( 'rtec_new_registrations', 0, 60 * 15 );
        return true;
    }

    public function check_for_new()
    {
        global $wpdb;
        $new = 'n';
        return $wpdb->query( $wpdb->prepare("SELECT status
        FROM $this->table_name WHERE status=%s", $new ) );
    }

    public function get_registration_count( $id )
    {
        global $wpdb;
        $result = $wpdb->get_results( $wpdb->prepare("SELECT event_id, COUNT(*) AS num_registered
        FROM $this->table_name WHERE event_id = %d", $id ), ARRAY_A );
        return $result[0]['num_registered'];
    }

    public function set_num_registered_meta( $id, $num )
    {
        update_post_meta( $id, '_RTECnumRegistered', (int)$num );
    }

    public function get_event_post_ids() 
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", RTEC_TRIBE_EVENTS_POST_TYPE );
        $event_ids = $wpdb->get_col( $query );
        return $event_ids;
    }
}