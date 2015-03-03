<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCubePoints
 *
 * @author sanket
 */
class RTMediaCubePoints {
    
    public function __construct() {
        $this->rtmedia_cubepoints_load_translation();
        // rtMedia Moderation db upgrade
		add_action( 'init', array( $this, 'rtmedia_cubepoints_do_upgrade' ), 9, 1 );
    }
    
    /*
     * load rtMedia-CubePoints translations
     */
	public function rtmedia_cubepoints_load_translation() {
		load_plugin_textdomain( 'rtmedia', false, basename( RTMEDIA_CUBEPOINTS_PATH ) . '/languages/' );
	}
    
    public function rtmedia_cubepoints_do_upgrade() {
        if ( class_exists( 'RTDBUpdate' ) ) {
            // db upgrade for moderation schema
            $update = new RTDBUpdate( false, RTMEDIA_CUBEPOINTS_PATH . "index.php", false, true );
            
            if( !defined( 'RTMEDIA_CUBEPOINTS_VERSION' ) ) {
                /**
                 * The version of the plugin
                 */
                define( 'RTMEDIA_CUBEPOINTS_VERSION', '1.0' );
            }
            
            if( $update->check_upgrade() ) {
                $update->do_upgrade();
            }
        }
    }
    
}
