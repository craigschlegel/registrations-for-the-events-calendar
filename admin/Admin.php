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
            'rtec_general',
            'rtec_general',
            array( $this, 'validate_general_options' )
        );

        /* General Settings Section */

        add_settings_section(
            'rtec_general_general',
            'General',
            array( $this, 'blank' ),
            'rtec_general_general'
        );

        // max registrations
        $this->create_settings_field( array(
            'option' => 'rtec_general',
            'name' => 'default_max_registrations',
            'title' => '<label for="rtec_default_max_registrations">Default Max Registrations</label>',
            'example' => '',
            'description' => 'Maximum allowed registrants for every event. Type "i" for no limit',
            'callback'  => 'default_text',
            'class' => 'small-text',
            'page' => 'rtec_general_general',
            'section' => 'rtec_general_general',
            'type' => 'number'
        ));

        // register text
        $this->create_settings_field( array(
            'option' => 'rtec_general',
            'name' => 'register_text',
            'title' => '<label for="rtec_register_text">"Register" Button Text</label>',
            'example' => '',
            'description' => 'The text displayed on the button that reveals the form',
            'callback'  => 'default_text',
            'class' => 'default-text',
            'page' => 'rtec_general_general',
            'section' => 'rtec_general_general',
            'type' => 'text',
            'default' => 'Register'
        ));
        
        // submit text
        $this->create_settings_field( array(
            'option' => 'rtec_general',
            'name' => 'submit_text',
            'title' => '<label for="rtec_submit_text">"Submit" Button Text</label>',
            'example' => '',
            'description' => 'The text displayed on the button that submits the form',
            'callback'  => 'default_text',
            'class' => 'default-text',
            'page' => 'rtec_general_general',
            'section' => 'rtec_general_general',
            'type' => 'text',
            'default' => 'Submit'
        ));

        /* Form Settings Section */

        add_settings_section(
            'rtec_general_form_fields',
            'Form Fields',
            array( $this, 'blank' ),
            'rtec_general_form_fields'
        );

        $form_fields_array = array(
            0 => array( 'first', 'First', 'Please enter your first name', true, true ),
            1 => array( 'last', 'Last', 'Please enter your last name', true, true ),
            2 => array( 'email', 'Email', 'Please enter a valid email address', true, true )
        );

        $this->create_settings_field( array(
            'option' => 'rtec_general',
            'name' => 'form_fields',
            'title' => 'Select Form Fields',
            'callback'  => 'form_field_select',
            'class' => '',
            'page' => 'rtec_general_form_fields',
            'section' => 'rtec_general_form_fields',
            'fields' => $form_fields_array
        ));

        /**
         * Email Settings
         */

        register_setting(
            'rtec_email',
            'rtec_email',
            array( $this, 'validate_email_options' )
        );

        /* Notification Email Settings Section */

        add_settings_section(
            'rtec_email_notification',
            'Notification Email',
            array( $this, 'blank' ),
            'rtec_email_notification'
        );

        // notification recipients
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'recipients',
            'title' => '<label>Recipient(s) Email</label>',
            'example' => 'example: one@yoursite.com, two@yoursite.com',
            'description' => 'Enter the email addresses you would like notification emails to go to separated by commas',
            'callback'  => 'default_text',
            'class' => 'large-text',
            'page' => 'rtec_email_notification',
            'section' => 'rtec_email_notification'
        ));

        // notification from
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'notification_from',
            'title' => '<label>Notification From</label>',
            'example' => 'example: New Registration',
            'description' => 'Enter the name you would like the notification email to come from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec_email_notification',
            'section' => 'rtec_email_notification'
        ));

        /* Confirmation Email Settings Section */

        add_settings_section(
            'rtec_email_confirmation',
            'Confirmation Email',
            array( $this, 'blank' ),
            'rtec_email_confirmation'
        );

        // confirmation from name
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'confirmation_from',
            'title' => '<label>Confirmation From</label>',
            'example' => 'example: Your Site',
            'description' => 'Enter the name you would like visitors to get the email from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation'
        ));

        // confirmation from address
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'confirmation_from_address',
            'title' => '<label>Confirmation From Address</label>',
            'example' => 'example: registrations@yoursite.com',
            'description' => 'Enter an email address you would like visitors to receive the confirmation email from',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation'
        ));

        // confirmation from address
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'confirmation_subject',
            'title' => '<label>Confirmation Subject</label>',
            'example' => 'example: Registration Confirmation',
            'description' => 'Enter a subject for the confirmation email',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation'
        ));

        // confirmation message
        $this->create_settings_field( array(
            'option' => 'rtec_email',
            'name' => 'confirmation_message',
            'title' => '<label>Message</label>',
            'example' => '',
            'description' => 'Enter the message you would like customers to receive along with details of the event',
            'callback'  => 'email_message_text_area',
            'class' => '',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation'
        ));
    }

    public function default_text( $args )
    {
        // get option 'text_string' value from the database
        $options = get_option( $args['option'] );
        $default = isset( $args['default'] ) ? esc_attr( $args['default'] ) : '';
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $default;
        $type = ( isset( $args['type'] ) ) ? 'type="'. $args['type'].'"' : 'type="text"';
        ?>

        <input id="rtec-<?php echo $args['name']; ?>" class="<?php echo $args['class']; ?>" name="<?php echo $args['option'].'['.$args['name'].']'; ?>" <?php echo $type; ?> value="<?php echo $option_string; ?>"/>
        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>

        <?php
    }

    public function form_field_select( $args )
    {
        $options = get_option( $args['option'] );
        foreach( $args['fields'] as $field ) {
            $label = isset( $field[1] ) ? $field[1] : '';
            $show = isset( $options[ $field[0].'_show' ] ) ? esc_attr( $options[ $field[0].'_show' ] ) : $field[3];
            $require = isset( $options[ $field[0].'_require' ] ) ? esc_attr( $options[ $field[0].'_require' ] ) : $field[4];
            $error = isset( $options[ $field[0].'_error' ] ) ? esc_attr( $options[ $field[0].'_error' ] ) : $field[2];
            ?>
            <label><?php esc_attr_e( $label ); ?></label><br>
            <div class="rtec-field-options-wrapper">
                <input type="checkbox" name="<?php echo $args['option'].'['.$field[0].'_show]'; ?>" <?php if ( $show == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'show', 'registrationstec' ); ?></label>

                <input type="checkbox" name="<?php echo $args['option'].'['.$field[0].'_require]'; ?>" <?php if ( $require == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'require', 'registrationstec' ); ?></label><br>

                <label><?php _e( 'error message', 'registrationstec' ); ?></label>
                <input type="text" name="<?php echo $args['option'].'['.$field[0].'_error]'; ?>" value="<?php echo $error; ?>">
            </div>
        <?php
        } // endforeach
        // the other field is treated specially
        $label = isset( $options[ 'other_label' ] ) ? esc_attr( $options[ 'other_label' ] ) : '';
        $show = isset( $options[ 'other_show' ] ) ? esc_attr( $options[ 'other_show' ] ) : false;
        $require = isset( $options[ 'other_require' ] ) ? esc_attr( $options[ 'other_require' ] ) : false;
        $error = isset( $options[ 'other_error' ] ) ? esc_attr( $options[ 'other_error' ] ) : false;
        ?>
        <label>Other (will create a plain text field with your label)</label><br>
        <div class="rtec-field-options-wrapper">
            <label>Custom Label:</label><input type="text" name="<?php echo $args['option'].'[other_label]'; ?>" value="<?php echo $label; ?>"><br>
            <input type="checkbox" name="<?php echo $args['option'].'[other_show]'; ?>" <?php if( $show == true ) { echo 'checked'; } ?>>
            <label><?php _e( 'show', 'registrationstec' ); ?></label>

            <input type="checkbox" name="<?php echo $args['option'].'[other_require]'; ?>" <?php if( $require == true ) { echo 'checked'; } ?>>
            <label><?php _e( 'require', 'registrationstec' ); ?></label>

            <input type="text" name="<?php echo $args['option'].'[other_error]'; ?>" value="<?php echo $error; ?>">
            <label><?php _e( 'error', 'registrationstec' ); ?></label><br>
        </div>
        <?php
    }

    public function required_field_select() { ?>
        <div>
                    <span>Required?</span><!--
                    <input type="checkbox" name="'.$args['option'].'['.$args['name'].'_required]'.'" value="y"><label>Yes</label>';
                    <input type="checkbox" name="'.$args['option'].'['.$args['name'].'_required]'.'" value="n"><label>No</label>';
            </div> -->
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

        $checkbox_settings = array( 'first_show', 'first_require', 'last_show', 'last_require', 'email_show', 'email_require', 'other_show', 'other_require' );
        $leave_spaces = array();

        foreach ( $checkbox_settings as $checkbox_setting ) {
            $new_input[$checkbox_setting] = false;
        }

        foreach ( $input as $key => $val ) {
            if ( in_array( $key, $checkbox_settings ) ) {
                if ( $val == 'on' ) {
                    $new_input[$key] = true;
                }
            } else {
                if ( in_array( $key, $leave_spaces ) ) {
                    $new_input[$key] = $val;
                } else {
                    $new_input[$key] = sanitize_text_field( $val );
                }
            }
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