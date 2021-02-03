<div class="wrap rtec-admin-wrap" id="rtec-admin-wrap">
	<?php
	$lite_notice_dismissed = get_transient( 'registrations_tec_dismiss_lite' );

	if ( ! $lite_notice_dismissed ) :
		?>
        <div id="rtec-notice-bar" style="display:none">
            <span class="rtec-notice-bar-message"><?php _e( 'You\'re using Registrations for the Events Calendar Lite. To unlock more features consider <a href="https://roundupwp.com/products/registrations-for-the-events-calendar-pro/" target="_blank" rel="noopener noreferrer">upgrading to Pro</a>.', 'registrations-for-the-events-calendar'); ?></span>
            <button type="button" class="dismiss" title="<?php _e( 'Dismiss this message.', 'registrations-for-the-events-calendar' ); ?>" data-page="overview">
            </button>
        </div>
	<?php endif; ?>
    <h1><?php _e( 'Registrations for the Events Calendar', 'registrations-for-the-events-calendar' ); ?></h1>
    <?php
    if ( ! defined( 'ABSPATH' ) ) {
        die( '-1' );
    }
    if ( ! is_callable( 'tribe_get_events' ) ) {

	        ?>

            <div id="rtec-admin-addons">
                <div class="rtec-notice">
                    <h3><?php echo __( 'Thank You for Installing Our Plugin!', 'registrations-for-the-events-calendar' ); ?></h3>
                    <p><?php _e( 'Registrations for the Events Calendar requires The Events Calendar to be installed and active.', 'registrations-for-the-events-calendar' ); ?></p>
                </div>
                <div class="addons-container">

			        <?php
                    if ( version_compare( PHP_VERSION,  '5.3.0', '<=' )
                         || version_compare( get_bloginfo( 'version' ), '4.6' , '<=' ) ) : ?>
                        <a href="plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true" class="button button-primary" title="<?php echo esc_html__( 'The Events Calendar', 'registrations-for-the-events-calendar' ); ?>"><?php echo esc_html__( 'Click here to install the latest version of', 'registrations-for-the-events-calendar' ); ?> <?php echo esc_html__( 'The Events Calendar', 'registrations-for-the-events-calendar' ); ?></a>
                    <?php
                    else :

                    $version_string = version_compare( PHP_VERSION,  '5.6.0', '<' ) ? '.4.6.23' : RTEC_TEC_VER_STRING;

			        $plugin      = array(
				        'icon' => RTEC_PLUGIN_URL . '/img/tec-icon.png',
				        'name' => esc_html__( 'The Events Calendar', 'registrations-for-the-events-calendar' ),
				        'desc' => esc_html__( 'A fully featured, immensely popular calendar solution from Modern Tribe. Registrations for The Events Calendar by Roundup WP adds registration features to The Events Calendar.', 'registrations-for-the-events-calendar' ),
				        'url'  => 'https://downloads.wordpress.org/plugin/the-events-calendar' . $version_string . '.zip',
			        );
			        $all_plugins = get_plugins();

			        $plugin_data = RTEC_Admin::get_plugin_data( 'the-events-calendar/the-events-calendar.php', $plugin, $all_plugins );

			        ?>
                    <div class="addon-container">
                        <div class="addon-item">
                            <div class="details rtec-clear">
                                <img src="<?php echo esc_url( $plugin_data['details']['icon'] ); ?>">
                                <h5 class="addon-name">
							        <?php echo esc_html( $plugin_data['details']['name'] ); ?>
                                </h5>
                                <p class="addon-desc">
							        <?php echo wp_kses_post( $plugin_data['details']['desc'] ); ?>
                                    <a href="https://wordpress.org/plugins/the-events-calendar/" target="_blank"
                                       rel="noopener noreferrer"><?php esc_html_e( 'Plugin Homepage', 'registrations-for-the-events-calendar' ); ?></a>
                                </p>
                            </div>
                            <div class="actions rtec-clear">
                                <div class="status">
                                    <strong>
								        <?php
								        printf(
								        /* translators: %s - addon status label. */
									        esc_html__( 'Status: %s', 'registrations-for-the-events-calendar' ),
									        '<span class="status-label ' . esc_attr( $plugin_data['status_class'] ) . '">' . wp_kses_post( $plugin_data['status_text'] ) . '</span>'
								        );
								        ?>
                                    </strong>
                                </div>
                                <div class="action-button">
                                    <button class="<?php echo esc_attr( $plugin_data['action_class'] ); ?>"
                                            data-plugin="<?php echo esc_attr( $plugin_data['plugin_src'] ); ?>"
                                            data-type="plugin">
								        <?php echo wp_kses_post( $plugin_data['action_text'] ); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
	        <?php
	    return false;
    }
    // this controls which view is included based on the selected tab
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : str_replace( 'rtec-', '', $_GET['page'] );

    $additional_tabs = array();
    $additional_tabs = apply_filters( 'rtec_admin_additional_tabs', $additional_tabs );
    $active_tab = RTEC_Admin::get_active_tab( $tab, $additional_tabs );

    $options = get_option( 'rtec_options' );
    $tz_offset = rtec_get_time_zone_offset();

    ?>

    <!-- Display the tabs along with styling for the 'active' tab -->
    <?php
    if ( current_user_can( 'manage_options' ) ) { ?>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo get_admin_url( null, 'admin.php?page=' . RTEC_MENU_SLUG ); ?>" class="nav-tab <?php if ( $active_tab == 'registrations' || $active_tab == 'single' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Registrations', 'registrations-for-the-events-calendar' ); ?></a>
            <a href="<?php echo get_admin_url( null, 'admin.php?page=rtec-form' ); ?>" class="nav-tab <?php if ( $active_tab == 'form' || $active_tab == 'create' ) { echo 'nav-tab-active'; } ?>"><?php _e( 'Form', 'registrations-for-the-events-calendar' ); ?></a>
            <a href="<?php echo get_admin_url( null, 'admin.php?page=rtec-email' ); ?>" class="nav-tab <?php if( $active_tab == 'email' || $active_tab == 'message-create' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Email', 'registrations-for-the-events-calendar' ); ?></a>
	        <?php foreach ( $additional_tabs as $additional_tab ) :
		        $label = isset( $additional_tab['label'] ) ? $additional_tab['label'] : '';
		        $value = isset( $additional_tab['value'] ) ? $additional_tab['value'] : false;
		        if ( $value === 'mailchimp' ) {
			        $link_href = get_admin_url( null, 'edit.php?post_type=tribe_events&page=registrations-for-the-events-calendar-pro%2F_settings&tab='.$value );
		        } else {
			        $link_href = get_admin_url( null, 'admin.php?page=rtec-'.$value );
		        }
		        ?>
                <a href="<?php echo $link_href; ?>" class="nav-tab <?php if( $active_tab == $value ){ echo 'nav-tab-active'; } ?>"><?php echo esc_html( $label ) ?></a>
	        <?php endforeach; ?>
            <a href="<?php echo get_admin_url( null, 'admin.php?page=rtec-support' ) ?>" class="nav-tab <?php if( $active_tab == 'support' ){ echo 'nav-tab-active'; } ?>"><?php _e( 'Support', 'registrations-for-the-events-calendar' ); ?></a>
        </h2>
        <?php
        if ( $active_tab === 'email' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/email.php';
        } elseif ( $active_tab === 'form' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/form.php';
        } elseif ( $active_tab === 'support' ){
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/support.php';
        } elseif ( $active_tab === 'single' ) {
            require_once RTEC_PLUGIN_DIR.'inc/admin/templates/single.php';
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

    $ad_text = array(
        '<span class="rtec-bold">Easily collect and manage payments.</span><span>Get paid for your events using PayPal.</span>',
        '<span class="rtec-bold">More forms. Lots of ways to customize.</span><span>Build them with our custom form building tool.</span>',
        '<span class="rtec-bold">Do you have a membership site?</span><span>See our features tailored for your needs in the Registrations for the Events Calendar Pro.',
	    '<span class="rtec-bold">More ways to follow up with your attendees.</span><span>Send event-wide emails right from the WordPress dashboard.</span>',
	    '<span class="rtec-bold">Tailor your settings for each event.</span><span>Custom forms, custom confirmation messages, custom response categories.</span>'
    );
    $random_ad_key = array_rand( $ad_text, 1 );
    $random_num = rand(0, 1);
    ?>
    <hr />
    <div id="rtec-admin-footer">
    <?php if ( $random_num === 1 ) : ?>
    <a href="https://roundupwp.com/products/registrations-for-the-events-calendar-pro/" target="_blank" class="rtec-pro-ad-wrap">
        <div class="rtec-pro-ad">
            <img src="<?php echo RTEC_PLUGIN_URL . 'img/RTEC-Pro-Logo-150x150.png'; ?>" alt="Registrations for the Events Calendar Pro">
            <div class="rtec-pro-copy">
                <span><?php echo $ad_text[ $random_ad_key ]; ?></span>
            </div>
        </div>
    </a>
    <?php else : ?>
        <a href="https://roundupwp.com/products/registrations-for-the-events-calendar-pro/" target="_blank" class="rtec-pro-ad-wrap">
            <div class="rtec-pro-features">
                <div class="rtec-pro-features-list">
                    <h3 class="rtec-pro-heading">Registrations for the Events Calendar <span>Pro</span></h3>
                    <div class="rtec-pro-features-list-items">
                        <?php
                        $features = array(
                            __( 'Create Multiple Forms', 'registrations-for-the-events-calendar' ),
	                        __( 'Collect Online Payments', 'registrations-for-the-events-calendar' ),
	                        __( 'Custom Email Templates', 'registrations-for-the-events-calendar' ),
	                        __( 'Flexible Event Costs', 'registrations-for-the-events-calendar' ),
	                        __( 'Send Automatic Messages', 'registrations-for-the-events-calendar' ),
	                        __( 'Check Attendees In', 'registrations-for-the-events-calendar' ),
	                        __( "List Member's Events", 'registrations-for-the-events-calendar' ),
	                        __( "Customizable Attendee Lists", 'registrations-for-the-events-calendar' ),
	                        __( "Edit Registration Entries", 'registrations-for-the-events-calendar' ),
	                        __( "Waiting Lists", 'registrations-for-the-events-calendar' ),
                        );
                        foreach( $features as $feature ) :
                        ?>
                            <div class="feature-feature-list-item"><span class="featured-feature-check"><i class="dashicons dashicons-yes"></i></span><span class="featured-feature-item"><?php echo esc_html( $feature ); ?></span></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="rtec-pro-cta-wrap">
                        <span class="rtec-pro-cta"><?php _e( 'Upgrade to Pro to Unlock These Features', 'registrations-for-the-events-calendar' ); ?></span>
                    </div>
                </div>
            </div>
        </a>
    <?php endif; ?>
    </div>
</div>