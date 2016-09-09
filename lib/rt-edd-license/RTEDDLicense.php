<?php

/**
 * Created by PhpStorm.
 * User: rtcamp
 * Date: 20/7/15
 * Time: 2:09 PM
 */

if ( ! class_exists( 'RTEDDLicense_rtmedia_cubepoints' ) ) {

	class RTEDDLicense_rtmedia_cubepoints {

		public $config;

		function __construct( $product_details_array ) {
			$this->config = $product_details_array;

			if ( ! class_exists( 'RTEDDSLPluginUpdater' ) ) {
				// load our custom updater
				include_once( $this->config['rt_product_path'] . 'lib/rt-edd-license/RTEDDSLPluginUpdater.php' );
			}

			add_action( 'admin_init', array( $this, 'edd_sl_sample_plugin_updater' ) );

			if ( ! empty( $this->config['rt_license_hook'] ) ) {
				add_filter( $this->config['rt_license_hook'], array( $this, 'add_rtmedia_license_tabs' ), 10, 1 );
			}

			add_action( 'admin_init', array( $this, 'edd_sample_activate_license' ) );
			add_action( 'admin_init', array( $this, 'edd_sample_deactivate_license' ) );
		}

		function add_rtmedia_license_tabs( $tabs ) {
			$tabs[] = array(
				'title' => $this->config['rt_product_name'],
				'name'  => $this->config['rt_product_name'],
				'href'  => '#' . $this->config['rt_product_href'],
				'args'  => array(
					'addon_id'    => $this->config['rt_product_id'],
					'key_id'      => $this->config['rt_license_key'],
					'status_id'   => $this->config['rt_license_status'],
					'license_key' => get_option( $this->config['rt_license_key'] ),
					'status'      => get_option( $this->config['rt_license_status'] ),
				),
			);

			return $tabs;
		}

		function edd_sl_sample_plugin_updater() {
			// retrieve our license key from the DB
			$license_key = trim( get_option( $this->config['rt_license_key'] ) );

			// setup the updater
			$edd_updater = new RTEDDSLPluginUpdater(
				$this->config['rt_product_store_url'],
				$this->config['rt_product_base_name'],
				array(
					'version'                => $this->config['rt_product_version'], // current version number
					'license'                => $license_key, // license key (used get_option above to retrieve from DB)
					'item_name'              => $this->config['rt_item_name'], // name of this plugin
					'author'                 => 'rtCamp', // author of this plugin
					'rt_product_text_domain' => isset( $this->config['rt_product_text_domain'] ) ? $this->config['rt_product_text_domain'] : '',// Text Domain
				)
			);
		}

		function edd_sample_activate_license() {
			if ( isset( $_POST[ $this->config['rt_license_key'] ] ) ) {
				update_option( $this->config['rt_license_key'], $_POST[ $this->config['rt_license_key'] ] );
			}

			// listen for our activate button to be clicked
			if ( isset( $_POST[ $this->config['rt_license_activate_btn_name'] ] ) ) {
				// run a quick security check
				if ( ! check_admin_referer( $this->config['rt_nonce_field_name'], $this->config['rt_nonce_field_name'] ) ) {
					return;
				} // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( $this->config['rt_license_key'] ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_name'  => urlencode( $this->config['rt_item_name'] ), // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config['rt_product_store_url'] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) ) {
					return false;
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "valid" or "invalid"

				update_option( $this->config['rt_license_status'], $license_data->license );
				update_option( 'edd_' . $this->config['rt_product_id'] . '_active', $license_data );
			}
		}

		function edd_sample_deactivate_license() {
			// listen for our activate button to be clicked
			if ( isset( $_POST[ $this->config['rt_license_deactivate_btn_name'] ] ) ) {
				// run a quick security check
				if ( ! check_admin_referer( $this->config['rt_nonce_field_name'], $this->config['rt_nonce_field_name'] ) ) {
					return;
				} // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( $this->config['rt_license_key'] ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_name'  => urlencode( $this->config['rt_item_name'] ), // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config['rt_product_store_url'] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) ) {
					return false;
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"

				if ( 'deactivated' == $license_data->license ) {
					delete_option( $this->config['rt_license_status'] );
					delete_option( 'edd_' . $this->config['rt_product_id'] . '_active' );
				}
			}
		}

		function edd_sample_check_license() {
			global $wp_version;

			$license = trim( get_option( $this->config['rt_license_key'] ) );

			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->config['rt_item_name'] ),
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->config['rt_product_store_url'] ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 'valid' == $license_data->license ) {
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
