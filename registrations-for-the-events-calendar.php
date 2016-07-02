<?php
/*
Plugin Name: Registrations for The Events Calendar
Description: Allows you to collect registrations for events posted using The Events Calendar by Modern Tribe.
Version: 0.1
Author: Craig Schlegel
Author URI: craigschlegel.com
License: GPLv2 or later
Text Domain: registrations-TEC
*/

/*
Copyright 2015 by Craig Schlegel

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

function registrationsTEC_TEC_check() {
    if( ! class_exists( 'Tribe__Events__Main' ) ) {
        if( current_user_can( 'activate_plugins' ) ) {

            add_action( 'admin_init', 'registrationsTEC_deactivate' );
            add_action( 'admin_notices', 'registrationsTEC_deactivation_notice' );

            function registrationsTEC_deactivate()
            {
                deactivate_plugins( plugin_basename( __FILE__ ) );
            }

            function registrationsTEC_deactivation_notice()
            {
                echo '<div class="updated"><p><strong>Registrations for The Events Calendar has been deactivated</strong>. The Events Calendar plugin must be active for this extension to work</strong>.</p></div>';
                if( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
            }

        }
    }
}
add_action( 'plugins_loaded', 'registrationsTEC_TEC_check' );

function rtec_activate() {

    require_once RTEC_URL . '/RegistrationsTEC/Database.php';

    RegistrationsTEC\Database::createTable();

    $options = get_option( 'rtec_general' );

    if ( empty( $options  ) ) {
        $defaults = array(
            'first_show' => true,
            'first_require' => true,
            'first_error' => 'Please enter your first name',
            'last_show' => true,
            'last_require' => true,
            'last_error' => 'Please enter your last name',
            'email_show' => true,
            'email_require' => false,
            'email_error' => 'Please enter a valid email address',
            'other_show' => false,
            'other_require' => false,
            'other_error' => 'There is an error with your entry'
        );

        // get form options from the db
        update_option( 'rtec_general', $defaults );
    }
}
register_activation_hook( __FILE__, 'rtec_activate' );

define( 'RTEC_URL' , plugin_dir_path( __FILE__ ) );
define( 'RTEC_VERSION' , '0.1' );
define( 'RTEC_DBVERSION' , '0.1' );
define( 'RTEC_TABLENAME' , 'rtec_registrations' );
define( 'TRIBE_EVENTS_POST_TYPE', 'tribe_events' );
define( 'RTEC_TRIBE_MENU_PAGE', 'edit.php?post_type=tribe_events' );

if ( is_admin() ) {
    require_once RTEC_URL . '/admin/Admin.php';

    $admin = new RegistrationsTEC\Admin;
}

function rtec_get_existing_new_reg_count() {
    $existing_new_reg_data = get_transient( 'rtec_new_registrations' );

    if ( $existing_new_reg_data ) {
        $new_registrations_count = $existing_new_reg_data;
    } else {
        require_once RTEC_URL . '/RegistrationsTEC/Database.php';

        $db = new RegistrationsTEC\Database();
        $new_registrations_count = $db->checkForNew();

        if ( ! $existing_new_reg_data ) {
            set_transient( 'rtec_new_registrations', $new_registrations_count, 60 * 15 );
        }
    }

    return $new_registrations_count;
}

add_action( 'admin_menu', 'rtec_registrations_bubble' );
function rtec_registrations_bubble() {
    $new_registrations_count = rtec_get_existing_new_reg_count();

    if ( $new_registrations_count > 0 ) {
        global $menu;
        foreach ( $menu as $key => $value ) {
            if ( $menu[$key][2] === RTEC_TRIBE_MENU_PAGE ) {
                $menu[$key][0] .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>' . $new_registrations_count . '</span></span>';
                return;
            }
        }
    }
}

function rtec_the_registration_form()
{
    $errors = array();
    $submission_data = array();
    $data_sent = false;

    if ( isset( $_POST['rtec_email_submission'] ) && '1' === $_POST['rtec_email_submission'] ) {
        // when a form is submitted, a form submission object is used to send an email and record
        // in the database
        require_once RTEC_URL . '/inc/submission-process.php';
        
        $errors = isset( $submission->errors ) ? $submission->errors : array();
        $submission_data = isset( $submission->submission ) ? $submission->submission : array();

        if ( empty( $errors ) && ! empty ( $submission_data ) ) {
            $data_sent = true;
        }
    }

    if ( ! $data_sent ) {
        require_once RTEC_URL . '/RegistrationsTEC/Form.php';
        $form = new RegistrationsTEC\Form( array( 'first', 'last', 'email', 'other' ), $submission_data, $errors );

        $form->setEventMeta();

        $form_html = '';

        $general_options = get_option( 'rtec_general', array() );

        $max_registrations = isset( $general_options['default_max_registrations'] ) ? $general_options['default_max_registrations'] : 'i';
        $form->setMaxRegistrations( $max_registrations );
        
        $form_html .= $form->getBeginningFormHtml( $general_options );

        $form_html .= $form->getHiddenInputFieldsHtml();

        $form->setInputFieldsData( $general_options );
        $form_html .= $form->getRegularInputFieldsHtml();

        $form_html .= $form->getEndingFormHtml( $general_options );

        echo $form_html;
    } else {
        $options = get_option( 'rtec_email' );

        $success_html = '<p class="rtec_success">';
        if ( isset( $options['success_message'] ) ) {
            $success_html .= esc_html( $options['success_message'] );
        } else {
            $success_html .= 'Success! Please check your email inbox for a confirmation message';
        }
        $success_html .= '</p>';

        echo $success_html;
    }

}
add_action( 'tribe_events_single_event_before_the_content', 'rtec_the_registration_form', 99 );

/**
 * To separate concerns and avoid potential problems with redirects, this function performs
 * a check to see if the registrationsTEC form was submitted and initiates form
 * before the template is loaded.
 */
function rtec_process_form_submission()
{
    if ( isset( $_POST['rtec_email_submission'] ) && '1' === $_POST['rtec_email_submission'] ) {
        // when a form is submitted, a form submission object is used to send an email and record
        // in the database
        require_once RTEC_URL . '/inc/submission-process.php';
    }
}

function rtec_delete_registrations()
{
    $nonce = $_POST['rtec_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
        die ( 'You did not do this the right way!' );
    }

    $registrations_to_be_deleted = array();
    foreach ( $_POST['registrations_to_be_deleted'] as $registration ) {
        $registrations_to_be_deleted[] = sanitize_text_field( $registration );
    }

    require_once RTEC_URL . '/RegistrationsTEC/Database.php';

    $db = new RegistrationsTEC\Database();
    
    if ( $db->removeRecords( $registrations_to_be_deleted ) ) {
        return true;
    } else {
        return false;
    }

    die();
}
add_action( 'wp_ajax_rtec_delete_registrations', 'rtec_delete_registrations' );

function rtec_add_registration()
{
    $nonce = $_POST['rtec_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
        die ( 'You did not do this the right way!' );
    }
    $data = array();
    foreach( $_POST as $key => $value ) {
        $data[$key] = sanitize_text_field( $value );
    }

    if ( ( time() - strtotime( $data['rtec_end_time'] ) ) > 0 ) {
        $data['rtec_status'] = 'p';
    } else {
        $data['rtec_status'] = 'c';
    }
    
    require_once RTEC_URL . '/RegistrationsTEC/Database.php';

    $new_reg = new RegistrationsTEC\Database();
    
    $new_reg->insertEntry( $data );

    die();
}
add_action( 'wp_ajax_rtec_add_registration', 'rtec_add_registration' );

function rtec_update_registration()
{
    $nonce = $_POST['rtec_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'rtec_nonce' ) ) {
        die ( 'You did not do this the right way!' );
    }
    $data = array();
    foreach( $_POST as $key => $value ) {
        $data[$key] = esc_sql( $value );
    }

    require_once RTEC_URL . '/RegistrationsTEC/Database.php';

    $edit_reg = new RegistrationsTEC\Database();

    $edit_reg->updateEntry( $data );

    die();
}
add_action( 'wp_ajax_rtec_update_registration', 'rtec_update_registration' );

/**
 * Some CSS and JS needed in the admin area as well
 */
function rtec_admin_scripts_and_styles() {
    wp_enqueue_style( 'rtec_admin_styles', plugins_url( '/css/rtec-admin-styles.css', __FILE__ ) );
    wp_enqueue_script( 'rtec_admin_scripts', plugins_url( '/js/rtec-admin-scripts.js', __FILE__ ), array( 'jquery' ), '', false );
    wp_localize_script( 'rtec_admin_scripts', 'rtecAdminScript', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rtec_nonce' => wp_create_nonce( 'rtec_nonce' )
        )
    );
}
add_action( 'admin_enqueue_scripts', 'rtec_admin_scripts_and_styles' );

//register_activation_hook( __FILE__, array( 'Tribe__Events__Main', 'activate' ) );
//register_deactivation_hook( __FILE__, array( 'Tribe__Events__Main', 'deactivate' ) );


/* example of message code
 *
 * <pre>
    <?php $message = get_option(RTEC_OPTION_NAME_CONFIRMATION);
    foreach($message as $key => $value ) {
        echo '<br>key: '.$key;
        echo '<br>value: '.$value;
    }
$needle = array( '{example}', '{example2}' );
$replace = array( 'works1', 'works2');
$clean_message = str_replace ( $needle , $replace , $message['message'] );
echo $clean_message;
</pre>
 */