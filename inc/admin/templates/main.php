<div class="wrap rtec-admin-wrap">
    <h1><?php _e( 'Registrations for the Events Calendar', 'registrations-for-the-events-calendar' ); ?></h1>

    <?php
    global $rtec_options;
    if ( isset( $rtec_options['default_max_registrations'] ) ) : ?>
        <div class="notice notice-info rtec-notice-all-admin is-dismissible">
            <div class="rtec-img-wrap">
                <img src="<?php echo RTEC_PLUGIN_URL . 'img/RTEC-Logo-150x150.png'; ?>" alt="Registrations for the Events Calendar">
            </div>
            <div class="rtec-msg-wrap">
                <p>Hey! First time using the Registrations plugin?</p>
                <p>A registration form is now added to all of your upcoming events.</p>
                <p>Go to the <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=form&goto=disable">"Form" tab</a> to change this or check out our <strong><a href="https://roundupwp.com/products/registrations-for-the-events-calendar/setup/#step4" target="_blank">Setup Instructions</a></strong> on our website for help setting your registrations up.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // this controls which view is included based on the selected tab
    $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'registrations';
    $active_tab = RTEC_Admin::get_active_tab( $tab );

    $options = get_option( 'rtec_options' );
    $WP_offset = get_option( 'gmt_offset' );

    if ( ! empty( $WP_offset ) ) {
        $tz_offset = $WP_offset * HOUR_IN_SECONDS;
    } else {
        $timezone = isset( $options['timezone'] ) ? $options['timezone'] : 'America/New_York';
        // use php DateTimeZone class to handle the date formatting and offsets
        $date_obj = new DateTime( date( 'm/d g:i a' ), new DateTimeZone( "UTC" ) );
        $date_obj->setTimeZone( new DateTimeZone( $timezone ) );
        $utc_offset = $date_obj->getOffset();
        $tz_offset = $utc_offset;
    }

    ?>

<!-- Display the tabs along with styling for the 'active' tab -->
    <?php
    if ( current_user_can( 'manage_options' ) ) { ?>
        <h2 class="nav-tab-wrapper">
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations" class="nav-tab <?php if ( $active_tab == 'registrations' || $active_tab == 'single' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Registrations', 'registrationsTEC' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=form" class="nav-tab <?php if ( $active_tab == 'form' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Form', 'registrationsTEC' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=email" class="nav-tab <?php if( $active_tab == 'email' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Email', 'registrationsTEC' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=support" class="nav-tab <?php if( $active_tab == 'support' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Support', 'registrationsTEC' ); ?></a>
        </h2>
    <?php
        if ( $active_tab === 'email' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/email.php';
        } elseif ( $active_tab === 'form' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/form.php';
        } elseif ( $active_tab === 'support' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/support.php';
        } else {
            if ( $active_tab === 'single' ) {
                require_once RTEC_PLUGIN_DIR.'inc/admin/templates/single.php';
            } else {
                require_once RTEC_PLUGIN_DIR.'inc/admin/templates/registrations.php';
            }
        }
    } else {
        if ( $active_tab === 'single' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/single.php';
        } else {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/registrations.php';
        }
    }

    ?>
    <hr />
    <a href="https://roundupwp.com/products/registrations-for-the-events-calendar-pro/" target="_blank" style="display: block; margin: 20px 0 0 0; float: left; clear: both;">
        <img src="<?php echo RTEC_PLUGIN_URL . 'img/rtec-pro-features.png'; ?>" alt="Registrations for the Events Calendar Pro">
    </a>
</div>
