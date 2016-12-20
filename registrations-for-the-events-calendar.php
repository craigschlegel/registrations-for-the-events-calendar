<?php
/*
Plugin Name: Registrations for The Events Calendar
Description: Collect and manage registrations for events posted using The Events Calendar by Modern Tribe.
Version: 1.3.1
Author: Roundup WP
Author URI: roundupwp.com
License: GPLv2 or later
Text Domain: registrations-for-the-events-calendar
*/

/*
Copyright 2016 by Craig Schlegel

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
/**
* @package RTEC
* @author Roundup WP
* @version 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Check for The Events Calendar to be active
function rtec_TEC_check() {
	if ( ! class_exists( 'Tribe__Events__Main' ) ) {
		if ( current_user_can( 'activate_plugins' ) ) {
			add_action( 'admin_init', 'rtec_deactivate' );
			add_action( 'admin_notices', 'rtec_deactivation_notice' );
			function rtec_deactivate() {
				deactivate_plugins( plugin_basename( __FILE__ ) );
			}
			function rtec_deactivation_notice() {
				echo '<div class="updated"><p><strong>Registrations for The Events Calendar has been deactivated</strong>. The Events Calendar plugin must be active for this extension to work</strong>.</p></div>';
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}
}
add_action( 'plugins_loaded', 'rtec_TEC_check' );

if ( ! class_exists( 'Registrations_For_The_Events_Calendar' ) ) :

    /**
     * Main Registrations_For_The_Events_Calendar Class.
     *
     * Design pattern inspired by Pippin Williamson's Easy Digital Downloads
     *
     * @since 1.0
     */
    final class Registrations_For_The_Events_Calendar {
        /** Singleton *************************************************************/
        /**
         * @var Registrations_For_The_Events_Calendar
         * @since 1.0
         */
        private static $instance;

	    /**
	     * @var Registrations_For_The_Events_Calendar
	     * @since 1.0
	     */
	    public $form;

	    /**
	     * @var Registrations_For_The_Events_Calendar
	     * @since 1.0
	     */
	    public $submission;

	    /**
	     * @var Registrations_For_The_Events_Calendar
	     * @since 1.0
	     */
	    public $db_frontend;

        /**
         * Main Registrations_For_The_Events_Calendar Instance.
         *
         * Only on instance of the form and functions at a time
         *
         * @since 1.0
         * @return object|Registrations_For_The_Events_Calendar
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Registrations_For_The_Events_Calendar ) ) {
                self::$instance = new Registrations_For_The_Events_Calendar;
                self::$instance->constants();
                self::$instance->includes();
	            self::$instance->form = new RTEC_Form();
	            if ( isset( $_POST['rtec_email_submission'] ) && '1' === $_POST['rtec_email_submission'] ) {
		            $sanitized_post = array();
		            foreach ( $_POST as $post_key => $raw_post_value ) {
			            $sanitized_post[$post_key] = sanitize_text_field( $raw_post_value );
		            }
		            self::$instance->submission = new RTEC_Submission( $sanitized_post );
		            self::$instance->db_frontend = new RTEC_Db();
	            }
            }
            return self::$instance;
        }

        /**
         * Throw error on object clone.
         *
         * @since 1.0
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'registrations-for-the-events-calendar' ), '1.0' );
        }

        /**
         * Disable unserializing of the class.
         *
         * @since 1.0
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'registrations-for-the-events-calendar' ), '1.0' );
        }

        /**
         * Setup plugin constants.
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function constants() {
            // Plugin version.
            if ( ! defined( 'RTEC_VERSION' ) ) {
                define( 'RTEC_VERSION', '1.3.1' );
            }
            // Plugin Folder Path.
            if ( ! defined( 'RTEC_PLUGIN_DIR' ) ) {
                define( 'RTEC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }
	        // Plugin Folder Path.
	        if ( ! defined( 'RTEC_PLUGIN_URL' ) ) {
		        define( 'RTEC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	        }
	        // Plugin Base Name
	        if ( ! defined( 'RTEC_PLUGIN_BASENAME') ) {
		        define( 'RTEC_PLUGIN_BASENAME', plugin_basename(__FILE__) );
	        }
            // Plugin Title.
            if ( ! defined( 'RTEC_TITLE' ) ) {
                define( 'RTEC_TITLE' , 'Registrations for the Events Calendar' );
            }
            // Db version.
            if ( ! defined( 'RTEC_DBVERSION' ) ) {
                define( 'RTEC_DBVERSION' , '1.2' );
            }
            // Table Name.
            if ( ! defined( 'RTEC_TABLENAME' ) ) {
                define( 'RTEC_TABLENAME' , 'rtec_registrations' );
            }
            // Tribe Events Post Type
            if ( ! defined( 'RTEC_TRIBE_EVENTS_POST_TYPE' ) ) {
                define( 'RTEC_TRIBE_EVENTS_POST_TYPE', 'tribe_events' );
            }
            // Tribe Menu Page.
            if ( ! defined( 'RTEC_TRIBE_MENU_PAGE' ) ) {
                define( 'RTEC_TRIBE_MENU_PAGE', 'edit.php?post_type=tribe_events' );
            }
        }

	    /**
	     * Include required files.
	     *
	     * @access private
	     * @since 1.0
	     * @return void
	     */
	    private function includes() {
		    global $rtec_options;
            $rtec_options = get_option( 'rtec_options', array() );
		    require_once RTEC_PLUGIN_DIR . 'inc/class-rtec-db.php';
		    require_once RTEC_PLUGIN_DIR . 'inc/helper-functions.php';
		    require_once RTEC_PLUGIN_DIR . 'inc/form/class-rtec-form.php';
		    require_once RTEC_PLUGIN_DIR . 'inc/form/form-functions.php';
		    require_once RTEC_PLUGIN_DIR . 'inc/submission/class-rtec-submission.php';
		    if ( is_admin() ) {
			    require_once RTEC_PLUGIN_DIR . 'inc/admin/class-rtec-db-admin.php';
			    require_once RTEC_PLUGIN_DIR . 'inc/admin/admin-functions.php';
			    require_once RTEC_PLUGIN_DIR . 'inc/admin/class-rtec-admin.php';
		    }
	    }

	    /**
	     * Add default settings and create the table in db
	     *
	     * @access public
	     * @since 1.0
	     * @return void
	     */
	    public static function install() {
		    $rtec_options = get_option( 'rtec_options', false );
		    $db = new RTEC_Db_Admin();

		    $db->create_table();

		    if ( ! $rtec_options ) {
			    $defaults = array(
				    'first_show' => true,
				    'first_require' => true,
				    'first_error' => 'Please enter your first name',
				    'last_show' => true,
				    'last_require' => true,
				    'last_error' => 'Please enter your last name',
				    'email_show' => true,
				    'email_require' => true,
				    'email_error' => 'Please enter a valid email address',
				    'phone_show' => false,
				    'phone_require' => false,
				    'phone_error' => 'Please enter a valid phone number',
				    'phone_valid_count' => '7, 10',
				    'recaptcha_require' => false,
				    'other_show' => false,
				    'other_require' => false,
				    'other_error' => 'There is an error with your entry'
			    );
			    // get form options from the db
			    update_option( 'rtec_options', $defaults );
			    // add cues to find the plugin for three days
			    set_transient( 'rtec_new_messages', 'yes', 60 * 60 * 24 * 3 );
		    }

		    $ids = $db->get_event_post_ids();
		    foreach ( $ids as $id ) {
			    $reg_count = $db->get_registration_count( $id );
			    update_post_meta( $id, '_RTECnumRegistered', $reg_count );
		    }

	    }

	    public function text_domain() {
		    load_plugin_textdomain( 'registrations-for-the-events-calendar', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
	    }
    }
endif; // End if class_exists check.
register_activation_hook( __FILE__, array( 'Registrations_For_The_Events_Calendar', 'install' ) );
add_action( 'plugins_loaded', array( 'Registrations_For_The_Events_Calendar', 'text_domain' ) );


/**
 * The main function for Registrations_For_The_Events_Calendar
 *
 * The main function responsible for returning the one true Registrations_For_The_Events_Calendar
 * Instance to functions everywhere.
 *
 * @since 1.0
 * @return object|Registrations_For_The_Events_Calendar The one true Registrations_For_The_Events_Calendar Instance.
 */
function RTEC() {
	return Registrations_For_The_Events_Calendar::instance();
}
// Get rtec Running.
RTEC();