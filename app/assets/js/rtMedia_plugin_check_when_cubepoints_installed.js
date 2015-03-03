/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function install_rtmedia_plugins_when_cubepoints_installed( plugin_slug, action, rtm_nonce ) {
    jQuery( '.rtmedia-not-installed-error' ).removeClass( 'error' );
    jQuery( '.rtmedia-not-installed-error' ).addClass( 'updated' );
    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> will be installed and activated. Please wait... <img src="' + rtmedia_cubepoints_ajax_loader + '" />' );

    var param = {
        action: action,
        plugin_slug: plugin_slug,
		_ajax_nonce: rtm_nonce
    };

    jQuery.post( rtmedia_ajax_url, param, function( data ) {
	    data = data.trim();

	    if( data == "true" ) {
		    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> installed and activated successfully.' );
		    location.reload();
	    } else {
		    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia:</b> There is some problem. Please try again.' );
	    }
	} );
}

function activate_rtmedia_plugins_when_cubepoints_installed( path, action, rtm_nonce ) {
    jQuery( '.rtmedia-not-installed-error' ).removeClass( 'error' );
    jQuery( '.rtmedia-not-installed-error' ).addClass( 'updated' );
    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia</b> will be activated now. Please wait... <img src="' + rtmedia_cubepoints_ajax_loader + '" />' );

    var param = {
        action: action,
        path: path,
		_ajax_nonce: rtm_nonce
    };

    jQuery.post( rtmedia_ajax_url, param, function( data ) {
	    data = data.trim();

	    if( data == "true" ) {
            jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia:</b> rtMedia activated successfully.' );
            location.reload();
	    } else {
		    jQuery( '.rtmedia-not-installed-error p' ).html( '<b>rtMedia:</b> There is some problem. Please try again.' );
	    }
	} );
}
