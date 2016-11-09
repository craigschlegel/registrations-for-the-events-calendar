<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

/**
 * Class RTEC_Admin
 * 
 * Just your standard settings pages with a tab to view current registrations
 * 
 * @since 1.0
 */
class RTEC_Admin
{
	/**
	 * RTEC_Admin constructor.
	 * 
	 * Create the basic admin pages
	 */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_tribe_submenu' ) );
        add_action( 'admin_init', array( $this, 'options_page_init' ) );
    }

    /**
     * Add the menu with new registration count alert
     * 
     * @since 1.0
     */
    public function add_tribe_submenu()
    {
        $menu_title = 'Registrations';

        $new_registrations_count = rtec_get_existing_new_reg_count();

        if ( $new_registrations_count > 0 ) {
            $menu_title .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>' . $new_registrations_count . '</span></span>';
        } else {
            if ( get_transient( 'rtec_new_messages' ) === 'yes' ) {
                $menu_title .= ' <span class="update-plugins rtec-notice-admin-reg-count"><span>New!</span></span>';
            }
        }

        add_submenu_page(
	        'edit.php?post_type=' . RTEC_TRIBE_EVENTS_POST_TYPE,
            'Registrations',
            $menu_title,
            'manage_options',
            RTEC_PLUGIN_DIR . '_settings',
            array( $this, 'create_options_page' )
        );
    }
    
    /**
     * Validates the $_GET field with tab information
     * 
     * @param string $tab   current selected tab
     *
     * @return string       name of the tab to navigate to
     * @since 1.0
     */
    public static function get_active_tab( $tab = '' )
    {
        switch( $tab ) {
            case 'single':
                return 'single';
            case 'form':
                return 'form';
            case 'email':
                return 'email';
	        case 'support':
		        return 'support';
            default:
                return 'registrations';
        }
    }
    
    public function create_options_page()
    {
        require_once RTEC_PLUGIN_DIR . '/inc/admin/templates/main.php';
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
            'rtec_timezone',
            '',
            array( $this, 'blank' ),
            'rtec_timezone'
        );

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'timezone',
            'title' => '<label for="rtec_timezone">Select your timezone</label>',
            'callback'  => 'timezone',
            'class' => '',
            'page' => 'rtec_timezone',
            'section' => 'rtec_timezone',
        ));

        add_settings_section(
            'rtec_form_form_fields',
            'Form Fields',
            array( $this, 'blank' ),
            'rtec_form_form_fields'
        );

        $form_fields_array = array(
            0 => array( 'first', 'First', 'Please enter your first name', true, true, '' ),
            1 => array( 'last', 'Last', 'Please enter your last name', true, true, '' ),
            2 => array( 'email', 'Email', 'Please enter a valid email address', true, true, '' ),
            3 => array( 'phone', 'Phone', 'Please enter a valid phone number', false, false, '7, 10' )
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

        /* Registration Messages */

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'limit_registrations',
            'title' => '<label for="rtec_limit_registrations">Limit Registrations for Events</label>',
            'example' => '',
            'description' => 'Only allow a certain amount of registrations for each event',
            'callback'  => 'default_checkbox',
            'class' => '',
            'page' => 'rtec_form_registration_availability',
            'section' => 'rtec_form_registration_availability',
            'default' => false
        ));

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'default_max_registrations',
            'title' => '<label for="rtec_default_max_registrations">Default Max Registrations</label>',
            'example' => '',
            'description' => 'Maximum allowed registrants for every event (if any limit)',
            'callback'  => 'default_text',
            'class' => 'small-text',
            'page' => 'rtec_form_registration_availability',
            'section' => 'rtec_form_registration_availability',
            'type' => 'number',
            'default' => 30
        ));

        // Registration Deadline
        $this->create_settings_field( array(
            'name' => 'registration_deadline',
            'title' => '<label for="rtec_registration_deadline">Registration Deadline</label>', // label for the input field
            'callback'  => 'deadline_offset', // name of the function that outputs the html
            'page' => 'rtec_form_registration_availability', // matches the section name
            'section' => 'rtec_form_registration_availability', // matches the section name
            'option' => 'rtec_options', // matches the options name
            'class' => 'short-text', // class for the wrapper and input field
        ) );

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
            'section' => 'rtec_form_custom_text',
            'legend' => false
        ));

        /* Form Styling */

        add_settings_section(
            'rtec_form_registration_availability',
            'Registration Availability',
            array( $this, 'blank' ),
            'rtec_form_registration_availability'
        );

        add_settings_section(
            'rtec_form_styles',
            'Styling',
            array( $this, 'blank' ),
            'rtec_form_styles'
        );

        // Template Location
        $this->create_settings_field( array(
            'name' => 'template_location',
            'title' => 'Form Location', // label for the input field
            'callback'  => 'default_select', // name of the function that outputs the html
            'page' => 'rtec_form_styles', // matches the section name
            'section' => 'rtec_form_styles', // matches the section name
            'option' => 'rtec_options', // matches the options name
            'class' => 'default-text', // class for the wrapper and input field
            'fields' => array(
                1 => array( 'tribe_events_single_event_before_the_content', 'Before the content' ),
                2 => array( 'tribe_events_single_event_after_the_content', 'After the content' ),
                3 => array( 'tribe_events_single_event_before_the_meta', 'Before the meta' ),
                4 => array( 'tribe_events_single_event_after_the_meta', 'After the meta' )
            ),
            'description' => "Location where the form will appear in the single event template" // what is this? text
        ) );

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

        /* Advanced */

        add_settings_section(
            'rtec_advanced',
            'Advanced',
            array( $this, 'blank' ),
            'rtec_advanced'
        );

        // preserve database
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'preserve_db',
            'title' => '<label for="rtec_preserve_db">Preserve registrations and settings on uninstall</label>',
            'example' => '',
            'description' => 'Keep your registration records and settings preserved in the database when you uninstall the plugin',
            'callback'  => 'default_checkbox',
            'class' => 'default-text',
            'page' => 'rtec_advanced',
            'section' => 'rtec_advanced',
            'default' => false
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

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'disable_notification',
            'title' => '<label for="rtec_disable_notification">Disable Notification Email</label>',
            'example' => '',
            'description' => '',
            'callback'  => 'default_checkbox',
            'class' => '',
            'page' => 'rtec_email_notification',
            'section' => 'rtec_email_notification',
            'default' => false
        ));

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
            'section' => 'rtec_email_notification',
            'default' => get_option( 'admin_email' )
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
            'section' => 'rtec_email_notification',
            'default' => 'New Registration'
        ));

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'use_custom_notification',
            'title' => '<label for="rtec_disable_notification">Use Custom Notification Message</label>',
            'example' => '',
            'description' => 'Click to reveal and use a custom message that you can configure',
            'callback'  => 'default_checkbox',
            'class' => '',
            'page' => 'rtec_email_notification',
            'section' => 'rtec_email_notification',
            'default' => false
        ));

        // notification message
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'notification_message',
            'title' => '<label>Notification Message</label>',
            'example' => '',
            'default' => 'The following submission was made for: {event-title} at {venue} on {event-date}{nl}First: {first}{nl}Last: {last}{nl}Email: {email}',
            'description' => 'Enter the message you would like your selected recipients to receive when a submission is made',
            'callback'  => 'message_text_area',
            'class' => 'rtec-confirmation-message-tr rtec-notification-message-tr',
            'page' => 'rtec_email_notification',
            'section' => 'rtec_email_notification',
            'columns' => '60',
            'preview' => true,
            'legend' => true
        ));

        /* Confirmation Email Settings Section */

        add_settings_section(
            'rtec_email_confirmation',
            'Confirmation Email',
            array( $this, 'blank' ),
            'rtec_email_confirmation'
        );

        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'disable_confirmation',
            'title' => '<label for="rtec_disable_confirmation">Disable Confirmation Email</label>',
            'example' => '',
            'description' => '',
            'callback'  => 'default_checkbox',
            'class' => '',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation',
            'default' => false
        ));

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
            'section' => 'rtec_email_confirmation',
            'default' => 'Thank You'
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
            'section' => 'rtec_email_confirmation',
            'default' => get_option( 'admin_email' )
        ));

        // confirmation subject
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'confirmation_subject',
            'title' => '<label>Confirmation Subject</label>',
            'example' => 'example: Registration Confirmation',
            'description' => 'Enter a subject for the confirmation email',
            'callback'  => 'default_text',
            'class' => 'regular-text',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation',
            'default' => 'Registration Confirmation'
        ));

        // confirmation message
        $this->create_settings_field( array(
            'option' => 'rtec_options',
            'name' => 'confirmation_message',
            'title' => '<label>Confirmation Message</label>',
            'example' => '',
            'default' => 'You are registered!{nl}{nl}Here are the details of your registration.{nl}{nl}Event: {event-title} at {venue} on {event-date}{nl}Registered Name: {first} {last}{nl}Phone: {phone}{nl}Other: {other}{nl}{nl}The event will be held at this location:{nl}{nl}{venue-address}{nl}{venue-city}, {venue-state} {venue-zip}{nl}{nl}See you there!',
            'description' => 'Enter the message you would like customers to receive along with details of the event',
            'callback'  => 'message_text_area',
            'class' => 'rtec-confirmation-message-tr',
            'page' => 'rtec_email_confirmation',
            'section' => 'rtec_email_confirmation',
	        'columns' => '60',
	        'preview' => true,
            'legend' => true
        ));

        // date format
        $this->create_settings_field( array(
            'name' => 'custom_date_format',
            'title' => '<label for="rtec_custom_date_format">Custom Date Format</label>', // label for the input field
            'callback'  => 'customize_custom_date_format', // name of the function that outputs the html
            'page' => 'rtec_email_confirmation', // matches the section name
            'section' => 'rtec_email_confirmation', // matches the section name
            'option' => 'rtec_options', // matches the options name
            'class' => 'default-text', // class for the wrapper and input field
            'description' => 'If you would like a custom date format in your messages, enter it here using the examples as a guide',
            'default' => 'F j, Y'
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

    public function default_select( $args )
    {
        $options = get_option( $args['option'] );
        $selected = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : '';
        ?>
        <select name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec_<?php echo $args['name']; ?>" class="<?php echo $args['class']; ?>">
            <?php foreach ( $args['fields'] as $field ) : ?>
                <option value="<?php echo $field[0]; ?>" id="rtec-<?php echo $args['name']; ?>" class="<?php echo $args['class']; ?>"<?php if( $selected == $field[0] ) { echo ' selected'; } ?>><?php _e( $field[1], 'rtec' ); ?></option>
            <?php endforeach; ?>
        </select>
        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>
        <?php
    }

    public function default_checkbox( $args )
    {
        $options = get_option( $args['option'] );
        $option_checked = ( isset( $options[ $args['name'] ] ) ) ? $options[ $args['name'] ] : $args['default'];
        ?>
        <input name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec_<?php echo $args['name']; ?>" type="checkbox" <?php if ( $option_checked === true ) echo "checked"; ?> />
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
            $custom_label = isset( $options[ $field[0].'_label' ] ) ? esc_attr( $options[ $field[0].'_label' ]  ) : $label;
            $show = isset( $options[ $field[0].'_show' ] ) ? esc_attr( $options[ $field[0].'_show' ] ) : $field[3];
            $require = isset( $options[ $field[0].'_require' ] ) ? esc_attr( $options[ $field[0].'_require' ] ) : $field[4];
            $error = isset( $options[ $field[0].'_error' ] ) ? esc_attr( $options[ $field[0].'_error' ] ) : $field[2];
            $valid_count = isset( $options[ $field[0].'_valid_count' ] ) ? esc_attr( $options[ $field[0].'_valid_count' ] ) : $field[5];
            ?>
            <div class="rtec-field-options-wrapper">
                <h4><?php _e( $label, 'rtec' ); ?></h4>
                <p>
                    <label><?php _e( 'Custom Label:', 'rtec' ); ?></label><input type="text" name="<?php echo $args['option'].'['.$field[0].'_label]'; ?>" value="<?php echo $custom_label; ?>" class="large-text">
                </p>
                <p class="rtec-checkbox-row">
                    <input type="checkbox" class="rtec_include_checkbox" name="<?php echo $args['option'].'['.$field[0].'_show]'; ?>" <?php if ( $show == true ) { echo 'checked'; } ?>>
                    <label><?php _e( 'include', 'rtec' ); ?></label>

                    <input type="checkbox" class="rtec_require_checkbox" name="<?php echo $args['option'].'['.$field[0].'_require]'; ?>" <?php if ( $require == true ) { echo 'checked'; } ?>>
                    <label><?php _e( 'require', 'rtec' ); ?></label><br>
                </p>
                <p>
                    <label><?php _e( 'Error Message:', 'rtec' ); ?></label>
                    <input type="text" name="<?php echo $args['option'].'['.$field[0].'_error]'; ?>" value="<?php echo $error; ?>" class="large-text rtec-other-input">
                </p>
                <?php if ( ! empty( $valid_count ) ) : ?>
                <p>
                    <label><?php _e( 'Required length for validation:', 'rtec' ); ?></label>
                    <input type="text" name="<?php echo $args['option'].'['.$field[0].'_valid_count]'; ?>" value="<?php echo $valid_count; ?>" class="large-text rtec-valid-count-input">
                    <a class="rtec-tooltip-link" href="JavaScript:void(0);"><?php _e( 'What is this?' ); ?></a>
                    <span class="rtec-tooltip rtec-availability-options-wrapper"><?php _e( 'Enter the length or lengths of the responses that are valid for this field separated by commas. For example, to accept North American phone numbers with and without area codes you would enter "7, 10". If area code is required, enter "10"' ); ?></span>
                </p>
                <?php endif; ?>
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
                <input type="checkbox" class="rtec_include_checkbox" name="<?php echo $args['option'].'[other_show]'; ?>" <?php if( $show == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'include', 'rtec' ); ?></label>

                <input type="checkbox" class="rtec_require_checkbox" name="<?php echo $args['option'].'[other_require]'; ?>" <?php if( $require == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'require', 'rtec' ); ?></label>
            </p>
            <p>
                <label><?php _e( 'Error Message:', 'rtec' ); ?></label>
                <input type="text" name="<?php echo $args['option'].'[other_error]'; ?>" value="<?php echo $error; ?>" class="large-text rtec-other-input">
            </p>
        </div>
        <?php
        // the other field is treated specially
        $label = isset( $options[ 'recaptcha_label' ] ) ? esc_attr( $options[ 'recaptcha_label' ] ) : 'What is';
        $require = isset( $options[ 'recaptcha_require' ] ) ? esc_attr( $options[ 'recaptcha_require' ] ) : false;
        $error = isset( $options[ 'recaptcha_error' ] ) ? esc_attr( $options[ 'recaptcha_error' ] ) : 'Please try again';
        ?>
        <div class="rtec-field-options-wrapper">
            <h4><?php _e( 'Recaptcha', 'rtec' ); ?> <span>(<?php _e( 'Simple math question to avoid spam entries. Spam "honey pot" field is in the form by default', 'rtec' ); ?>)</span></h4>
            <p>
                <label><?php _e( 'Custom Label: ', 'rtec' ); ?></label><input type="text" name="<?php echo $args['option'].'[recaptcha_label]'; ?>" value="<?php echo $label; ?>" />
                <span> 2 + 5</span>
            </p>
            <p class="rtec-checkbox-row">
                <input type="checkbox" class="rtec_require_checkbox" name="<?php echo $args['option'].'[recaptcha_require]'; ?>" <?php if( $require == true ) { echo 'checked'; } ?>>
                <label><?php _e( 'require and include', 'rtec' ); ?></label>
            </p>
            <p>
                <label><?php _e( 'Error Message:', 'rtec' ); ?></label>
                <input type="text" name="<?php echo $args['option'].'[recaptcha_error]'; ?>" value="<?php echo $error; ?>" class="large-text rtec-recaptcha-input">
            </p>
        </div>
        <?php
    }

    public function custom_code( $args )
    {
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : '';
        ?>
        <p><?php _e( $args['description'], 'rtec' ) ; ?></p>
        <textarea name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec_<?php echo $args['name']; ?>" style="width: 70%;" rows="7"><?php esc_attr_e( stripslashes( $option_string ) ); ?></textarea>
        <?php
    }

    public function deadline_offset( $args )
    {
        $options = get_option( $args['option'] );
        $default = 0;
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $default;
        $selected = ( isset( $options[ $args['name'] . '_unit' ] ) ) ? esc_attr( $options[ $args['name'] . '_unit' ] ) : '3600';
        ?>
        <span><?php _e( 'Accept registrations up until', 'rtec' ); ?></span>
        <input name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec_<?php echo $args['name']; ?>" type="number" value="<?php echo $option_string; ?>"/>
        <select name="<?php echo $args['option'].'['.$args['name'].'_unit]'; ?>">
            <option value="60" <?php if ( $selected == "60" ) echo 'selected="selected"' ?> ><?php esc_attr_e( 'Minutes' ); ?></option>
            <option value="3600" <?php if ( $selected == "3600" ) echo 'selected="selected"' ?> ><?php esc_attr_e( 'Hours' ); ?></option>
            <option value="86400" <?php if ( $selected == "86400" ) echo 'selected="selected"' ?> ><?php esc_attr_e( 'Days' ); ?></option>
        </select>
        <span><?php _e( 'before event start time', 'rtec' ); ?></span>
     <?php
    }
    
    public function num_registrations_messages( $args ) {
        $options = get_option( $args['option'] );
        $text_before_up = ( isset( $options['attendance_text_before_up'] ) ) ? esc_attr( $options['attendance_text_before_up'] ) : 'Join';
        $text_after_up = ( isset( $options['attendance_text_after_up'] ) ) ? esc_attr( $options['attendance_text_after_up'] ) : 'others';
        $one_up = ( isset( $options['attendance_text_one_up'] ) ) ? esc_attr( $options['attendance_text_one_up'] ) : 'Join one other person';
        $text_before_down = ( isset( $options['attendance_text_before_down'] ) ) ? esc_attr( $options['attendance_text_before_down'] ) : 'Only';
        $text_after_down = ( isset( $options['attendance_text_after_down'] ) ) ? esc_attr( $options['attendance_text_after_down'] ) : 'spots left';
        $one_down = ( isset( $options['attendance_text_one_down'] ) ) ? esc_attr( $options['attendance_text_one_down'] ) : 'Only one spot left!';
        $none_yet = ( isset( $options['attendance_text_none_yet'] ) ) ? esc_attr( $options['attendance_text_none_yet'] ) : 'Be the first!';
        $closed = ( isset( $options['registrations_closed_message'] ) ) ? esc_attr( $options['registrations_closed_message'] ) : 'Registrations are closed for this event';
        $option_checked = ( isset( $options['include_attendance_message'] ) ) ? $options['include_attendance_message'] : true;
        $option_selected = ( isset( $options['attendance_message_type'] ) ) ? $options['attendance_message_type'] : 'up';
        ?>
        <input name="<?php echo $args['option'].'[include_attendance_message]'; ?>" id="rtec_include_attendance_message" type="checkbox" <?php if ( $option_checked ) echo "checked"; ?> />
        <label for="rtec_include_attendance_message"><?php _e( 'include registrations availability message', 'rtec' ); ?></label>
        <br>
        <div class="rtec-availability-options-wrapper" id="rtec-message-type-wrapper">
            <div class="rtec-checkbox-row">
                <h4><?php _e( 'Message Type', 'rtec' ); ?></h4>
                <input class="rtec_attendance_message_type" id="rtec_guests_attending_type" name="<?php echo $args['option'].'[attendance_message_type]'; ?>" type="radio" value="up" <?php if ( $option_selected == 'up' ) echo "checked"; ?> />
                <label for="rtec_guests_attending_type"><?php _e( 'guests attending (count up)', 'rtec' ); ?></label>
                <input class="rtec_attendance_message_type" id="rtec_spots_remaining_type" name="<?php echo $args['option'].'[attendance_message_type]'; ?>" type="radio" value="down" <?php if ( $option_selected == 'down' ) echo "checked"; ?>/>
                <label for="rtec_spots_remaining_type"><?php _e( 'spots remaining (count down)', 'rtec' ); ?></label>
            </div>
        </div>
        
        <div class="rtec-availability-options-wrapper rtec-admin-2-columns" id="rtec-message-text-wrapper-up">

            <h4><?php _e( 'Guests Attending Message Text', 'rtec' ); ?></h4>

            <label for="rtec_text_before_up"><?php _e( 'Text Before: ', 'rtec' ); ?></label><input id="rtec_text_before_up" type="text" name="<?php echo $args['option'].'[attendance_text_before_up]'; ?>" value="<?php echo $text_before_up; ?>"/></br>
            <label for="rtec_text_after_up"><?php _e( 'Text After: ', 'rtec' ); ?></label><input id="rtec_text_after_up" type="text" name="<?php echo $args['option'].'[attendance_text_after_up]'; ?>" value="<?php echo $text_after_up; ?>"/>
            <p class="description">Example: "<strong>Join</strong> 20 <strong>others.</strong>"</p>
	        <br>
	        <label for="rtec_text_one_up"><?php _e( 'Message if exactly 1 registration: ', 'rtec' ); ?></label>
	        <input id="rtec_text_one_up" type="text" class="large-text" name="<?php echo $args['option'].'[attendance_text_one_up]'; ?>" value="<?php echo $one_up; ?>"/>

        </div>
        
        <div class="rtec-availability-options-wrapper rtec-admin-2-columns" id="rtec-message-text-wrapper-down">

            <h4><?php _e( 'Spots Remaining Message Text', 'rtec' ); ?></h4>

            <label for="rtec_text_before_down"><?php _e( 'Text Before: ', 'rtec' ); ?></label><input id="rtec_text_before_down" type="text" name="<?php echo $args['option'].'[attendance_text_before_down]'; ?>" value="<?php echo $text_before_down; ?>"/></br>
            <label for="rtec_text_after_down"><?php _e( 'Text After: ', 'rtec' ); ?></label><input id="rtec_text_after_down" type="text" name="<?php echo $args['option'].'[attendance_text_after_down]'; ?>" value="<?php echo $text_after_down; ?>"/>
            <p class="description">Example: "<strong>Only</strong> 5 <strong>spots left.</strong>"</p>
            <br>
            <label for="rtec_text_one_down"><?php _e( 'Message if exactly 1 spot left: ', 'rtec' ); ?></label>
            <input id="rtec_text_one_down" type="text" class="large-text" name="<?php echo $args['option'].'[attendance_text_one_down]'; ?>" value="<?php echo $one_down; ?>"/>

        </div>
        
        <div class="rtec-availability-options-wrapper" id="rtec-message-text-wrapper-other">

            <h4><?php _e( 'Other Messages', 'rtec' ); ?></h4>

            <label for="rtec_text_none_yet"><?php _e( 'Message if no registrations yet: ', 'rtec' ); ?></label>
            <input id="rtec_text_none_yet" type="text" class="large-text" name="<?php echo $args['option'].'[attendance_text_none_yet]'; ?>" value="<?php echo $none_yet; ?>"/>
            <br><br>
            <label for="rtec_registrations_closed_message"><?php _e( 'Message if registrations are closed or filled: ', 'rtec' ); ?></label>
            <input id="rtec_registrations_closed_message" type="text" class="large-text" name="<?php echo $args['option'].'[registrations_closed_message]'; ?>" value="<?php echo $closed; ?>"/>

        </div>
        <?php
    }

    public function message_text_area( $args )
    {
        // get option 'text_string' value from the database
        $options = get_option( $args['option'] );
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $args['default'];
        $rows = isset( $args['rows'] ) ? $args['rows'] : '10';
	    $columns = isset( $args['columns'] ) ? $args['columns'] : '70';
	    $preview = isset( $args['preview'] ) ? $args['preview'] : false;
        ?>
        <textarea id="confirmation_message_textarea" class="<?php echo $args['class']; ?> confirmation_message_textarea" name="<?php echo $args['option'].'['.$args['name'].']'; ?>" cols="<?php echo $columns; ?>" rows="<?php echo $rows; ?>"><?php echo $option_string; ?></textarea>

        <?php if ( $args['legend'] ) : ?>
        <a class="rtec-tooltip-link" href="JavaScript:void(0);"><?php _e( 'Legend' ); ?></a>
        <span class="rtec-tooltip-table rtec-tooltip rtec-availability-options-wrapper">
            <span class="rtec-col-1">{venue}</span><span class="rtec-col-2">Event venue/location</span>
            <span class="rtec-col-1">{venue-address}</span><span class="rtec-col-2">Venue street address</span>
            <span class="rtec-col-1">{venue-city}</span><span class="rtec-col-2">Venue city</span>
            <span class="rtec-col-1">{venue-state}</span><span class="rtec-col-2">Venue state/province</span>
            <span class="rtec-col-1">{venue-zip}</span><span class="rtec-col-2">Venue zip code</span>
            <span class="rtec-col-1">{event-title}</span><span class="rtec-col-2">Title of event</span>
            <span class="rtec-col-1">{event-date}</span><span class="rtec-col-2">Event start date</span>
            <span class="rtec-col-1">{first}</span><span class="rtec-col-2">First name of registrant</span>
            <span class="rtec-col-1">{last}</span><span class="rtec-col-2">Last name of registrant</span>
            <span class="rtec-col-1">{email}</span><span class="rtec-col-2">Email of registrant</span>
            <span class="rtec-col-1">{phone}</span><span class="rtec-col-2">Phone number of registrant</span>
            <span class="rtec-col-1">{other}</span><span class="rtec-col-2">Information submitted in the "other" field</span>
            <span class="rtec-col-1">{ical-url}</span><span class="rtec-col-2">Plain text web address to download ical file for event</span>
            <span class="rtec-col-1">{nl}</span><span class="rtec-col-2">Creates a new line/line break</span>
        </span>
        <?php endif; ?>

        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>
        <?php if ( $preview ) : ?>
	    <td>
		    <h4>Preview:</h4>
		    <div class="rtec_js_preview">
			    <pre></pre>
		    </div>
	    </td>
	    <?php endif; ?>
        <?php
    }

    public function timezone()
    {
        $options = get_option( 'rtec_options' );
        $rtec_timezone = ( isset( $options['timezone'] ) ) ? esc_attr( $options['timezone'] ) : '';
        ?>
        <select name="rtec_options[timezone]" id="rtec_timezone" style="width: 300px;">
            <option value="America/New_York" <?php if( $rtec_timezone == "(GMT05:00) Eastern Time (US & Canada)" ) echo 'selected="selected"' ?> ><?php _e( '(GMT05:00) Eastern Time (US & Canada)' ) ?></option>
            <option value="Pacific/Midway" <?php if( $rtec_timezone == "Pacific/Midway" ) echo 'selected="selected"' ?> ><?php _e( '(GMT11:00) Midway Island, Samoa' ) ?></option>
            <option value="America/Adak" <?php if( $rtec_timezone == "America/Adak" ) echo 'selected="selected"' ?> ><?php _e( '(GMT10:00) HawaiiAleutian' ) ?></option>
            <option value="Etc/GMT+10" <?php if( $rtec_timezone == "Etc/GMT+10" ) echo 'selected="selected"' ?> ><?php _e( '(GMT10:00) Hawaii' ) ?></option>
            <option value="Pacific/Marquesas" <?php if( $rtec_timezone == "Pacific/Marquesas" ) echo 'selected="selected"' ?> ><?php _e( '(GMT09:30) Marquesas Islands' ) ?></option>
            <option value="Pacific/Gambier" <?php if( $rtec_timezone == "Pacific/Gambier" ) echo 'selected="selected"' ?> ><?php _e( '(GMT09:00) Gambier Islands' ) ?></option>
            <option value="America/Anchorage" <?php if( $rtec_timezone == "America/Anchorage" ) echo 'selected="selected"' ?> ><?php _e( '(GMT09:00) Alaska' ) ?></option>
            <option value="America/Ensenada" <?php if( $rtec_timezone == "America/Ensenada" ) echo 'selected="selected"' ?> ><?php _e( '(GMT08:00) Tijuana, Baja California' ) ?></option>
            <option value="Etc/GMT+8" <?php if( $rtec_timezone == "Etc/GMT+8" ) echo 'selected="selected"' ?> ><?php _e( '(GMT08:00) Pitcairn Islands' ) ?></option>
            <option value="America/Los_Angeles" <?php if( $rtec_timezone == "America/Los_Angeles" ) echo 'selected="selected"' ?> ><?php _e( '(GMT08:00) Pacific Time (US & Canada)' ) ?></option>
            <option value="America/Denver" <?php if( $rtec_timezone == "America/Denver" ) echo 'selected="selected"' ?> ><?php _e( '(GMT07:00) Mountain Time (US & Canada)' ) ?></option>
            <option value="America/Chihuahua" <?php if( $rtec_timezone == "America/Chihuahua" ) echo 'selected="selected"' ?> ><?php _e( '(GMT07:00) Chihuahua, La Paz, Mazatlan' ) ?></option>
            <option value="America/Dawson_Creek" <?php if( $rtec_timezone == "America/Dawson_Creek" ) echo 'selected="selected"' ?> ><?php _e( '(GMT07:00) Arizona' ) ?></option>
            <option value="America/Belize" <?php if( $rtec_timezone == "America/Belize" ) echo 'selected="selected"' ?> ><?php _e( '(GMT06:00) Saskatchewan, Central America' ) ?></option>
            <option value="America/Cancun" <?php if( $rtec_timezone == "America/Cancun" ) echo 'selected="selected"' ?> ><?php _e( '(GMT06:00) Guadalajara, Mexico City, Monterrey' ) ?></option>
            <option value="Chile/EasterIsland" <?php if( $rtec_timezone == "Chile/EasterIsland" ) echo 'selected="selected"' ?> ><?php _e( '(GMT06:00) Easter Island' ) ?></option>
            <option value="America/Chicago" <?php if( $rtec_timezone == "America/Chicago" ) echo 'selected="selected"' ?> ><?php _e( '(GMT06:00) Central Time (US & Canada)' ) ?></option>
            <option value="America/New_York" <?php if( $rtec_timezone == "America/New_York" ) echo 'selected="selected"' ?> ><?php _e( '(GMT05:00) Eastern Time (US & Canada)' ) ?></option>
            <option value="America/Havana" <?php if( $rtec_timezone == "America/Havana" ) echo 'selected="selected"' ?> ><?php _e( '(GMT05:00) Cuba' ) ?></option>
            <option value="America/Bogota" <?php if( $rtec_timezone == "America/Bogota" ) echo 'selected="selected"' ?> ><?php _e( '(GMT05:00) Bogota, Lima, Quito, Rio Branco' ) ?></option>
            <option value="America/Caracas" <?php if( $rtec_timezone == "America/Caracas" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:30) Caracas' ) ?></option>
            <option value="America/Santiago" <?php if( $rtec_timezone == "America/Santiago" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) Santiago' ) ?></option>
            <option value="America/La_Paz" <?php if( $rtec_timezone == "America/La_Paz" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) La Paz' ) ?></option>
            <option value="Atlantic/Stanley" <?php if( $rtec_timezone == "Atlantic/Stanley" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) Faukland Islands' ) ?></option>
            <option value="America/Campo_Grande" <?php if( $rtec_timezone == "America/Campo_Grande" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) Brazil' ) ?></option>
            <option value="America/Goose_Bay" <?php if( $rtec_timezone == "America/Goose_Bay" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) Atlantic Time (Goose Bay)' ) ?></option>
            <option value="America/Glace_Bay" <?php if( $rtec_timezone == "America/Glace_Bay" ) echo 'selected="selected"' ?> ><?php _e( '(GMT04:00) Atlantic Time (Canada)' ) ?></option>
            <option value="America/St_Johns" <?php if( $rtec_timezone == "America/St_Johns" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:30) Newfoundland' ) ?></option>
            <option value="America/Araguaina" <?php if( $rtec_timezone == "America/Araguaina" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) UTC3' ) ?></option>
            <option value="America/Montevideo" <?php if( $rtec_timezone == "America/Montevideo" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) Montevideo' ) ?></option>
            <option value="America/Miquelon" <?php if( $rtec_timezone == "America/Miquelon" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) Miquelon, St. Pierre' ) ?></option>
            <option value="America/Godthab" <?php if( $rtec_timezone == "America/Godthab" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) Greenland' ) ?></option>
            <option value="America/Argentina/Buenos_Aires" <?php if( $rtec_timezone == "America/Argentina/Buenos_Aires" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) Buenos Aires' ) ?></option>
            <option value="America/Sao_Paulo" <?php if( $rtec_timezone == "America/Sao_Paulo" ) echo 'selected="selected"' ?> ><?php _e( '(GMT03:00) Brasilia' ) ?></option>
            <option value="America/Noronha" <?php if( $rtec_timezone == "America/Noronha" ) echo 'selected="selected"' ?> ><?php _e( '(GMT02:00) MidAtlantic' ) ?></option>
            <option value="Atlantic/Cape_Verde" <?php if( $rtec_timezone == "Atlantic/Cape_Verde" ) echo 'selected="selected"' ?> ><?php _e( '(GMT01:00) Cape Verde Is.' ) ?></option>
            <option value="Atlantic/Azores" <?php if( $rtec_timezone == "Atlantic/Azores" ) echo 'selected="selected"' ?> ><?php _e( '(GMT01:00) Azores' ) ?></option>
            <option value="Europe/Belfast" <?php if( $rtec_timezone == "Europe/Belfast" ) echo 'selected="selected"' ?> ><?php _e( '(GMT) Greenwich Mean Time : Belfast' ) ?></option>
            <option value="Europe/Dublin" <?php if( $rtec_timezone == "Europe/Dublin" ) echo 'selected="selected"' ?> ><?php _e( '(GMT) Greenwich Mean Time : Dublin' ) ?></option>
            <option value="Europe/Lisbon" <?php if( $rtec_timezone == "Europe/Lisbon" ) echo 'selected="selected"' ?> ><?php _e( '(GMT) Greenwich Mean Time : Lisbon' ) ?></option>
            <option value="Europe/London" <?php if( $rtec_timezone == "Europe/London" ) echo 'selected="selected"' ?> ><?php _e( '(GMT) Greenwich Mean Time : London' ) ?></option>
            <option value="Africa/Abidjan" <?php if( $rtec_timezone == "Africa/Abidjan" ) echo 'selected="selected"' ?> ><?php _e( '(GMT) Monrovia, Reykjavik' ) ?></option>
            <option value="Europe/Amsterdam" <?php if( $rtec_timezone == "Europe/Amsterdam" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna' ) ?></option>
            <option value="Europe/Belgrade" <?php if( $rtec_timezone == "Europe/Belgrade" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague' ) ?></option>
            <option value="Europe/Brussels" <?php if( $rtec_timezone == "Europe/Brussels" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris' ) ?></option>
            <option value="Africa/Algiers" <?php if( $rtec_timezone == "Africa/Algiers" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+01:00) West Central Africa' ) ?></option>
            <option value="Africa/Windhoek" <?php if( $rtec_timezone == "Africa/Windhoek" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+01:00) Windhoek' ) ?></option>
            <option value="Asia/Beirut" <?php if( $rtec_timezone == "Asia/Beirut" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Beirut' ) ?></option>
            <option value="Africa/Cairo" <?php if( $rtec_timezone == "Africa/Cairo" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Cairo' ) ?></option>
            <option value="Asia/Gaza" <?php if( $rtec_timezone == "Asia/Gaza" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Gaza' ) ?></option>
            <option value="Africa/Blantyre" <?php if( $rtec_timezone == "Africa/Blantyre" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Harare, Pretoria' ) ?></option>
            <option value="Asia/Jerusalem" <?php if( $rtec_timezone == "Asia/Jerusalem" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Jerusalem' ) ?></option>
            <option value="Europe/Minsk" <?php if( $rtec_timezone == "Europe/Minsk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Minsk' ) ?></option>
            <option value="Asia/Damascus" <?php if( $rtec_timezone == "Asia/Damascus" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+02:00) Syria' ) ?></option>
            <option value="Europe/Moscow" <?php if( $rtec_timezone == "Europe/Moscow" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+03:00) Moscow, St. Petersburg, Volgograd' ) ?></option>
            <option value="Africa/Addis_Ababa" <?php if( $rtec_timezone == "Africa/Addis_Ababa" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+03:00) Nairobi' ) ?></option>
            <option value="Asia/Tehran" <?php if( $rtec_timezone == "Asia/Tehran" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+03:30) Tehran' ) ?></option>
            <option value="Asia/Dubai" <?php if( $rtec_timezone == "Asia/Dubai" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+04:00) Abu Dhabi, Muscat' ) ?></option>
            <option value="Asia/Yerevan" <?php if( $rtec_timezone == "Asia/Yerevan" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+04:00) Yerevan' ) ?></option>
            <option value="Asia/Kabul" <?php if( $rtec_timezone == "Asia/Kabul" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+04:30) Kabul' ) ?></option>
            <option value="Asia/Yekaterinburg" <?php if( $rtec_timezone == "Asia/Yekaterinburg" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+05:00) Ekaterinburg' ) ?></option>
            <option value="Asia/Tashkent" <?php if( $rtec_timezone == "Asia/Tashkent" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+05:00) Tashkent' ) ?></option>
            <option value="Asia/Kolkata" <?php if( $rtec_timezone == "Asia/Kolkata" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi' ) ?></option>
            <option value="Asia/Katmandu" <?php if( $rtec_timezone == "Asia/Katmandu" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+05:45) Kathmandu' ) ?></option>
            <option value="Asia/Dhaka" <?php if( $rtec_timezone == "Asia/Dhaka" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+06:00) Astana, Dhaka' ) ?></option>
            <option value="Asia/Novosibirsk" <?php if( $rtec_timezone == "Asia/Novosibirsk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+06:00) Novosibirsk' ) ?></option>
            <option value="Asia/Rangoon" <?php if( $rtec_timezone == "Asia/Rangoon" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+06:30) Yangon (Rangoon)' ) ?></option>
            <option value="Asia/Bangkok" <?php if( $rtec_timezone == "Asia/Bangkok" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+07:00) Bangkok, Hanoi, Jakarta' ) ?></option>
            <option value="Asia/Krasnoyarsk" <?php if( $rtec_timezone == "Asia/Krasnoyarsk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+07:00) Krasnoyarsk' ) ?></option>
            <option value="Asia/Hong_Kong" <?php if( $rtec_timezone == "Asia/Hong_Kong" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi' ) ?></option>
            <option value="Asia/Irkutsk" <?php if( $rtec_timezone == "Asia/Irkutsk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+08:00) Irkutsk, Ulaan Bataar' ) ?></option>
            <option value="Australia/Perth" <?php if( $rtec_timezone == "Australia/Perth" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+08:00) Perth' ) ?></option>
            <option value="Australia/Eucla" <?php if( $rtec_timezone == "Australia/Eucla" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+08:45) Eucla' ) ?></option>
            <option value="Asia/Tokyo" <?php if( $rtec_timezone == "Asia/Tokyo" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+09:00) Osaka, Sapporo, Tokyo' ) ?></option>
            <option value="Asia/Seoul" <?php if( $rtec_timezone == "Asia/Seoul" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+09:00) Seoul' ) ?></option>
            <option value="Asia/Yakutsk" <?php if( $rtec_timezone == "Asia/Yakutsk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+09:00) Yakutsk' ) ?></option>
            <option value="Australia/Adelaide" <?php if( $rtec_timezone == "Australia/Adelaide" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+09:30) Adelaide' ) ?></option>
            <option value="Australia/Darwin" <?php if( $rtec_timezone == "Australia/Darwin" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+09:30) Darwin' ) ?></option>
            <option value="Australia/Brisbane" <?php if( $rtec_timezone == "Australia/Brisbane" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+10:00) Brisbane' ) ?></option>
            <option value="Australia/Hobart" <?php if( $rtec_timezone == "Australia/Hobart" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+10:00) Sydney' ) ?></option>
            <option value="Asia/Vladivostok" <?php if( $rtec_timezone == "Asia/Vladivostok" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+10:00) Vladivostok' ) ?></option>
            <option value="Australia/Lord_Howe" <?php if( $rtec_timezone == "Australia/Lord_Howe" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+10:30) Lord Howe Island' ) ?></option>
            <option value="Etc/GMT11" <?php if( $rtec_timezone == "Etc/GMT11" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+11:00) Solomon Is., New Caledonia' ) ?></option>
            <option value="Asia/Magadan" <?php if( $rtec_timezone == "Asia/Magadan" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+11:00) Magadan' ) ?></option>
            <option value="Pacific/Norfolk" <?php if( $rtec_timezone == "Pacific/Norfolk" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+11:30) Norfolk Island' ) ?></option>
            <option value="Asia/Anadyr" <?php if( $rtec_timezone == "Asia/Anadyr" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+12:00) Anadyr, Kamchatka' ) ?></option>
            <option value="Pacific/Auckland" <?php if( $rtec_timezone == "Pacific/Auckland" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+12:00) Auckland, Wellington' ) ?></option>
            <option value="Etc/GMT12" <?php if( $rtec_timezone == "Etc/GMT12" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+12:00) Fiji, Kamchatka, Marshall Is.' ) ?></option>
            <option value="Pacific/Chatham" <?php if( $rtec_timezone == "Pacific/Chatham" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+12:45) Chatham Islands' ) ?></option>
            <option value="Pacific/Tongatapu" <?php if( $rtec_timezone == "Pacific/Tongatapu" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+13:00) Nuku\'alofa' ) ?></option>
            <option value="Pacific/Kiritimati" <?php if( $rtec_timezone == "Pacific/Kiritimati" ) echo 'selected="selected"' ?> ><?php _e( '(GMT+14:00) Kiritimati' ) ?></option>
        </select>
    <?php
    }

    public function customize_custom_date_format( $args )
    {
        $options = get_option( $args['option'] );
        $default = isset( $args['default'] ) ? esc_attr( $args['default'] ) : '';
        $option_string = ( isset( $options[ $args['name'] ] ) ) ? esc_attr( $options[ $args['name'] ] ) : $default;
        ?>
        <input name="<?php echo $args['option'].'['.$args['name'].']'; ?>" id="rtec_<?php echo $args['name']; ?>" type="text" value="<?php esc_attr_e( $option_string ); ?>" size="10" placeholder="Eg. F jS, Y" />
        <a href="https://www.roundupwp.com/products/registrations-for-the-events-calendar/docs/date-formatting-guide/" target="_blank"><?php _e( 'Examples' , 'rtec'); ?></a>
        <br><span class="description"><?php esc_attr_e( $args['description'], 'rtec' ); ?></span>
        <?php
    }

    /**
     * Makes creating settings easier
     * 
     * @param array $args   extra arguments to create parts of the form fields
     */
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
     * @since 1.0
     */
    public function validate_options( $input )
    {
        $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'registrations';
        
        $updated_options = get_option( 'rtec_options', false );
        $checkbox_settings = array();
        $leave_spaces = array();

        if ( isset( $input['default_max_registrations'] ) ) {
            $checkbox_settings = array( 'first_show', 'first_require', 'last_show', 'last_require', 'email_show', 'email_require', 'phone_show', 'phone_require', 'other_show', 'other_require', 'recaptcha_require', 'limit_registrations', 'include_attendance_message', 'preserve_db' );
            $leave_spaces = array( 'custom_js', 'custom_css' );
        } elseif ( isset( $input['confirmation_message'] ) ) {
            $checkbox_settings = array( 'disable_notification', 'disable_confirmation', 'use_custom_notification' );
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
     * @since 1.0
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

/**
 * Create the admin menus and pages
 * 
 * @since 1.0
 */
function RTEC_ADMIN() {
    $admin = new RTEC_Admin;
}
RTEC_ADMIN();