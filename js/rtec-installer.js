/* global rtec_admin, jconfirm, wpCookies, Choices, List */

(function($) {

    'use strict';

    // Global settings access.
    var s;

    // Admin object.
    var RtecAdmin = {

        // Settings.
        settings: {
            iconActivate: '<i class="fa fa-toggle-on fa-flip-horizontal" aria-hidden="true"></i>',
            iconDeactivate: '<i class="fa fa-toggle-on" aria-hidden="true"></i>',
            iconInstall: '<i class="fa fa-cloud-download" aria-hidden="true"></i>',
            iconSpinner: '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>',
            mediaFrame: false
        },

        /**
         * Start the engine.
         *
         * @since 1.3.9
         */
        init: function() {

            // Settings shortcut.
            s = this.settings;

            // Document ready.
            $( document ).ready( RtecAdmin.ready );

            // Addons List.
            RtecAdmin.initAddons();
        },

        /**
         * Document ready.
         *
         * @since 1.3.9
         */
        ready: function() {

            // Action available for each binding.
            $( document ).trigger( 'rtecReady' );
        },

        //--------------------------------------------------------------------//
        // Addons List.
        //--------------------------------------------------------------------//

        /**
         * Element bindings for Addons List page.
         *
         * @since 1.3.9
         */
        initAddons: function() {

            // Some actions have to be delayed to document.ready.
            $( document ).on( 'rtecReady', function() {

                // Only run on the addons page.
                if ( ! $( '#rtec-admin-addons' ).length ) {
                    return;
                }

                // Display all addon boxes as the same height.
                if( $( '#rtec-admin-about.rtec-admin-wrap').length ){
                    $( '#rtec-admin-about .addon-item .details' ).matchHeight( { byrow: false, property: 'height' } );
                }

                // Addons searching.
                if ( $('#rtec-admin-addons-list').length ) {
                    var addonSearch = new List( 'rtec-admin-addons-list', {
                        valueNames: [ 'addon-name' ]
                    } );

                    $( '#rtec-admin-addons-search' ).on( 'keyup', function () {
                        var searchTerm = $( this ).val(),
                            $heading = $( '#addons-heading' );

                        if ( searchTerm ) {
                            $heading.text( rtec_admin.addon_search );
                        }
                        else {
                            $heading.text( $heading.data( 'text' ) );
                        }

                        addonSearch.search( searchTerm );
                    } );
                }
            });

            // Toggle an addon state.
            $( document ).on( 'click', '#rtec-admin-addons .addon-item button', function( event ) {

                event.preventDefault();

                if ( $( this ).hasClass( 'disabled' ) ) {
                    return false;
                }

                RtecAdmin.addonToggle( $( this ) );
            });
        },

        /**
         * Toggle addon state.
         *
         * @since 1.3.9
         */
        addonToggle: function( $btn ) {

            var $addon = $btn.closest( '.addon-item' ),
                plugin = $btn.attr( 'data-plugin' ),
                plugin_type = $btn.attr( 'data-type' ),
                action,
                cssClass,
                statusText,
                buttonText,
                errorText,
                successText;

            if ( $btn.hasClass( 'status-go-to-url' ) ) {
                // Open url in new tab.
                window.open( $btn.attr('data-plugin'), '_blank' );
                return;
            }

            $btn.prop( 'disabled', true ).addClass( 'loading' );
            $btn.html( s.iconSpinner );

            if ( $btn.hasClass( 'status-active' ) ) {
                // Deactivate.
                action     = 'rtec_deactivate_addon';
                cssClass   = 'status-inactive';
                if ( plugin_type === 'plugin' ) {
                    cssClass += ' button button-secondary';
                }
                statusText = rtec_admin.addon_inactive;
                buttonText = rtec_admin.addon_activate;
                if ( plugin_type === 'addon' ) {
                    buttonText = s.iconActivate + buttonText;
                }
                errorText  = s.iconDeactivate + rtec_admin.addon_deactivate;

            } else if ( $btn.hasClass( 'status-inactive' ) ) {
                // Activate.
                action     = 'rtec_activate_addon';
                cssClass   = 'status-active';
                if ( plugin_type === 'plugin' ) {
                    cssClass += ' button button-secondary disabled';
                }
                statusText = rtec_admin.addon_active;
                buttonText = rtec_admin.addon_deactivate;
                if ( plugin_type === 'addon' ) {
                    buttonText = s.iconDeactivate + buttonText;
                } else if ( plugin_type === 'plugin' ) {
                    buttonText = rtec_admin.addon_activated;
                }
                errorText  = s.iconActivate + rtec_admin.addon_activate;

            } else if ( $btn.hasClass( 'status-download' ) ) {
                // Install & Activate.
                action   = 'rtec_install_addon';
                cssClass = 'status-active';
                if ( plugin_type === 'plugin' ) {
                    cssClass += ' button disabled';
                }
                statusText = rtec_admin.addon_active;
                buttonText = rtec_admin.addon_activated;
                if ( plugin_type === 'addon' ) {
                    buttonText = s.iconActivate + rtec_admin.addon_deactivate;
                }
                errorText = s.iconInstall + rtec_admin.addon_activate;
                var noticeHtml = '<div class="rtec-addon-patience" style="' +
                    '    background-color: #f7f7f7;' +
                    '    padding: 20px;' +
                    '    position: relative;' +
                    '">' +
                    rtec_admin.thanks_patience + ' :)' +
                    '</div>';
                $addon.append(noticeHtml);

            } else {
                return;
            }

            var data = {
                action: action,
                nonce : rtec_admin.nonce,
                plugin: plugin,
                type  : plugin_type
            };

            $.post( rtec_admin.ajax_url, data, function( res ) {
                $addon.find( '.rtec-addon-patience' ).remove();
                if ( res.success ) {
                    if ( 'rtec_install_addon' === action ) {
                        $btn.attr( 'data-plugin', res.data.basename );
                        successText = res.data.msg;
                        if ( ! res.data.is_activated ) {
                            cssClass = 'status-inactive';
                            if ( plugin_type === 'plugin' ) {
                                cssClass = 'button';
                            }
                            statusText = rtec_admin.addon_inactive;
                            buttonText = s.iconActivate + rtec_admin.addon_activate;
                        }
                    } else {
                        successText = res.data;
                    }
                    $addon.find( '.actions' ).append( '<div class="msg success">'+successText+'</div>' );
                    $addon.find( 'span.status-label' )
                        .removeClass( 'status-active status-inactive status-download' )
                        .addClass( cssClass )
                        .removeClass( 'button button-primary button-secondary disabled' )
                        .text( statusText );
                    $btn
                        .removeClass( 'status-active status-inactive status-download' )
                        .removeClass( 'button button-primary button-secondary disabled' )
                        .addClass( cssClass ).html( buttonText );

                    window.location.reload();
                } else {
                    if ( 'download_failed' === res.data[0].code ) {
                        if ( plugin_type === 'addon' ) {
                            $addon.find( '.actions' ).append( '<div class="msg error">'+rtec_admin.addon_error+'</div>' );
                        } else {
                            $addon.find( '.actions' ).append( '<div class="msg error">'+rtec_admin.plugin_error+'</div>' );
                        }
                    } else {
                        $addon.find( '.actions' ).append( '<div class="msg error">'+res.data+'</div>' );
                    }
                    $btn.html( errorText );
                }

                $btn.prop( 'disabled', false ).removeClass( 'loading' );

                // Automatically clear addon messages after 3 seconds.
                setTimeout( function() {
                    $( '.addon-item .msg' ).remove();
                }, 3000 );

            }).fail( function( xhr ) {
                console.log( xhr.responseText );
            });
        },

    };

    RtecAdmin.init();

    window.RtecAdmin = RtecAdmin;

})( jQuery );