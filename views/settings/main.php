<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/10/2016
 * Time: 10:06 PM
 */
?>
<div class="wrap">
    <h1>Registrations for the Events Calendar</h1>
    <?php
    // this controls which view is included based on the selected tab
    $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : '';
    $active_tab = Registrations_TEC\Settings::get_active_tab( $tab );
    ?>

<!-- Display the tabs along with styling for the 'active' tab -->
<h2 class="nav-tab-wrapper">
    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations" class="nav-tab <?php if($active_tab == 'registrations' || $active_tab == 'single'  ){echo 'nav-tab-active';} ?> "><?php _e('Registrations', 'registrationsTEC'); ?></a>
    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=settings" class="nav-tab <?php if($active_tab == 'settings'){echo 'nav-tab-active';} ?>"><?php _e('Settings', 'registrationsTEC'); ?></a>
</h2>
    <?php

    if( isset( $active_tab ) ) {
        if( $active_tab === 'registrations' ) {
            require_once RTEC_URL . 'views/settings/registrations.php';
        } elseif( $active_tab === 'single' ) {
            /*
            if( isset( $_GET["id"] ) ) {
                require_once RTEC_URL . 'includes/database/class.MySQL.php';
                $single_query = new RegistrationsTEC_MySQL();
                $options = get_option( 'registrationsTEC_general' );
                $multiple_locations_venue = $options["multiple_locations_venue"];
                $id = (int) $_GET["id"];
                $results = $single_query->get_long_single_event_registrations($id);
                $meta = get_post_meta( $id );
                $venue_meta = get_post_meta( $meta["_EventVenueID"][0] );
                $venue = $venue_meta["_VenueVenue"][0];
                if( $multiple_locations_venue != 'none' ) {
                    $multiple_locations_options = explode(', ', $options["multiple_locations_options"] );
                    $has_multiple_locations = RegistrationsTEC_Form::is_multiple_locations_venue( $id, $multiple_locations_venue );
                } else {
                    $has_multiple_locations = false;
                }
                $start_date = $single_query->get_formatted_event_start_time( $meta["_EventStartDate"][0] );
                require_once RTEC_URL.'views/admin/partial.single-registrations-view.php';
            }*/
        } elseif( $active_tab === 'settings' ) {
            require_once RTEC_URL.'views/settings/settings.php';
        }

    }
    ?>
</div>