<?php

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

/**
 *
 */
class Database
{

    /**
     * @var object WordPress database object
     */
    protected $wpdb;

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

        $this->wpdb = &$wpdb;
        $this->table_name = $wpdb->prefix . RTEC_TABLENAME;
    }

    public static function createTable()
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

    public function insertEntry( $data )
    {
        $now = date( "Y-m-d H:i:s" );
        
        $event_id = isset( $data['rtec_event_id'] ) ? $data['rtec_event_id'] : '';
        $registration_date = isset( $data['rtec_entry_date'] ) ? $data['rtec_entry_date'] : $now;
        $last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
        $first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
        $email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
        $venue = isset( $data['rtec_venue_title'] ) ? $data['rtec_venue_title'] : '';
        $other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';
        $status = isset( $data['rtec_status'] ) ? $data['rtec_status'] : 'n';

        $this->wpdb->query( $this->wpdb->prepare( "INSERT INTO $this->table_name
          ( event_id, registration_date, last_name, first_name, email, venue, other, status ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )",
            $event_id, $registration_date, $last, $first, $email, $venue, $other, $status ) );
    }

    public function updateEntry( $data )
    {
        $id = isset( $data['rtec_id'] ) ? $data['rtec_id'] : '';
        $last = isset( $data['rtec_last'] ) ? $data['rtec_last'] : '';
        $first = isset( $data['rtec_first'] ) ? $data['rtec_first'] : '';
        $email = isset( $data['rtec_email'] ) ? $data['rtec_email'] : '';
        $other = isset( $data['rtec_other'] ) ? $data['rtec_other'] : '';

        if ( ! empty( $id ) ) {
            $this->wpdb->query( $this->wpdb->prepare( "UPDATE $this->table_name
                SET last_name=%s, first_name=%s, email=%s, other=%s
                WHERE id=%d",
                $last, $first, $email, $other, $id ) );
        }

    }

    public function retrieveEntries( $data )
    {
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

        $results = $this->wpdb->get_results( $sql, $type );
        return $results;
    }

    public function removeRecords( $records ) {
        $where = 'id';
        if ( ( is_array( $records ) && is_email( $records[0] ) ) || is_email( $records ) ) {
            $where = 'email';
        }

        if ( is_array( $records ) ) {
            $registrations_to_be_deleted = implode( ', ', $records);
        } else {
            $registrations_to_be_deleted = $records;
        }

        $this->wpdb->query( $this->wpdb->prepare( "DELETE FROM $this->table_name
        WHERE $where IN($registrations_to_be_deleted)" ) );

        // add a way to check if success
        return true;
    }

    public function updateStatuses() 
    {
        $current = 'c';
        $new = 'n';
        $this->wpdb->query( $this->wpdb->prepare( "UPDATE $this->table_name SET status=%s WHERE status=%s", $current, $new ) );

        // add a way to check if success
        return true;
    }

    public function checkForNew()
    {
        $new = 'n';

        return $this->wpdb->query($this->wpdb->prepare("SELECT status
        FROM $this->table_name WHERE status=%s", $new) );
    }
}