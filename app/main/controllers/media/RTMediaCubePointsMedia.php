<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCubePointsMedia
 *
 * @author sanket
 */
class RTMediaCubePointsMedia {

	var $rtmedia_key = array(
		'after_upload_image' => array( 'action' => 'rtmedia_after_add_photo' ),
		'after_upload_music' => array( 'action' => 'rtmedia_after_add_music' ),
		'after_upload_video' => array( 'action' => 'rtmedia_after_add_video' ),
		'after_album_create' => array( 'action' => 'rtmedia_after_add_album' ),
		'after_playlist_create' => array( 'action' => 'rtmedia_after_add_playlist' ),
		'after_media_rate' => array( 'action' => 'rtmedia_pro_after_rating_media' ),
		'after_media_download' => array( 'action' => 'rtmedia_downloads_before_download_media' ),
		'after_media_like' => array( 'action' => 'rtmedia_after_like_media' ),
		'after_media_view' => array( 'action' => 'rtmedia_after_view_media' ),
		'after_media_edit' => array( 'action' => 'rtmedia_after_edit_media' ),
		'after_media_delete' => array( 'action' => 'rtmedia_after_delete_media' ),
		'after_media_report' => array( 'action' => 'rtmedia_cubepoints_after_report_media' ),
		'after_set_album_cover' => array( 'action' => 'rtmedia_pro_after_set_album_cover' ),
		'after_set_featured' => array( 'action' => 'rtmedia_after_set_featured' ),
		'after_comment' => array( 'action' => 'rtmedia_after_add_comment' ),
		'after_edit_album' => array( 'action' => 'rtmedia_after_update_album' ),
	);

	public function __construct() {
		// CubePoints settings tab
		add_filter( 'rtmedia_add_settings_sub_tabs', array( $this, 'rtmedia_cubepoints_add_settings_tab' ), 10, 1 );
		$this->init();
	}

	/*
	 * Adding sub-tab under rtMedia Settings
	 */
	public function rtmedia_cubepoints_add_settings_tab( $sub_tabs ) {
		// Creating sub-tab array
		$sub_tabs[] = array(
			'href' => '#rtmedia-cubepoints',
			'icon' => 'dashicons-star-filled',
			'title' => __( 'rtMedia CubePoints', 'rtmedia' ),
			'name' => __( 'CubePoints', 'rtmedia' ),
			'callback' => array( $this, 'rtmedia_cubepoints_admin_content' ),
		);

		return $sub_tabs;
	}

	/*
	 * rtMedia CubePoints admin content
	 */
	public function rtmedia_cubepoints_admin_content() {
		?>
		<div class="rtm-option-wrapper">
			<h3 class="rtm-option-title">CubePoints Settings</h3>
			<table class="form-table">
				<tbody>
					<tr>
						<?php if ( function_exists( 'cp_module_register' ) ) { ?>
							<th><?php _e( 'Setup', 'rtmedia' ); ?> CubePoints <a href="<?php echo get_admin_url(); ?>admin.php?page=cp_admin_config#rtmedia-cp" target="_blank"><?php _e( 'here', 'rtmedia' ); ?></a>.</th>
							<?php } else { ?>
							<td><a href="http://wordpress.org/plugins/cubepoints/" target="_blank">CubePoints</a><?php echo ' ' . __( 'must be installed or activated.', 'rtmedia' ); ?></td>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function init() {
		// CubePoints is activated
		if ( function_exists( 'cp_module_register' ) ) {
			// Regestering module for rtMedia activities
			cp_module_register( __( 'Points for rtMedia', 'cp' ), 'rtmedia', '1.0', '<a id="rtmedia-cp" href="http://rtcamp.com/">rtCamp</a>', 'http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media', 'http://rtcamp.com/', __( 'Points for add photos, videos or music.', 'cp' ), 1 );

			add_action( 'cp_module_rtmedia_activate', array( $this, 'cp_rtmedia_install' ) );

			if ( cp_module_activated( 'rtmedia' ) ) {
				//$this->configure_cp_rtmedia_options();
				// Add a function to display the form inputs.
				add_action( 'cp_config_form', array( $this, 'cp_rtmedia_config' ) );
				// Create a function to process the form inputs when the form is submitted.
				add_action( 'cp_config_process', array( $this, 'cp_rtmedia_config_process' ) );
				add_action( 'cp_logs_description', array( $this, 'cp_rtmedia_log' ), 10, 4 );
			}

			// bind actions dynamically
			$rtmedia_points = maybe_unserialize( get_site_option( 'rtmedia_points', array() ) );

			if ( is_array( $rtmedia_points ) && sizeof( $rtmedia_points ) > 0 ) {
				foreach ( $rtmedia_points as $key => $val ) {
					add_action( $val['action'], array( $this, $key ) );
				}
			}
		}
	}

	public function cp_rtmedia_log( $type, $uid, $points, $data ) {
		if ( 'cp_rtmedia' != $type ) {
			return;
		}

		_e( $data, 'cp_rtmedia' );
	}

	public function cp_rtmedia_install() {
		$rtmedia_points = maybe_unserialize( get_site_option( 'rtmedia_points', array() ) );

		if ( ! is_array( $rtmedia_points ) ) {
			$rtmedia_points = array();

			foreach ( $this->rtmedia_key as $key => $val ) {
				$val['message'] = array( 'cp_message' => 'rtMedia ' . str_replace( '_', ' ', $key ) );
				$val['points'] = array( 'cp_points' => 0 );
				$rtmedia_points[ $key ] = $val;
			}
		} else {
			foreach ( $this->rtmedia_key as $key => $val ) {
				if ( ( ! isset( $rtmedia_points[ $key ]['points']['cp_points'] ) ) || ( $rtmedia_points[ $key ]['action'] != $val['action'] ) ) {
					$val['message']['cp_message'] = 'rtMedia ' . str_replace( '_', ' ', $key );
					$val['points']['cp_points'] = 0;
					$rtmedia_points[ $key ] = $val;
				}
			}

			if ( sizeof( $this->rtmedia_key ) < sizeof( $rtmedia_points ) ) {
				foreach ( $rtmedia_points as $key => $val ) {
					if ( ! isset( $this->rtmedia_key[ $key ] ) ) {
						unset( $rtmedia_points[ $key ] );
					}
				}
			}
		}

		rtmedia_update_site_option( 'rtmedia_points', $rtmedia_points );
	}

	public function cp_rtmedia_config() {
		$rtmedia_points = maybe_unserialize( get_site_option( 'rtmedia_points', array() ) );
		?>
		<br />
		<h3 id="rtmedia-cp"><?php esc_html_e( 'Points for rtMedia', 'cp' ); ?></h3>
		<table class="form-table">
			<?php foreach ( $rtmedia_points as $key => $val ) { ?>
				<tr valign="top">
					<th scope="row">
						<label for="cp_rtmedia_points_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?>:</label>
					</th>
					<td valign="middle">
						<input type="text" id="cp_rtmedia_points_<?php echo esc_attr( $key ); ?>" name="rtmedia_points[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val['points']['cp_points'] ); ?>" size="30" />
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php
	}

	public function cp_rtmedia_config_process() {
		$rtmedia_points = maybe_unserialize( get_site_option( 'rtmedia_points', array() ) );

		foreach ( $rtmedia_points as $key => $val ) {
			$rtmedia_points[ $key ]['points']['cp_points'] = (int) $_POST['rtmedia_points'][ $key ];
		}

		rtmedia_update_site_option( 'rtmedia_points', $rtmedia_points );
	}

	public function __call( $name, $arguments ) {
		$rtmedia_points = maybe_unserialize( get_site_option( 'rtmedia_points', array() ) );

		if ( is_array( $rtmedia_points ) && sizeof( $rtmedia_points ) > 0 ) {
			if ( function_exists( 'cp_module_register' ) ) {
				$user = get_current_user_id();
				$user_meta = maybe_unserialize( get_user_meta( $user, 'rtmedia_points_key', true ) );
				global $rtmedia_points_media_id;

				if ( ! is_array( $user_meta ) || '' == $user_meta  ) {
					$user_meta = array();
				}

				if ( ! isset( $user_meta[ $name ]['cp_points'] ) ) {
					$user_meta[ $name ]['cp_points'] = array();
				}

				if ( isset( $rtmedia_points[ $name ]['points']['cp_points'] ) && ( '' != $rtmedia_points[ $name ]['points']['cp_points'] ) && ( ! in_array( $rtmedia_points_media_id, $user_meta[ $name ]['cp_points'] ) ) && isset( $user ) && 0 != $user ) {
					cp_points( 'cp_rtmedia', $user, $rtmedia_points[ $name ]['points']['cp_points'], ucfirst( str_replace( '_', ' ', $name ) ) );
					$user_meta[ $name ]['cp_points'][] = $rtmedia_points_media_id;
					update_user_meta( $user, 'rtmedia_points_key', $user_meta );
				}
			}
		}
	}

}
