<?php
if ( ! class_exists( 'RocketGeek_Satellite_Beta' ) ) :
class RocketGeek_Satellite_Beta {

	public $version = "1.0.3";
	public $api_domain = 'https://rocketgeek.com';
	public $theme_fields  = array( 'Name','URI','Author','AuthorURI','Version' );
	public $plugin_fields = array( 'Name','URI','Author','AuthorURI','Version','RequiresWP','RequiresPHP' );
	public $slug;
	public $type;
	public $url;
	public $product_version;

	public function __construct( $slug, $product_file, $action, $type ) {
		$this->slug = $slug;
		$this->type = $type;
		$this->url  = trailingslashit( $this->get_endpoint() . $action );

		if ( 'plugin' == $type ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$plugin_data = get_plugin_data( $product_file );
			$this->product_version = $plugin_data['Version'];
		}
		$this->do_action();
	}

	private function get_action() {
		return $this->action;
	}
	
	private function get_slug() {
		return $this->slug;
	}
	
	private function get_product_version() {
		return $this->product_version;
	}
	
	private function get_module_type() {
		return $this->type;
	}

	private function get_admin() {
		return get_bloginfo( 'admin_email' );
	}

	private function get_endpoint() {
		return trailingslashit( trailingslashit( $this->api_domain ) . 'api/v1/product/action' );
	}

	private function is_marketing_allowed() {
		return 1;
	}

	private function is_deactivated() {
		return false;
	}

	private function is_uninstall() {
		return false;
	}
	
	private function get_uninstall_params() {
		$reason_id = ''; $reason_info = '';
		return array(
			'reason_id'   => $reason_id,
			'reason_info' => $reason_info,
		);
	}

	private function check_return_url() {
		return admin_url();
	}
	
	private function check_site() {
		return array(
			'wp_version'  => get_bloginfo( 'version' ),
			'php_version' => phpversion(),
			'is_network'  => ( ( is_multisite() ) ? 1 : 0 ),
			'site_url'    => get_site_url(),
			'site_name'   => get_bloginfo( 'name' ),
			'language'    => get_bloginfo( 'language' ),
			'charset'     => get_bloginfo( 'charset' ),
		);
	}

	private function check_product() {
		
		return array(
			'sdk_version'    => $this->version,
			'license'        => $this->check_license(),
			'slug'           => $this->get_slug(),
			'version'        => $this->get_product_version(),
			'is_active'      => true,
			'is_uninstalled' => false,
		);
	}

	private function check_addr() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	private function check_license() {
		$license = get_option( $this->get_slug() . '-license', false );
		return ( $license ) ? $license : false;
	}

	private function check_themes() {
		$theme_data = wp_get_themes();
		foreach ( $theme_data as $theme_slug => $theme ) {
			$themes[ $theme_slug ] = array(
				'Name'      => $theme->get( 'Name' ),
				'URI'       => $theme->get( 'ThemeURI' ),
				'Author'    => $theme->get( 'Author' ),
				'AuthorURI' => $theme->get( 'AuthorURI' ),
				'Version'   => $theme->get( 'Version' ),
			);
			$themes[ $theme_slug ]['Active'] = ( $theme->get( 'Name' ) == wp_get_theme()->get('Name') ) ? 'active' : 'inactive';
		}
		return $themes;
	}

	private function check_plugins() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );			
		$plugin_data = get_plugins();
		$active_plugins = get_option( 'active_plugins' );
		foreach ( $plugin_data as $plugin_slug => $plugin ) {
			$plugins[ $plugin_slug ] = array(
				'Name'        => $plugin['Name'],
				'URI'         => $plugin['PluginURI'],
				'Author'      => $plugin['Author'],
				'AuthorURI'   => $plugin['AuthorURI'],
				'Version'     => $plugin['Version'],
				'RequiresWP'  => $plugin['RequiresWP'],
				'RequiresPHP' => $plugin['RequiresPHP'],
			);
			$plugins[ $plugin_slug ]['Active'] = ( in_array( $plugin_slug, $active_plugins ) ) ? 'active' : 'inactive';
		}
		return $plugins;
	}	
	private function check_user() {
		$user = wp_get_current_user();
		$check_user = get_user_by( 'email', $this->get_admin() );
		$admin = ( $check_user ) ? $check_user : false;
		return array(
			'site'  => array(
				'email'      => ( $admin ) ? $admin->user_email : '',
				'first_name' => ( $admin ) ? $admin->first_name : '',
				'last_name'  => ( $admin ) ? $admin->last_name  : '',
			),
			'user'  => array(
				'email'       => $user->user_email,
				'first_name'  => $user->first_name,
				'last_name'   => $user->last_name,
			),
			'marketing'   => $this->is_marketing_allowed(),
			'user_ip'     => $this->check_addr(),
		);
	}

	private function do_action() {

		$params = array(
			'check_site'    => $this->check_site(),
			'check_product' => $this->check_product(),
			'return_url'    => $this->check_return_url(),
			'account_url'   => admin_url(),
		);

		$params['check_user'] = $this->check_user();

		if ( $this->is_uninstall() ) {
			$params['uninstall_params'] = $this->get_uninstall_params();
		}

		$params['check_themes']  = $this->check_themes();
		$params['check_plugins'] = $this->check_plugins();

		$params['sdk_version'] = $this->version;
		$params['format'] = 'json';

		$request = array(
			'method'  => 'POST',
			'body'    => $params,
			'timeout' => 30,
		);

		$response = wp_remote_post( $this->url, $request );
		if ( is_wp_error( $response ) ) {
			/**
			 * @var WP_Error $response
			 */
			$result = new stdClass();

			$error_code = $response->get_error_code();
			$error_type = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $error_code ) ) );

			$result->error = (object) array(
				'type'    => $error_type,
				'message' => $response->get_error_message(),
				'code'    => $error_code,
				'http'    => 402
			);

			$this->maybe_modify_api_curl_error_message( $result );

			return $result;
		}

		// Module is being uninstalled, don't handle the returned data.
		if ( $this->is_uninstall() ) {
			return true;
		}

	}

	/**
	 * Handles cURL error message.
	 * 
	 * @since 1.0.0
	 *
	 * @param object $result
	 */
	private function maybe_modify_api_curl_error_message( $result ) {
		if  ( 'cUrlMissing' !== $result->error->type &&
			( 'CurlException' !== $result->error->type || CURLE_COULDNT_CONNECT != $result->error->code )  &&
			( 'HttpRequestFailed' !== $result->error->type || false === strpos( $result->error->message, 'cURL error ' . CURLE_COULDNT_CONNECT ) )
		) {
			return;
		}

		$result->error->message = esc_html(
			__( 'We use PHP cURL library for the API calls, which is a very common library and usually installed and activated out of the box. Unfortunately, cURL is not activated (or disabled) on your server.', 'text-domain' ) .
			' ' .
			sprintf(
				__( 'Please contact your hosting provider and ask them to whitelist %s for external connection.', 'text-domain' ),
				implode(
					', ',
					apply_filters( 'api_domains', array(
						'rocketgeek.com',
					) )
				)
			) .
			' ' .
			sprintf( __( 'Once you are done, deactivate the %s and activate it again.', 'text-domain' ), $this->get_module_type() ) 
		);
	}
}
endif;