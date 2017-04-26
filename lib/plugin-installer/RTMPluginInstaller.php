<?php
/**
 * Author: Ritesh <ritesh.patel@rtcamp.com>
 */
if( !class_exists( 'RTMPluginInstaller' ) ) {

    class RTMPluginInstaller {

        public $plugins = array();

        public function __construct() {
            if( function_exists( 'rtm_plugin_installer_plugin_upgrader_class' ) ) {
                add_action( 'admin_init', array( $this, 'check_rtmedia_install_active' ) );
            }
        }

        function check_rtmedia_install_active() {
            if( !defined( 'RTMEDIA_PATH' ) && current_user_can( 'list_users' ) ) {

                $this->setup_plugins_array();
                rtm_plugin_installer_plugin_upgrader_class();

                add_action( 'admin_enqueue_scripts', array( $this, 'rtm_plugin_installer_scripts' ), 99 );
                add_action( 'admin_notices', array( $this, 'rtm_plugin_installer_admin_notice' ), 99 );
                add_action( 'wp_ajax_rtm_plugin_installer_install_plugin', array( $this, 'rtm_plugin_installer_install_plugin_ajax' ), 10 );
                add_action( 'wp_ajax_rtm_plugin_installer_activate_plugin', array( $this, 'rtm_plugin_installer_activate_plugin_ajax' ), 10 );
            }
        }

        function setup_plugins_array() {
            $this->plugins = array(
                'buddypress-media' => array(
                    'project_type' => 'all',
                    'name' => esc_html__( 'rtMedia for WordPress, BuddyPress and bbPress', 'rtmedia' ),
                    'active' => is_plugin_active( 'buddypress-media/index.php' ),
                    'filename' => 'index.php',
                ),
                'rtMedia' => array(
                    'project_type' => 'all',
                    'name' => esc_html__( 'rtMedia for WordPress, BuddyPress and bbPress', 'rtmedia' ),
                    'active' => is_plugin_active( 'rtMedia/index.php' ),
                    'filename' => 'index.php',
                )
            );
        }

        function rtm_plugin_installer_scripts() {
            wp_enqueue_script( 'rtm-plugin-installer', plugin_dir_url( __FILE__ ) . "rtm-plugin-installer.js", '', false, true );
            wp_localize_script( 'rtm-plugin-installer', 'rtmedia_plugin_installer_ajax_url', admin_url( 'admin-ajax.php' ) );
            wp_localize_script( 'rtm-plugin-installer', 'rtmedia_plugin_installer_ajax_loader', admin_url( '/images/spinner.gif' ) );
        }

        function rtm_plugin_installer_admin_notice() {
            $admin_notice = "<b>" . __( "rtMedia Add-on(s)", "rtmedia" ) . "</b> " . __( "won't work if rtMedia is", "rtmedia" ) . " ";
            if( !$this->is_plugin_installed( 'buddypress-media' ) && !$this->is_plugin_installed( 'rtMedia' ) ) {
                $nonce = wp_create_nonce( 'rtm_plugin_installer_install_plugin_buddypress-media' );

                $admin_notice .= __( "not installed. Click", "rtmedia" ) . " ";
                $admin_notice .= '<a href="#" class="rtm-plugin-installer" data-mode="install" data-slug="buddypress-media" data-nonce="' . $nonce . '">' . __( "here", "rtmedia" ) . '</a> ';
                $admin_notice .= __( 'to install rtMedia.', 'rtmedia' );
            } else {
                if( $this->is_plugin_installed( 'buddypress-media' ) && !$this->is_plugin_active( 'buddypress-media' ) ) {
                    $path = $this->get_plugin_path( 'buddypress-media' );
                    $nonce = wp_create_nonce( 'rtm_plugin_installer_activate_plugin_' . $path );

                    $admin_notice .= __( "not activated. Click", "rtmedia" ) . " ";
                    $admin_notice .= '<a href="#" class="rtm-plugin-installer" data-mode="activate" data-slug="buddypress-media" data-nonce="' . $nonce . '">' . __( "here", "rtmedia" ) . '</a> ';
                    $admin_notice .= __( 'to activate rtMedia.', 'rtmedia' );
                }
                if( $this->is_plugin_installed( 'rtMedia' ) && !$this->is_plugin_active( 'rtMedia' ) ) {
                    $path = $this->get_plugin_path( 'rtMedia' );
                    $nonce = wp_create_nonce( 'rtm_plugin_installer_activate_plugin_' . $path );

                    $admin_notice .= __( "not activated. Click", "rtmedia" ) . " ";
                    $admin_notice .= '<a href="#" class="rtm-plugin-installer" data-mode="activate" data-slug="rtMedia" data-nonce="' . $nonce . '">' . __( "here", "rtmedia" ) . '</a> ';
                    $admin_notice .= __( 'to activate rtMedia.', 'rtmedia' );
                }
            }
            ?>
            <div class="error rtmedia-not-installed-error">
                <p>
                    <?php echo $admin_notice; ?>
                </p>
            </div>
            <?php
        }

        function rtm_plugin_installer_install_plugin_ajax() {
            if( empty( $_POST[ 'plugin_slug' ] ) ) {
                die( __( 'ERROR: No slug was passed to the AJAX callback.', 'rtmedia' ) );
            }

            check_ajax_referer( 'rtm_plugin_installer_install_plugin_' . $_POST[ 'plugin_slug' ] );

            if( !current_user_can( 'install_plugins' ) || !current_user_can( 'activate_plugins' ) ) {
                die( __( 'ERROR: You lack permissions to install and/or activate plugins.', 'rtmedia' ) );
            }

            $this->install_plugin( $_POST[ 'plugin_slug' ] );

            echo "true";
            die();
        }

        function rtm_plugin_installer_activate_plugin_ajax() {
            if( empty( $_POST[ 'plugin_slug' ] ) ) {
                die( __( 'ERROR: No slug was passed to the AJAX callback.', 'rtmedia' ) );
            }
            $path = $this->get_plugin_path( $_POST[ 'plugin_slug' ] );
            check_ajax_referer( 'rtm_plugin_installer_activate_plugin_' . $path );

            if( !current_user_can( 'activate_plugins' ) ) {
                die( __( 'ERROR: You lack permissions to activate plugins.', 'rtmedia' ) );
            }

            $this->activate_plugin( $path );

            echo "true";
            die();
        }

        function install_plugin( $plugin_slug ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            $api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

            if( is_wp_error( $api ) ) {
                die( sprintf( __( 'ERROR: Error fetching plugin information: %s', 'rtmedia' ), $api->get_error_message() ) );
            }

            $upgrader = new Plugin_Upgrader( new RTMedia_Plugin_Upgrader_Skin( array(
                'nonce' => 'install-plugin_' . $plugin_slug, 'plugin' => $plugin_slug, 'api' => $api,
                ) ) );

            $install_result = $upgrader->install( $api->download_link );

            if( !$install_result || is_wp_error( $install_result ) ) {
                // $install_result can be false if the file system isn't writable.
                $error_message = __( 'Please ensure the file system is writable', 'rtmedia' );

                if( is_wp_error( $install_result ) ) {
                    $error_message = $install_result->get_error_message();
                }

                die( sprintf( __( 'ERROR: Failed to install plugin: %s', 'rtmedia' ), $error_message ) );
            }

            $activate_result = activate_plugin( $this->get_plugin_path( $plugin_slug ) );

            if( is_wp_error( $activate_result ) ) {
                die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rtmedia' ), $activate_result->get_error_message() ) );
            }
        }

        function update_plugin( $plugin_slug ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            $api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

            if( is_wp_error( $api ) ) {
                die( sprintf( __( 'ERROR: Error fetching plugin information: %s', 'rtmedia' ), $api->get_error_message() ) );
            }

            $upgrader = new Plugin_Upgrader( new RTMedia_Plugin_Upgrader_Skin( array(
                'nonce' => 'install-plugin_' . $plugin_slug, 'plugin' => $plugin_slug, 'api' => $api,
                ) ) );

            $update_result = $upgrader->upgrade( $this->get_plugin_path( $plugin_slug ) );

            if( !$update_result || is_wp_error( $update_result ) ) {
                // $update_result can be false if the file system isn't writeable.
                $error_message = __( 'Please ensure the file system is writeable', 'rtmedia' );

                if( is_wp_error( $update_result ) ) {
                    $error_message = $update_result->get_error_message();
                }

                die( sprintf( __( 'ERROR: Failed to update plugin: %s', 'rtmedia' ), $error_message ) );
            }

            $activate_result = activate_plugin( $this->get_plugin_path( $plugin_slug ) );

            if( is_wp_error( $activate_result ) ) {
                die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rtmedia' ), $activate_result->get_error_message() ) );
            }
        }

        function activate_plugin( $plugin_path ) {
            $activate_result = activate_plugin( $plugin_path );

            if( is_wp_error( $activate_result ) ) {
                die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rtmedia' ), $activate_result->get_error_message() ) );
            }
        }

        function is_plugin_installed( $plugin_slug ) {
            if( file_exists( WP_PLUGIN_DIR . '/' . $this->get_plugin_path( $plugin_slug ) ) ) {
                return true;
            }
            return false;
        }

        function is_plugin_active( $plugin_slug ) {
            return $this->plugins[ $plugin_slug ][ 'active' ];
        }

        function get_plugin_path( $plugin_slug ) {
            $filename = (!empty( $this->plugins[ $plugin_slug ][ 'filename' ] ) ) ? $this->plugins[ $plugin_slug ][ 'filename' ] : 'index.php';

            return $plugin_slug . '/' . $filename;
        }

    }

}

if( !function_exists( 'rtm_plugin_installer_plugin_upgrader_class' ) ) {

    function rtm_plugin_installer_plugin_upgrader_class() {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        if( !class_exists( 'RTMedia_Plugin_Upgrader_Skin' ) ) {

            class RTMedia_Plugin_Upgrader_Skin extends WP_Upgrader_Skin {

                function __construct( $args = array() ) {
                    $defaults = array( 'type' => 'web', 'url' => '', 'plugin' => '', 'nonce' => '', 'title' => '' );
                    $args = wp_parse_args( $args, $defaults );

                    $this->type = $args[ 'type' ];
                    $this->api = isset( $args[ 'api' ] ) ? $args[ 'api' ] : array();

                    parent::__construct( $args );
                }

                public function request_filesystem_credentials( $error = false, $context = false, $allow_relaxed_file_ownership = false ) {
                    return true;
                }

                public function error( $errors ) {
                    die( '-1' );
                }

                public function header() {

                }

                public function footer() {

                }

                public function feedback( $string ) {

                }

            }

        }
    }

}
