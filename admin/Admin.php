<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/10/2016
 * Time: 9:45 PM
 */

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class Admin
{
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_tribe_submenu' ) );
        add_action( 'admin_init', array( $this, 'options_page_init' ) );
    }

    public function add_tribe_submenu()
    {
        add_submenu_page(
            'edit.php?post_type=' . TRIBE_EVENTS_POST_TYPE,
            'Registrations',
            'Registrations',
            'manage_options',
            RTEC_URL.'_settings',
            array( $this, 'create_options_page' )
        );
    }

    public static function get_active_tab( $tab = '' )
    {
        switch( $tab ) {
            case 'single':
                return 'single';
            case 'general':
                return 'general';
            case 'email':
                return 'email';
            default:
                return 'registrations';
        }
    }

    public function create_options_page()
    {
        require_once RTEC_URL . '/views/admin/main.php';
    }

    public function blank() {
        // none needed
    }

    public function options_page_init() {

        /**
         * General Settings
         */

        register_setting(
            'rtec-general',
            'rtec-general',
            array( $this, 'validate_general_options' )
        );

        /* General Settings Section */

        add_settings_section(
            'rtec-general-section',
            'General',
            array( $this, 'blank' ),
            'rtec-general-section'
        );

        // max registrations
        $this->create_settings_field( array(
            'option' => 'rtec-general',
            'name' => 'default_max_registrations',
            'title' => 'Default Max Registrations',
            'example' => '',
            'description' => 'Maximum allowed registrants for every event. Type "i" for no limit',
            'callback'  => 'default_text',
            'class' => 'small-text',
            'page' => 'rtec-general-section',
            'section' => 'rtec-general-section',
            'type' => 'number'
        ));

        /**
         * Email Settings
         */

        register_setting(
            'rtec-email',
            'rtec-email',
            array( $this, 'validate_email_options' )
        );

        /* Notification Email Settings Section */

        add_settings_section(
            'rtec-notification-section',
            'Notification Email',
            array( $this, 'blank' ),
            'rtec-notification-section'
        );

        // notification recipients
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'recipients',
            'title' => 'Recipient(s) Email',
            'example' => 'example: one@yoursite.com, two@yoursite.com',
            'description' => 'Enter the email addresses you would like notification emails to go to separated by commas',
            'callback'  => 'default_text',
            'class' => 'large-text',
            'page' => 'rtec-notification-section',
            'section' => 'rtec-notification-section'
        ));

        // notification from
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'notification_from',
            'title' => 'Notification From',
            'example' => 'example: New Registration',
            'description' => 'Enter the name you would like the notification email to come from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec-notification-section',
            'section' => 'rtec-notification-section'
        ));

        /* Confirmation Email Settings Section */

        add_settings_section(
            'rtec-confirmation-section',
            'Confirmation Email',
            array( $this, 'blank' ),
            'rtec-confirmation-section'
        );

        // confirmation from name
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'confirmation_from',
            'title' => 'Confirmation From',
            'example' => 'example: Your Site',
            'description' => 'Enter the name you would like visitors to get the email from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec-confirmation-section',
            'section' => 'rtec-confirmation-section'
        ));

        // confirmation from address
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'confirmation_from_address',
            'title' => 'Confirmation From Address',
            'example' => 'example: registrations@yoursite.com',
            'description' => 'Enter an email address you would like visitors to receive the confirmation email from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec-confirmation-section',
            'section' => 'rtec-confirmation-section'
        ));

        // confirmation from address
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'confirmation_subject',
            'title' => 'Confirmation Subject',
            'example' => 'example: Registration Confirmation',
            'description' => 'Enter a subject for the confirmation email',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec-confirmation-section',
            'section' => 'rtec-confirmation-section'
        ));

        // confirmation message
        $this->create_settings_field( array(
            'option' => 'rtec-email',
            'name' => 'message',
            'title' => 'Message',
            'example' => '',
            'description' => 'Enter the message you would like customers to receive along with details of the event',
            'callback'  => 'email_message_text_area',
            'class' => '',
            'page' => 'rtec-confirmation-section',
            'section' => 'rtec-confirmation-section'
        ));
    }

    public function default_text( $args )
    {
        // get option 'text_string' value from the database
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : '';
        $type = ( isset( $args['type'] ) ) ? 'type="'. $args['type'].'"' : 'type="text"';
        ?>
        <input id="rtec-<?php echo $args['name']; ?>" class="<?php echo $args['class']; ?>" name="<?php echo $args['option'].'['.$args['name'].']'; ?>" <?php echo $type; ?> value="<?php echo $option_string; ?>"/>

        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>

        <?php
    }

    public function email_message_text_area( $args )
    {
        // get option 'text_string' value from the database
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : '';
        ?>
        <textarea id="textarea_string" class="<?php echo $args['class']; ?>" name="<?php echo $args['option'].'['.$args['name'].']'; ?>" cols="80" rows="10"><?php echo $option_string; ?></textarea><br>
        <span><?php print( $args['example'] ); ?></span>

        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>
        <?php
    }

    public function create_settings_field( $args = array() )
    {
        add_settings_field(
            $args['name'],
            $args['title'],
            array( $this, $args['callback'] ),
            $args['page'],
            $args['section'],
            $args
        );
    }

    /**
     * Validate and sanitize form entries
     *
     * This is used for settings not involved in email
     *
     * @param array $input raw input data from the user
     * @return array valid and sanitized data
     */
    public function validate_general_options( $input )
    {
        $new_input = array();

        foreach ( $input as $key => $val ) {
            $new_input[ $key ] = sanitize_text_field( $val );
        }
        return $new_input;
    }

    /**
     * Checks for malicious headers
     *
     * Since these settings are used as part of an email message, the data is
     * checked for potential header injections
     *
     * @param string $value value of an option submitted from the plugin options page
     * @return string sanitized data string or if validation fails, empty string
     */
    public function check_malicious_headers( $value )
    {
        $malicious = array( 'to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:' );

        foreach ( $malicious as $m ) {
            if( stripos( $value, $m ) !== false ) {
                add_settings_error( '', 'setting-error', 'Your entries contain dangerous input', 'error' );
                return '';
            }
        }

        $value = str_replace( array( '\r', '\n', '%0a', '%0d'), ' ' , $value);
        return trim( $value );
    }

    /**
     * Validate and sanitize form entries
     *
     * Since user input is used in emails, it needs to be checked for header injections
     *
     * @param array $input raw input data from the user
     * @return array valid and sanitized data
     */
    public function validate_email_options( $input )
    {
        $new_input = array();

        foreach ( $input as $key => $val ) {
            $new_input[ $key ] = sanitize_text_field( $val );
            $new_input[ $key ] = $this->check_malicious_headers( $val );
        }
        return $new_input;
    }

}