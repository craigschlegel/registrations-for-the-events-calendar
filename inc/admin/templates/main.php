<div class="wrap rtec-admin-wrap">
    <h1><?php _e( 'Registrations for the Events Calendar', 'registrations-for-the-events-calendar' ); ?></h1>
    <?php
    if ( ! defined( 'ABSPATH' ) ) {
        die( '-1' );
    }
    // this controls which view is included based on the selected tab
    $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'registrations';

    $additional_tabs = array();
    $additional_tabs = apply_filters( 'rtec_admin_additional_tabs', $additional_tabs );
    $active_tab = RTEC_Admin::get_active_tab( $tab, $additional_tabs );

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
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations" class="nav-tab <?php if ( $active_tab == 'registrations' || $active_tab == 'single' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Registrations', 'registrations-for-the-events-calendar' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=form" class="nav-tab <?php if ( $active_tab == 'form' || $active_tab == 'create' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Form', 'registrations-for-the-events-calendar' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=email" class="nav-tab <?php if( $active_tab == 'email' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Email', 'registrations-for-the-events-calendar' ); ?></a>
            <?php foreach ( $additional_tabs as $additional_tab ) :
                $label = isset( $additional_tab['label'] ) ? $additional_tab['label'] : '';
                $value = isset( $additional_tab['value'] ) ? $additional_tab['value'] : false;
                ?>
                <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=<?php echo urlencode( $value ); ?>" class="nav-tab <?php if( $active_tab == $value ){ echo 'nav-tab-active'; } ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=support" class="nav-tab <?php if( $active_tab == 'support' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Support', 'registrations-for-the-events-calendar' ); ?></a>
            <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=license" class="nav-tab <?php if( $active_tab == 'license' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'License', 'registrations-for-the-events-calendar' ); ?></a>

        </h2>
        <?php
        if ( $active_tab === 'email' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/email.php';
        } elseif ( $active_tab === 'form' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/form.php';
        } elseif ( $active_tab === 'create' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/create.php';
        } elseif ( $active_tab === 'support' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/support.php';
        } elseif ( $active_tab === 'single' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/single.php';
        } elseif ( $active_tab === 'license' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/license.php';
        } else {
            $default = true;
            foreach ( $additional_tabs as $additional_tab ) {
                $value = isset( $additional_tab['value'] ) ? $additional_tab['value'] : false;
                if ( $active_tab === $value ) {
                    $default = false;
                    do_action( 'rtec_the_tab_html_' . $additional_tab['value'] );
                }
            }
            if ( $default ) {
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
</div>