jQuery( '.rtm-plugin-installer' ).click( function( e ) {

    var is_install = ( jQuery( this ).data( 'mode' ) == 'install' );

    jQuery( '.rtmedia-not-installed-error' ).removeClass( 'error' );
    jQuery( '.rtmedia-not-installed-error' ).addClass( 'updated' );

    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> will be installed and activated. Please wait... <img src="' + rtmedia_plugin_installer_ajax_loader + '" />' );


    var param = {
        plugin_slug: jQuery( this ).data( 'slug' ),
        _ajax_nonce: jQuery( this ).data( 'nonce' )
    };

    if( is_install ) {
        param.action = 'rtm_plugin_installer_install_plugin';
    } else {
        param.action = 'rtm_plugin_installer_activate_plugin'
    }
    jQuery.post( rtmedia_plugin_installer_ajax_url, param, function( data ) {
        data = data.trim();

        if( data == "true" ) {
            if( is_install ) {
                jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> installed and activated successfully.' );
            } else {
                jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> activated successfully.' );
            }
            location.reload();
        } else {
            jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia:</b> There is some problem. Please try again.' );
        }
    } );

    e.preventDefault();
} );
