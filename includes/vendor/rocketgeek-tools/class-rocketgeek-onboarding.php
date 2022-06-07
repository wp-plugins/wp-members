<?php

class RocketGeek_Onboarding_Beta {

    public function __construct( $settings ) {
        $this->settings = $settings;
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function enqueue_scripts() {
        wp_register_style( 'rktgk_onboarding_css', plugin_dir_url( __FILE__ ) . 'assets/css/onboarding.css', false, '1.0.0' );
        wp_enqueue_style( 'rktgk_onboarding_css' );
    }

    public function record_plugin_activation( $slug, $product_file ) {
        require_once( plugin_dir_path( __FILE__ ) . 'class-rocketgeek-satellite.php' );
        $rgut = new RocketGeek_Satellite_Beta( $slug, $product_file, 'activate', 'plugin' );
    }

    public function record_plugin_deactivation( $slug, $product_file ) {
        require_once( plugin_dir_path( __FILE__ ) . 'class-rocketgeek-satellite.php' );
        $rgut = new RocketGeek_Satellite_Beta( $slug, $product_file, 'deactivate', 'plugin' );
    }

    public function record_plugin_upgrade( $slug, $product_file ) {
        require_once( plugin_dir_path( __FILE__ ) . 'class-rocketgeek-satellite.php' );
        $rgut = new RocketGeek_Satellite_Beta( $slug, $product_file, 'update', 'plugin' );
    }
    
    public function admin_menu () {
        add_submenu_page( null, $this->settings['page_title'], $this->settings['menu_title'], $this->settings['capability'], $this->settings['menu_slug'], array( $this, 'do_options_page' ) );
    }
    
    public function do_options_page() {
        // @todo Get install record to check if this is a new install or update.
        call_user_func_array( $this->settings['opt_in_callback'], $this->settings['opt_in_callback_args'] );
    }
}