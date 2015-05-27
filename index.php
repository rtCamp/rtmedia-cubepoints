<?php

/*
  Plugin Name: rtMedia CubePoints
  Plugin URI: https://rtcamp.com/products/rtmedia-cubepoints/
  Description: This plugin provides CubePoints integration with rtMedia.
  Version: 1.0
  Author: rtCamp
  Text Domain: rtmedia
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=rtmedia-cubepoints
 */

if( !defined( 'RTMEDIA_CUBEPOINTS_PATH' ) ) {
	/**
	 *  The server file system path to the plugin directory
	 */
	define( 'RTMEDIA_CUBEPOINTS_PATH', plugin_dir_path( __FILE__ ) );
}

if( !defined( 'RTMEDIA_CUBEPOINTS_URL' ) ) {
	/**
	 * The url to the plugin directory
	 */
	define( 'RTMEDIA_CUBEPOINTS_URL', plugin_dir_url( __FILE__ ) );
}

if( !defined( 'RTMEDIA_CUBEPOINTS_BASE_NAME' ) ) {
	/**
	 * The base name of the plugin directory
	 */
	define( 'RTMEDIA_CUBEPOINTS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if( !defined( 'RTMEDIA_CUBEPOINTS_VERSION' ) ) {
    /**
	 * The version of the plugin
	 */
	define( 'RTMEDIA_CUBEPOINTS_VERSION', '1.0' );
}

if( !defined( 'EDD_RTMEDIA_CUBEPOINTS_STORE_URL' ) ) {
	// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	define( 'EDD_RTMEDIA_CUBEPOINTS_STORE_URL', 'https://rtcamp.com/' );
}

if( !defined( 'EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME' ) ) {
	// the name of your product. This should match the download name in EDD exactly
	define( 'EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME', 'rtMedia CubePoints' );
}

// define RTMEDIA_DEBUG to true in wp-config.php to debug updates
if( defined( 'RTMEDIA_DEBUG' ) && RTMEDIA_DEBUG === true ){
    set_site_transient( 'update_plugins', null );
}

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rtmedia_cubepoints_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/main/controllers/media/' . $class_name . '.php',
	);
    
	foreach( $rtlibpath as $path ) {
		$path = RTMEDIA_CUBEPOINTS_PATH . $path;
		if ( file_exists( $path ) ){
			include $path;
            
			break;
		}
	}
}

function rtmedia_cubepoints_loader( $class_construct ) {

	/*
	 * do not load classes of rtMedia Pro is activated
	 * as it might break some functionality
	 */
	if( defined( 'RTMEDIA_PRO_PATH' ) ){
                add_action( 'admin_notices', 'rtmedia_pubepoints_pro_active_notice' );
		return $class_construct;
	}

    require_once RTMEDIA_CUBEPOINTS_PATH . 'app/RTMediaCubePoints.php';
    
    $class_construct[ 'CubePoints' ] = false;
    $class_construct[ 'CubePointsMedia' ] = false;
    
    return $class_construct;
}
function rtmedia_pubepoints_pro_active_notice(){
	?>
		<div class="error">
			<p>
				<strong>rtMedia CubePoints </strong> plugin is not effective as rtMedia Pro is active. Please deactivate rtMedia Pro in order to make rtMedia Cube Points plugin work.
			</p>
		</div>
	<?php
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rtmedia_cubepoints_autoloader' );
add_filter( 'rtmedia_class_construct', 'rtmedia_cubepoints_loader' );

// EDD License
include_once RTMEDIA_CUBEPOINTS_PATH . 'lib/edd-license/RTMediaCubePointsEDDLicense.php';
new RTMediaCubePointsEDDLicense();

/*
 * One click install/activate rtMedia.
 */
include_once( RTMEDIA_CUBEPOINTS_PATH . 'lib/plugin-installer/RTMPluginInstaller.php' );

global $rtm_plugin_installer;

if( empty( $rtm_plugin_installer ) ) {
	$rtm_plugin_installer = new RTMPluginInstaller();
}
