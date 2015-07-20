<?php

/**
 * Created by PhpStorm.
 * User: rtcamp
 * Date: 20/7/15
 * Time: 2:09 PM
 */

if ( !class_exists( 'RTEDDLicense' ) ) {

	class RTEDDLicense {

		//public $config;

		public $config = array(
			'rt_product_id' => 'rtmedia_activity_url_preview',
			'rt_product_name' => 'rtMedia Activity URL Preview',
			'rt_product_href' => 'rtmedia-activity-url-preview',
			'rt_license_key' => 'edd_rtmedia_activity_url_preview_license_key',
			'rt_license_status' => 'edd_rtmedia_activity_url_preview_license_status',
			'rt_nonce_field_name' => 'edd_rtmedia_activity_url_preview_nonce',
			'rt_license_activate_btn_name' => 'edd_rtmedia_activity_url_preview_license_activate',
			'rt_license_deactivate_btn_name' => 'edd_rtmedia_activity_url_preview_license_deactivate',
			'rt_product_path' => RTMEDIA_ACTIVITY_URL_PREVIEW_PATH,
			'rt_product_store_url' => EDD_RTMEDIA_ACTIVITY_URL_PREVIEW_STORE_URL,
			'rt_product_base_name' => RTMEDIA_ACTIVITY_URL_PREVIEW_BASE_NAME,
			'rt_product_version' => RTMEDIA_ACTIVITY_URL_PREVIEW_VERSION,
			'rt_item_name' => EDD_RTMEDIA_ACTIVITY_URL_PREVIEW_ITEM_NAME

		);

		function __construct( $product_details_array ) {
			$this->config = $product_details_array;

			if ( !class_exists( 'RT_EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater
				include_once( $this->config[ 'rt_product_path' ] . 'lib/edd-license/RT_EDD_SL_Plugin_Updater.php' );
			}

			add_action( 'admin_init', array( $this, 'edd_sl_sample_plugin_updater' ) );

			add_filter( 'rtmedia_license_tabs', array( $this, 'add_rtmedia_license_tabs' ), 10, 1 );

			add_action( 'admin_init', array( $this, 'edd_sample_activate_license' ) );
			add_action( 'admin_init', array( $this, 'edd_sample_deactivate_license' ) );
		}

		function add_rtmedia_license_tabs( $tabs ) {
			$tabs[] = array(
				'title' => $this->config[ 'rt_product_name' ],
				'name'  => $this->config[ 'rt_product_name' ],
				'href'  => '#' . $this->config[ 'rt_product_href' ],
				'args'  => array(
					'addon_id'    => $this->config[ 'rt_product_id' ],
					'key_id'      => $this->config[ 'rt_license_key' ],
					'status_id'   => $this->config[ 'rt_license_status' ],
					'license_key' => get_option( $this->config[ 'rt_license_key' ] ),
					'status'      => get_option( $this->config[ 'rt_license_status' ] )
				)
			);

			return $tabs;
		}

		function edd_sl_sample_plugin_updater() {
			// retrieve our license key from the DB
			$license_key = trim( get_option( $this->config[ 'rt_license_key' ] ) );

			// setup the updater
			$edd_updater = new RT_EDD_SL_Plugin_Updater(
				$this->config[ 'rt_product_store_url' ],
				$this->config[ 'rt_product_base_name' ],
				array(
					'version'   => $this->config[ 'rt_product_version' ], // current version number
					'license'   => $license_key, // license key (used get_option above to retrieve from DB)
					'item_name' => $this->config[ 'rt_item_name' ], // name of this plugin
					'author'    => 'rtCamp' // author of this plugin
				)
			);
		}

		function edd_sample_activate_license() {
			if ( isset( $_POST[ $this->config[ 'rt_license_key' ] ] ) ) {
				update_option( $this->config[ 'rt_license_key' ], $_POST[ $this->config[ 'rt_license_key' ] ] );
			}

			// listen for our activate button to be clicked
			if ( isset( $_POST[ $this->config[ 'rt_license_activate_btn_name' ] ] ) ) {
				// run a quick security check
				if ( !check_admin_referer( $this->config[ 'rt_nonce_field_name' ], $this->config[ 'rt_nonce_field_name' ] ) ) {
					return;
				} // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( $this->config[ 'rt_license_key' ] ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_name'  => urlencode( $this->config[ 'rt_item_name' ] ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config[ 'rt_product_store_url' ] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) ) {
					return false;
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "valid" or "invalid"

				update_option( $this->config[ 'rt_license_status' ], $license_data->license );
			}
		}

		function edd_sample_deactivate_license() {
			// listen for our activate button to be clicked
			if ( isset( $_POST[ $this->config[ 'rt_license_deactivate_btn_name' ] ] ) ) {
				// run a quick security check
				if ( !check_admin_referer( $this->config[ 'rt_nonce_field_name' ], $this->config[ 'rt_nonce_field_name' ] ) ) {
					return;
				} // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( $this->config[ 'rt_license_key' ] ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_name'  => urlencode( $this->config[ 'rt_item_name' ] ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config[ 'rt_product_store_url' ] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) ) {
					return false;
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"

				if ( $license_data->license == 'deactivated' ) {
					delete_option( $this->config[ 'rt_license_status' ] );
				}
			}
		}

		function edd_sample_check_license() {
			global $wp_version;

			$license = trim( get_option( $this->config[ 'rt_license_key' ] ) );

			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->config[ 'rt_item_name' ] ),
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config[ 'rt_product_store_url' ] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->license == 'valid' ) {
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

}
