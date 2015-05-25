<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCubePointsEDDLicense
 *
 * @author sanket
 */
class RTMediaCubePointsEDDLicense {
    
    public $config = array(
        'product_id' => 'rtmedia_cubepoints',
		'product_name' => 'rtMedia CubePoints',
		'license_key' => 'edd_rtmedia_cubepoints_license_key',
		'license_status' => 'edd_rtmedia_cubepoints_license_status',
		'nonce_field_name' => 'edd_rtmedia_cubepoints_nonce',
		'license_activate_btn_name' => 'edd_rtmedia_cubepoints_license_activate',
		'license_deactivate_btn_name' => 'edd_rtmedia_cubepoints_license_deactivate',
	);

	function __construct() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ){
			// load our custom updater
			include_once( RTMEDIA_CUBEPOINTS_PATH . 'lib/edd-license/EDD_SL_Plugin_Updater.php' );
		}

		add_action( 'admin_init', array( $this, 'edd_sl_sample_plugin_updater' ) );
		add_filter( 'rtmedia_license_tabs', array( $this, 'add_rtmedia_license_tabs' ), 10, 1 );
		add_action( 'admin_init', array( $this, 'edd_sample_activate_license' ) );
		add_action( 'admin_init', array( $this, 'edd_sample_deactivate_license' ) );
	}

	function add_rtmedia_license_tabs( $tabs ){
		$tabs[] = array(
			'title' => $this->config['product_name'],
			'name' => $this->config['product_name'],
			'href' => '#rtmedia-cubepoints',
			'args' => array(
				'addon_id' => $this->config['product_id'],
				'key_id' => $this->config['license_key'],
				'status_id' => $this->config['license_status'],
				'license_key' => get_option( $this->config['license_key'] ),
				'status' => get_option( $this->config['license_status'] ),
			),
		);
        
		return $tabs;
	}

	function edd_sl_sample_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( $this->config['license_key'] ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( EDD_RTMEDIA_CUBEPOINTS_STORE_URL, RTMEDIA_CUBEPOINTS_BASE_NAME, array(
				'version'   => RTMEDIA_CUBEPOINTS_VERSION, // current version number
				'license'   => $license_key, // license key (used get_option above to retrieve from DB)
				'item_name' => EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME, // name of this plugin
				'author'    => 'rtCamp' // author of this plugin
			) );

	}

	function edd_sample_activate_license() {

		if ( isset( $_POST[ $this->config['license_key'] ] ) ){
			update_option( $this->config['license_key'], $_POST[ $this->config['license_key'] ] );
		}

		// listen for our activate button to be clicked
		if ( isset( $_POST[ $this->config['license_activate_btn_name'] ] ) ){
			// run a quick security check
			if ( ! check_admin_referer( $this->config['nonce_field_name'], $this->config['nonce_field_name'] ) ){
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( $this->config['license_key'] ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_RTMEDIA_CUBEPOINTS_STORE_URL ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			//		var_dump($response);

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( $this->config['license_status'], $license_data->license );

		}
	}

	function edd_sample_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ $this->config['license_deactivate_btn_name'] ] ) ){

			// run a quick security check
			if ( ! check_admin_referer( $this->config['nonce_field_name'], $this->config['nonce_field_name'] ) ){
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( $this->config['license_key'] ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_RTMEDIA_CUBEPOINTS_STORE_URL ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			//		var_dump($response);

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			//		var_dump($license_data);

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ){
				delete_option( $this->config['license_status'] );
			}

		}
	}

	function edd_sample_check_license() {

		global $wp_version;

		$license = trim( get_option( $this->config['license_key'] ) );

		$api_params = array(
			'edd_action' => 'check_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_CUBEPOINTS_ITEM_NAME ), 'url' => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, EDD_RTMEDIA_CUBEPOINTS_STORE_URL ) ), array( 'timeout' => 15, 'sslverify' => false ) );


		if ( is_wp_error( $response ) ){
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ){
			echo 'valid';
			exit;
			// this license is still valid
		} else {
			echo 'invalid';
			exit;
			// this license is no longer valid
		}
	}
    
}
