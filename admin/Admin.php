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
        $menu_title = 'Registrations';

        $new_registrations_count = rtec_get_existing_new_reg_count();
        if ( $new_registrations_count > 0 ) {
            $menu_title .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>' . $new_registrations_count . '</span></span>';
        }
        add_submenu_page(
            'edit.php?post_type=' . TRIBE_EVENTS_POST_TYPE,
            'Registrations',
            $menu_title,
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
            case 'form':
                return 'form';
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
         * Form Settings
         */

        register_setting(
            'rtec_options',
            'rtec_options',
            array( $this, 'validate_options' )
        );

        /* Form Settings Section */

        add_settings_section(
            'rtec_form_form_fields',
            'Form Fields',
            array( $this, 'blank' ),
            'rtec_form_form_fields'
        );

        $form_fields_array = array(
            0 => array( 'first', 'First', 'Please enter your first name', true, true ),
            1 => array( 'last', 'Last', 'Please enter your last name', true, true ),
            2 => array( 'email', 'Email', 'Please enter a valid email address', true, true )
        );

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'form_fields',
            'title' => 'Select Form Fields',
            'callback'  => 'form_field_select',
            'class' => '',
            'page' => 'rtec_form_form_fields',
            'section' => 'rtec_form_form_fields',
            'fields' => $form_fields_array
        ));


        add_settings_section(
            'rtec_form_registration_availability',
            'Registration Availability',
            array( $this, 'blank' ),
            'rtec_form_registration_availability'
        );

        /* Registration Messages */

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'default_max_registrations',
            'title' => '<label for="rtec_default_max_registrations">Default Max Registrations</label>',
            'example' => '',
            'description' => 'Maximum allowed registrants for every event. Type "i" for no limit',
            'callback'  => 'default_text',
            'class' => 'small-text',
            'page' => 'rtec_form_registration_availability',
            'section' => 'rtec_form_registration_availability',
            'type' => 'number'
        ));

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'num_registrations_messages',
            'title' => '<label>Event Attendance Messages</label>',
            'example' => '',
            'default' => '',
            'description' => '',
            'callback'  => 'num_registrations_messages',
            'class' => '',
            'page' => 'rtec_form_registration_availability',
            'section' => 'rtec_form_registration_availability'
        ));
        
        /* Form Custom Text */

        add_settings_section(
            'rtec_form_custom_text',
            'Custom Text/Labels',
            array( $this, 'blank' ),
            'rtec_form_custom_text'
        );

        // register text
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'register_text',
            'title' => '<label for="rtec_register_text">"Register" Button Text</label>',
            'example' => '',
            'description' => 'The text displayed on the button that reveals the form',
            'callback'  => 'default_text',
            'class' => 'default-text',
            'page' => 'rtec_form_custom_text',
            'section' => 'rtec_form_custom_text',
            'type' => 'text',
            'default' => 'Register'
        ));
        
        // submit text
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'submit_text',
            'title' => '<label for="rtec_submit_text">"Submit" Button Text</label>',
            'example' => '',
            'description' => 'The text displayed on the button that submits the form',
            'callback'  => 'default_text',
            'class' => 'default-text',
            'page' => 'rtec_form_custom_text',
            'section' => 'rtec_form_custom_text',
            'type' => 'text',
            'default' => 'Submit'
        ));

        // success message
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'success_message',
            'title' => '<label>Website Success Message</label>',
            'example' => '',
            'default' => 'Success! Please check your email inbox for a confirmation message',
            'description' => 'Enter the message you would like to display on your site after a successful form completion',
            'callback'  => 'message_text_area',
            'rows' => '3',
            'class' => '',
            'page' => 'rtec_form_custom_text',
            'section' => 'rtec_form_custom_text'
        ));

        /* Form Styling */

        add_settings_section(
            'rtec_form_styles',
            'Styling',
            array( $this, 'blank' ),
            'rtec_form_styles'
        );

        // width
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'width',
            'title' => '<label for="rtec_form_width">Width of Form</label>',
            'example' => '',
            'description' => 'The width of the form',
            'callback'  => 'width_and_height_settings',
            'class' => 'small-text',
            'default' => '100',
            'page' => 'rtec_form_styles',
            'section' => 'rtec_form_styles',
            'type' => 'text',
            'default_unit' => '%'
        ));

        // Custom CSS
        $this->create_settings_field( array(
            'name' => 'custom_css',
            'title' => '<label for="rtec_custom_css">Custom CSS</label>', // label for the input field
            'callback'  => 'custom_code', // name of the function that outputs the html
            'page' => 'rtec_form_styles', // matches the section name
            'section' => 'rtec_form_styles', // matches the section name
            'option' => 'rtec_options', // matches the options name
            'class' => 'default-text', // class for the wrapper and input field
            'description' => 'Enter your own custom CSS in the box below'
        ));

        // Custom JS
        $this->create_settings_field( array(
            'name' => 'custom_js',
            'title' => '<label for="rtec_custom_js">Custom Javascript*</label>', // label for the input field
            'callback'  => 'custom_code', // name of the function that outputs the html
            'page' => 'rtec_form_styles', // matches the section name
            'section' => 'rtec_form_styles', // matches the section name
            'option' => 'rtec_options', // matches the options name
            'class' => 'default-text', // class for the wrapper and input field
            'description' => 'Enter your own custom Javascript/JQuery in the box below',
        ));

        /**
         * Email Settings
         */

        /* Notification Email Settings Section */

        add_settings_section(
            'rtec_email_notification',
            'Notification Email',
            array( $this, 'blank' ),
            'rtec_email_notification'
        );

        // notification recipients
        $this->create_settings_field( array(
            'option' => 'rtec_options',
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
            'option' => 'rtec_options',
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
            'option' => 'rtec_options',
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
            'option' => 'rtec_options',
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
            'option' => 'rtec_options',
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
            'option' => 'rtec_options',
            'name' => 'confirmation_message',
            'title' => '<label>Confirmation Message</label>',
            'example' => '',
            'default' => 'You are registered',
            'description' => 'Enter the message you would like customers to receive along with details of the event',
            'callback'  => 'message_text_area',
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

    public function width_and_height_settings( $args )
    {
        $options = get_option( $args['option'] );
        $default = isset( $args['default'] ) ? $args['default'] : '';
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $default;
        $selected = ( isset( $options[ $args['name'] . '_unit' ] ) ) ? esc_attr( $options[ $args['name'] . '_unit' ] ) : $args['default_unit'];
        ?>
        <input name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec-<?php echo $args['name']; ?>" class="<?php echo $args['class']; ?>" type="number" value="<?php echo $option_string; ?>" />
        <select name="<?php echo $args['option'].'['.$args['name'].'_unit]'; ?>" id="rtec-<?php echo $args['name'].'_unit'; ?>">
            <option value="px" <?php if ( $selected == "px" ) echo 'selected="selected"' ?> >px</option>
            <option value="%" <?php if ( $selected == "%" ) echo 'selected="selected"' ?> >%</option>
        </select>
        
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
            <div class="rtec-field-options-wrapper">
                <h4><?php _e( $label, 'rtec' ); ?></h4>
                <p class="rtec-checkbox-row">
                    <input type="checkbox" name="<?php echo $args['option'].'['.$field[0].'_show]'; ?>" <?php if ( $show == true ) { echo 'checked'; } ?>>
                    <label><?php _e( 'include', 'rtec' ); ?></label>

                    <input type="checkbox" name="<?php echo $args['option'].'['.$field[0].'_require]'; ?>" <?php if ( $require == true ) { echo 'checked'; } ?>>
                    <label><?php _e( 'require', 'rtec' ); ?></label><br>
                </p>
                <p>
                    <label><?php _e( 'Error Message:', 'rtec' ); ?></label>
                    <input type="text" name="<?php echo $args['option'].'['.$field[0].'_error]'; ?>" value="<?php echo $error; ?>" class="large-text">
                </p>
            </div>
        <?php
        } // endforeach
        // the other field is treated specially
        $label = isset( $options[ 'other_label' ] ) ? esc_attr( $options[ 'other_label' ] ) : '';
        $show = isset( $options[ 'other_show' ] ) ? esc_attr( $options[ 'other_show' ] ) : false;
        $require = isset( $options[ 'other_require' ] ) ? esc_attr( $options[ 'other_require' ] ) : false;
        $error = isset( $options[ 'other_error' ] ) ? esc_attr( $options[ 'other_error' ] ) : false;
        ?>
        <div class="rtec-field-options-wrapper">
            <h4><?php _e( 'Other', 'rtec' ); ?> <span>(<?php _e( 'will create a plain text field with your label', 'rtec' ); ?>)</span></h4>
            <p>
                <label><?php _e( 'Custom Label:', 'rtec' ); ?></label><input type="text" name="<?php echo $args['option'].'[other_label]'; ?>" value="<?php echo $label; ?>" class="large-text">
            </p>
            <p class="rtec-checkbox-row">
                <input type="checkbox" name="<?php echo $args['option'].'[other_show]'; ?>" <?php if( $show == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'include', 'rtec' ); ?></label>

                <input type="checkbox" name="<?php echo $args['option'].'[other_require]'; ?>" <?php if( $require == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'require', 'rtec' ); ?></label>
            </p>
            <p>
                <label><?php _e( 'Error Message:', 'rtec' ); ?></label>
                <input type="text" name="<?php echo $args['option'].'[other_error]'; ?>" value="<?php echo $error; ?>" class="large-text">
            </p>
        </div>
        <?php
    }

    public function custom_code( $args )
    {
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : '';
        ?>
        <p><?php _e( $args['description'], 'custom-twitter-feeds' ) ; ?></p>
        <textarea name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="ctf_<?php echo $args['name']; ?>" style="width: 70%;" rows="7"><?php esc_attr_e( stripslashes( $option_string ) ); ?></textarea>
        <?php
    }
    
    public function num_registrations_messages( $args ) {
        $options = get_option( $args['option'] );
        $text_before = ( isset( $options['attendance_text_before'] ) ) ? esc_attr( $options['attendance_text_before'] ) : 'Join';
        $text_after = ( isset( $options['attendance_text_after'] ) ) ? esc_attr( $options['attendance_text_after'] ) : 'others';
        $text_singular = ( isset( $options['attendance_text_singular'] ) ) ? esc_attr( $options['attendance_text_singular'] ) : 'other';
        $text_singular_replace = ( isset( $options['attendance_text_singular_replace'] ) ) ? esc_attr( $options['attendance_text_singular_replace'] ) : 'others';
        $none_yet = ( isset( $options['attendance_text_none_yet'] ) ) ? esc_attr( $options['attendance_text_none_yet'] ) : 'Be the first!';
        $option_checked = ( isset( $options['include_attendance_message'] ) ) ? $options['include_attendance_message'] : true;
        $option_selected = ( isset( $options['attendance_message_type'] ) ) ? $options['attendance_message_type'] : 'up';
        ?>
        <input name="<?php echo $args['option'].'[include_attendance_message]'; ?>" id="rtec_include_attendance_message" type="checkbox" <?php if ( $option_checked ) echo "checked"; ?> />
        <label for="rtec_include_attendance_message"><?php _e( 'include registrations availability message', 'rtec' ); ?></label>
        <br>
        <div class="rtec-availability-options-wrapper">
            <div class="rtec-checkbox-row">
                <h4><?php _e( 'Message Type', 'rtec' ); ?></h4>
                <input id="rtec_guests_attending_type" name="<?php echo $args['option'].'[attendance_message_type]'; ?>" type="radio" value="up" <?php if ( $option_selected == 'up' ) echo "checked"; ?> />
                <label for="rtec_guests_attending_type"><?php _e( 'guests attending', 'rtec' ); ?></label>
                <input id="rtec_spots_remaining_type" name="<?php echo $args['option'].'[attendance_message_type]'; ?>" type="radio" value="down" <?php if ( $option_selected == 'down' ) echo "checked"; ?>/>
                <label for="rtec_spots_remaining_type"><?php _e( 'spots remaining', 'rtec' ); ?></label>
            </div>
        </div>
        <div class="rtec-availability-options-wrapper">

            <h4><?php _e( 'Message Text', 'rtec' ); ?></h4>

            <label for="rtec_text_before"><?php _e( 'Text Before: ', 'rtec' ); ?></label><input id="rtec_text_before" type="text" name="<?php echo $args['option'].'[attendance_text_before]'; ?>" value="<?php echo $text_before; ?>"/>
            <label for="rtec_text_after"><?php _e( 'Text After: ', 'rtec' ); ?></label><input id="rtec_text_after" type="text" name="<?php echo $args['option'].'[attendance_text_after]'; ?>" value="<?php echo $text_after; ?>"/>
            <p class="description">Example: "<strong>Join</strong> 20 <strong>others.</strong>", "<strong>Only</strong> 5 <strong>spots left.</strong>"</p>
            <label for="rtec_text_singular"><?php _e( 'Singular form of plural: ', 'rtec' ); ?></label><input id="rtec_text_singular" type="text" name="<?php echo $args['option'].'[attendance_text_singular]'; ?>" value="<?php echo $text_singular; ?>"/>
            <label for="rtec_text_singular_replace"><?php _e( 'Plural form to replace: ', 'rtec' ); ?></label><input id="rtec_text_singular_replace" type="text" name="<?php echo $args['option'].'[attendance_text_singular_replace]'; ?>" value="<?php echo $text_singular_replace; ?>"/>
            <p class="description">Example: "Join 1 <strong>other</strong>.", "Only 1 <strong>spot</strong> left."</p>
            <br>
            <label for="rtec_text_none_yet"><?php _e( 'Message if no registrations yet: ', 'rtec' ); ?></label>
            <input id="rtec_text_none_yet" type="text" class="large-text" name="<?php echo $args['option'].'[attendance_text_none_yet]'; ?>" value="<?php echo $none_yet; ?>"/>
        </div>
        <?php
    }

    public function message_text_area( $args )
    {
        // get option 'text_string' value from the database
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $args['default'];
        $rows = isset( $args['rows'] ) ? $args['rows'] : '10';
        ?>
        <textarea id="textarea_string" class="<?php echo $args['class']; ?>" name="<?php echo $args['option'].'['.$args['name'].']'; ?>" cols="80" rows="<?php echo $rows; ?>"><?php echo $option_string; ?></textarea><br>
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
    public function validate_options( $input )
    {
        $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'registrations';
        
        $updated_options = get_option( 'rtec_options', false );
        $checkbox_settings = array();
        $leave_spaces = array();

        if ( isset( $input['default_max_registrations'] ) ) {
            $checkbox_settings = array( 'first_show', 'first_require', 'last_show', 'last_require', 'email_show', 'email_require', 'other_show', 'other_require' );
            $leave_spaces = array();
        } elseif ( isset( $input['confirmation_message'] ) ) {
            $checkbox_settings = array();
            $leave_spaces = array();
        }

        foreach ( $checkbox_settings as $checkbox_setting ) {
            $updated_options[$checkbox_setting] = false;
        }

        foreach ( $input as $key => $val ) {
            if ( in_array( $key, $checkbox_settings ) ) {
                if ( $val == 'on' ) {
                    $updated_options[$key] = true;
                }
            } else {
                if ( in_array( $key, $leave_spaces ) ) {
                    $updated_options[$key] = $val;
                } else {
                    $updated_options[$key] = sanitize_text_field( $val );
                }
            }
            if ( $tab === 'email' ) {
                $updated_options[$key] = $this->check_malicious_headers( $val );
            }
        }

        return $updated_options;
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

        $value = str_replace( array( '\r', '\n', '%0a', '%0d' ), ' ' , $value );
        return trim( $value );
    }
}