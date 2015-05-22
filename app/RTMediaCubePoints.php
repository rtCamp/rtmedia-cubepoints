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
    }
    
    /*
     * load rtMedia-CubePoints translations
     */
	public function rtmedia_cubepoints_load_translation() {
		load_plugin_textdomain( 'rtmedia', false, basename( RTMEDIA_CUBEPOINTS_PATH ) . '/languages/' );
	}
    
}
