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
    $tab = isset( $_GET["tab"] ) ? $_GET["tab"] : 'registrations';
    $active_tab = RegistrationsTEC\Admin::get_active_tab( $tab );
    ?>

<!-- Display the tabs along with styling for the 'active' tab -->
<h2 class="nav-tab-wrapper">
    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=registrations" class="nav-tab <?php if ( $active_tab == 'registrations' || $active_tab == 'single' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Registrations', 'registrationsTEC' ); ?></a>
    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=general" class="nav-tab <?php if ( $active_tab == 'general' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'General', 'registrationsTEC' ); ?></a>
    <a href="edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar%2F_settings&tab=email" class="nav-tab <?php if( $active_tab == 'email' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Email', 'registrationsTEC' ); ?></a>
</h2>
    <?php
        if ( $active_tab === 'email' ) {
            require_once RTEC_URL.'views/admin/e-mail.php';
        } elseif ( $active_tab === 'general' ){
            require_once RTEC_URL.'views/admin/general.php';
        } else {
            if ( $active_tab === 'single' ) {
                require_once RTEC_URL.'views/admin/single.php';
            } else {
                require_once RTEC_URL.'views/admin/registrations.php';
            }
        }
    ?>
</div>