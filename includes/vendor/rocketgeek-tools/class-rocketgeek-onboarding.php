<?php

if ( ! class_exists( 'RocketGeek_Onboarding_Beta' ) ) :
class RocketGeek_Onboarding_Beta {

    public function __construct( $settings ) {
        $this->settings = $settings;

        foreach ( $settings as $key => $value ) {
            $this->{$key} = $value;
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        if ( $this->menu_slug != rktgk_get( 'page', false, 'get' ) ) {
            add_action( 'admin_notices', array( $this, 'onboarding_notice'  ) );
        }
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
        add_submenu_page( null, $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, array( $this, 'do_options_page' ) );
    }
    
    public function do_options_page() {
        // @todo Get install record to check if this is a new install or update.
        call_user_func_array( $this->opt_in_callback, $this->opt_in_callback_args );
    }

    public function onboarding_notice() {
        $install_state = get_option( $this->install_state_option );
        if ( 'new_install' == $install_state ) {
			$args = $this->new_install_notice_args;
		}

		if ( 'update_pending' == $install_state ) {
			$args = $this->update_pending_notice_args;
		}

        include_once( $this->notice_template );
    }

	private function has_user_opted_in() {
		global $wpmem;
		if ( 1 == $wpmem->optin ) {
			return true;
		}

		return false;
	}
}
endif;