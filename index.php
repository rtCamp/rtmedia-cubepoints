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
    require_once RTMEDIA_CUBEPOINTS_PATH . 'app/RTMediaCubePoints.php';
    
    $class_construct[ 'CubePoints' ] = false;
    $class_construct[ 'CubePointsMedia' ] = false;
    
    return $class_construct;
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rtmedia_cubepoints_autoloader' );
add_filter( 'rtmedia_class_construct', 'rtmedia_cubepoints_loader' );

// EDD License
include_once RTMEDIA_CUBEPOINTS_PATH . 'lib/edd-license/RTMediaCubePointsEDDLicense.php';
new RTMediaCubePointsEDDLicense();

/**
 * Install/activate rtMedia plugin
 */
if( !defined( 'RTMEDIA_PATH' ) ) {
	function rtmedia_plugins_enque_js_when_cubepoints_installed() {
		wp_enqueue_script( 'rtmedia-cubepoints-plugins', RTMEDIA_CUBEPOINTS_URL . "app/assets/js/rtMedia_plugin_check_when_cubepoints_installed.js", '', false, true );
		wp_localize_script( 'rtmedia-cubepoints-plugins', 'rtmedia_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'rtmedia-cubepoints-plugins', 'rtmedia_cubepoints_ajax_loader', admin_url( '/images/spinner.gif' ) );
	}

	add_action( 'admin_enqueue_scripts', 'rtmedia_plugins_enque_js_when_cubepoints_installed' );
	add_action( 'admin_notices', 'admin_notice_rtmedia_not_installed_when_cubepoints_installed' );
    add_action( 'admin_init', 'rtmedia_cubepoints_set_rtmedia_activation_variables' );
	add_action( 'wp_ajax_rtmedia_cubepoints_install_plugin', 'rtmedia_cubepoints_install_plugin_ajax', 10 );
	add_action( 'wp_ajax_rtmedia_cubepoints_activate_plugin', 'rtmedia_cubepoints_activate_plugin_ajax', 10 );
	rtmedia_cubepoints_plugin_upgrader_class();
}

function rtmedia_cubepoints_set_rtmedia_activation_variables() {
	/**
	 * Automatic install/activate rtMedia
	 */
	global $rtmedia_cubepoints_plugins;
	$rtmedia_cubepoints_plugins = array(
		'buddypress-media' => array(
			'project_type' => 'all', 
            'name'         => esc_html__( 'rtMedia for WordPress, BuddyPress and bbPress', 'rtmedia' ), 
            'active'       => is_plugin_active( 'rtMedia/index.php' ), 
            'filename'     => 'index.php',
		), 
        'rtMedia'          => array(
			'project_type' => 'all', 
            'name' => esc_html__( 'rtMedia for WordPress, BuddyPress and bbPress', 'rtmedia' ), 
            'active' => is_plugin_active( 'rtMedia/index.php' ), 
            'filename' => 'index.php',
		)
	);
}

function rtmedia_cubepoints_plugin_upgrader_class() {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	if ( !class_exists( 'RTMedia_Plugin_Upgrader_Skin' ) ) {
		class RTMedia_Plugin_Upgrader_Skin extends WP_Upgrader_Skin {
			function __construct( $args = array() ) {
				$defaults = array( 
                    'type'   => 'web', 
                    'url'    => '', 
                    'plugin' => '', 
                    'nonce'  => '', 
                    'title'  => '' 
                );
				$args = wp_parse_args( $args, $defaults );

				$this->type = $args[ 'type' ];
				$this->api  = isset( $args[ 'api' ] ) ? $args[ 'api' ] : array();

				parent::__construct( $args );
			}

			public function request_filesystem_credentials( $error = false, $context = false, $allow_relaxed_file_ownership = false ) {
				return true;
			}

			public function error( $errors ) {
				die( '-1' );
			}

			public function header() { }

			public function footer() { }

			public function feedback( $string ) { }
		}
	}
}

if( !function_exists( "admin_notice_rtmedia_not_installed_when_cubepoints_installed" ) ) {    
	function admin_notice_rtmedia_not_installed_when_cubepoints_installed() {
        if( current_user_can( "list_users" ) ) {
            global $rtmedia_not_installed_or_activated_admin_notice;
            
            $rtmedia_cubepoints_not_installed_msg_set = false;
        
            // Admin notice if rtMedia is not installed / activated
            if( !isset( $rtmedia_not_installed_or_activated_admin_notice ) && is_null( $rtmedia_not_installed_or_activated_admin_notice ) ) {
                $rtmedia_not_installed_or_activated_admin_notice = "<b>" . __( "rtMedia Add-on(s)", "rtmedia" ) . "</b> " . __( "will not work if you will not", "rtmedia" ) . " ";
                $rtmedia_cubepoints_not_installed_msg_set = true;
            }
            
            if( !is_rtmedia_plugin_installed_when_cubepoints_installed( 'buddypress-media' ) && !is_rtmedia_plugin_installed_when_cubepoints_installed( 'rtMedia' ) ) {
                $nonce = wp_create_nonce( 'rtmedia_cubepoints_install_plugin_buddypress-media' );
                
                if( $rtmedia_cubepoints_not_installed_msg_set ) {
                    $rtmedia_not_installed_or_activated_admin_notice .= __( "install rtMedia. Click", "rtmedia" ) . " ";
                    $rtmedia_not_installed_or_activated_admin_notice .= '<a href="#" onclick="install_rtmedia_plugins_when_cubepoints_installed( \'buddypress-media\', \'rtmedia_cubepoints_install_plugin\', \'' . $nonce . '\' );">' . __( "here", "rtmedia" ) . '</a> ';
                    $rtmedia_not_installed_or_activated_admin_notice .= __( 'to install rtMedia.', 'rtmedia' );
                }
            } else {
                if( is_rtmedia_plugin_installed_when_cubepoints_installed( 'buddypress-media' ) && !is_rtmedia_plugin_active_when_cubepoints_installed( 'buddypress-media' ) ) {
                    $path  = get_path_for_rtmedia_plugins_when_cubepoints_installed( 'buddypress-media' );
                    $nonce = wp_create_nonce( 'rtmedia_cubepoints_activate_plugin_' . $path );
                    
                    if( $rtmedia_cubepoints_not_installed_msg_set ) {
                        $rtmedia_not_installed_or_activated_admin_notice .= __( "activate buddypress-media. Click", "rtmedia" ) . " ";
                        $rtmedia_not_installed_or_activated_admin_notice .= '<a href="#" onclick="activate_rtmedia_plugins_when_cubepoints_installed( \'' . $path . '\', \'rtmedia_cubepoints_activate_plugin\', \'' . $nonce . '\' );">' . __( "here", "rtmedia" ) . '</a> ';
                        $rtmedia_not_installed_or_activated_admin_notice .= __( 'to activate rtMedia.', 'rtmedia' );
                    }
                }
                
                if( is_rtmedia_plugin_installed_when_cubepoints_installed( 'rtMedia' ) && !is_rtmedia_plugin_active_when_cubepoints_installed( 'rtMedia' ) ) {
                    $path  = get_path_for_rtmedia_plugins_when_cubepoints_installed( 'rtMedia' );
                    $nonce = wp_create_nonce( 'rtmedia_cubepoints_activate_plugin_' . $path );
                    
                    if( $rtmedia_cubepoints_not_installed_msg_set ) {
                        $rtmedia_not_installed_or_activated_admin_notice .= __( "activate rtMedia. Click", "rtmedia" ) . " ";
                        $rtmedia_not_installed_or_activated_admin_notice .= '<a href="#" onclick="activate_rtmedia_plugins_when_cubepoints_installed( \'' . $path . '\', \'rtmedia_cubepoints_activate_plugin\', \'' . $nonce . '\' );">' . __( "here", "rtmedia" ) . '</a> ';
                        $rtmedia_not_installed_or_activated_admin_notice .= __( 'to activate rtMedia.', 'rtmedia' );
                    }
                }
            }
            
            if( $rtmedia_cubepoints_not_installed_msg_set ) {
                ?>
                <div class="error rtmedia-not-installed-error">
                    <p>
                        <?php echo $rtmedia_not_installed_or_activated_admin_notice; ?>
                    </p>
                </div>
                <?php
            }
        }
	}
}

function get_path_for_rtmedia_plugins_when_cubepoints_installed( $slug ) {
	global $rtmedia_cubepoints_plugins;
	$filename = ( !empty( $rtmedia_cubepoints_plugins[ $slug ][ 'filename' ] ) ) ? $rtmedia_cubepoints_plugins[ $slug ][ 'filename' ] : $slug . '.php';

	return $slug . '/' . $filename;
}

function is_rtmedia_plugin_active_when_cubepoints_installed( $slug ) {
	global $rtmedia_cubepoints_plugins;
	if( empty( $rtmedia_cubepoints_plugins[ $slug ] ) ) {
		return false;
	}

	return $rtmedia_cubepoints_plugins[ $slug ][ 'active' ];
}

function is_rtmedia_plugin_installed_when_cubepoints_installed( $slug ) {
	global $rtmedia_cubepoints_plugins;
    
	if( empty( $rtmedia_cubepoints_plugins[ $slug ] ) ) {
		return false;
	}

	if( is_rtmedia_plugin_active_when_cubepoints_installed( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . get_path_for_rtmedia_plugins_when_cubepoints_installed( $slug ) ) ) {
		return true;
	}

	return false;
}

function rtmedia_cubepoints_install_plugin_ajax() {
	if( empty( $_POST[ 'plugin_slug' ] ) ) {
		die( __( 'ERROR: No slug was passed to the AJAX callback.', 'rtmedia' ) );
	}

	check_ajax_referer( 'rtmedia_cubepoints_install_plugin_' . $_POST[ 'plugin_slug' ] );

	if( !current_user_can( 'install_plugins' ) || !current_user_can( 'activate_plugins' ) ) {
		die( __( 'ERROR: You lack permissions to install and/or activate plugins.', 'rtmedia' ) );
	}

	rtmedia_cubepoints_install_plugin( $_POST[ 'plugin_slug' ] );

	echo "true";
	die();
}

function rtmedia_cubepoints_install_plugin( $plugin_slug ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

	if( is_wp_error( $api ) ) {
		die( sprintf( __( 'ERROR: Error fetching plugin information: %s', 'rtmedia' ), $api->get_error_message() ) );
	}

	$upgrader = new Plugin_Upgrader( 
        new RTMedia_Plugin_Upgrader_Skin( 
            array(
                'nonce' => 'install-plugin_' . $plugin_slug, 
                'plugin' => $plugin_slug, 
                'api' => $api,
            ) 
        ) 
    );

	$install_result = $upgrader->install( $api->download_link );

	if( !$install_result || is_wp_error( $install_result ) ) {
		// $install_result can be false if the file system isn't writeable.
		$error_message = __( 'Please ensure the file system is writeable', 'rtmedia' );

		if( is_wp_error( $install_result ) ) {
			$error_message = $install_result->get_error_message();
		}

		die( sprintf( __( 'ERROR: Failed to install plugin: %s', 'rtmedia' ), $error_message ) );
	}

	$activate_result = activate_plugin( get_path_for_rtmedia_plugins_when_cubepoints_installed( $plugin_slug ) );

	if( is_wp_error( $activate_result ) ) {
		die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rtmedia' ), $activate_result->get_error_message() ) );
	}
}

function rtmedia_cubepoints_activate_plugin_ajax() {
	if( empty( $_POST[ 'path' ] ) ) {
		die( __( 'ERROR: No slug was passed to the AJAX callback.', 'rtmedia' ) );
	}
    
	check_ajax_referer( 'rtmedia_cubepoints_activate_plugin_' . $_POST[ 'path' ] );

	if( !current_user_can( 'activate_plugins' ) ) {
		die( __( 'ERROR: You lack permissions to activate plugins.', 'rtmedia' ) );
	}

	rtmedia_cubepoints_activate_plugin( $_POST[ 'path' ] );

	echo "true";
	die();
}

function rtmedia_cubepoints_activate_plugin( $plugin_path ) {
	$activate_result = activate_plugin( $plugin_path );

	if( is_wp_error( $activate_result ) ) {
		die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rtmedia' ), $activate_result->get_error_message() ) );
	}
}
