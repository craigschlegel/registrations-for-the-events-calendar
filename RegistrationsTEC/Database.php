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
                seen CHAR(1) DEFAULT 'y' NOT NULL,
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
        $seen = isset( $data['rtec_seen'] ) ? $data['rtec_seen'] : 'n';

        $this->wpdb->query( $this->wpdb->prepare( "INSERT INTO $this->table_name
          ( event_id, registration_date, last_name, first_name, email, venue, other, seen ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )",
            $event_id, $registration_date, $last, $first, $email, $venue, $other, $seen ) );
    }

}