<?php
/**
 * A WP_CLI set of subcommands to list and update WP-Members plugin settings.
 *
 * @since 3.3.5
 */
class WP_Members_CLI_Settings {
	
	/**
	 * Initialize any required elements.
	 *
	 * @since 3.3.5
	 *
	 * @global object $wpmem
	 */
	public function __construct() {
		// WP-Members admin needs to be loaded manually.
		global $wpmem;
		if ( ! isset( $wpmem->admin ) ) {
			$wpmem->load_admin();
		}
	}
	
	/**
	 * List the WP-Members content settings.
	 *
	 * @since 3.3.5
	 */
	public function content() {
		$this->_list( array( 'content' ), array() );	
	}
	
	/**
	 * List the WP-Members option settings.
	 *
	 * @since 3.3.5
	 */
	public function options() {
		$this->_list( array( 'options' ), array() );
	}
	
	/**
	 * Lists WP-Members settings.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $args
	 * @param  array  $assoc_args
	 */
	private function _list( $args, $assoc_args ) {
		
		global $wpmem;
		
		if ( 'content' == $args[0] ) {
			$settings = $wpmem->admin->settings( 'content' );
		} else {
			$settings = $wpmem->admin->settings( 'options' );
		}
		if ( 'content' == $args[0] ) {

			// @todo Add custom post types, and look for admin where all possible post types are assembled.
			$post_types = array( 'post', 'page' );

			foreach( $post_types as $post_type ) {
				foreach ( $settings as $setting => $description ) {
					if ( 'autoex' != $setting ) {
						$list[] = array(
							'Setting' => $setting . ' ' . $post_type,
							'Description' => $description . ' ' . $post_type,
							'Value' =>  $wpmem->{$setting}[ $post_type ],
							'Option' => ( 0 == $value ) ? 'Disabled' : 'Enabled',
						);
					} else {
						$list[] = array(
							'Setting' => $setting . ' ' . $post_type,
							'Description' => $description . ' ' . $post_type,
							'Value' =>  $wpmem->{$setting}[ $post_type ]['enabled'],
							'Option' => ( 0 == $wpmem->{$setting}[ $post_type ]['enabled'] ) ? 'Disabled' : 'Enabled',
						);
						$list[] = array( 
							'Setting' => '', 
							'Description' => $post_type . ' excerpt word length:', 
							'Value' => $wpmem->{$setting}[ $post_type ]['length'],
							'Option' => '', 
						);

					}
				}

				$list[] = array( 'Setting' => '', 'Description' => '', 'Value' => '', 'Option' => '' );
			}
		} else {
			foreach ( $settings as $setting => $description ) {
				if ( 'captcha' == $setting ) {
					$option = WP_Members_Captcha::type( $wpmem->{$setting} );
				} else {
					$option = ( 0 == $wpmem->{$setting} ) ? 'Disabled' : 'Enabled';
				}
				$list[] = array(
					'Setting'  => $setting,
					'Description' => $description,
					'Value' => $wpmem->{$setting},
					'Option' => $option,
				);

			}
		}
	
		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Description', 'Setting', 'Value', 'Option' ) );
		$formatter->display_items( $list ); 
	}
	
	/**
	 * Enable a WP-Members setting.
	 *
	 * ## OPTIONS
	 *
	 * <option>
	 * : The WP-Members option setting to enable.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mem settings enable mod_reg
	 */
	public function enable( $args, $assoc_args ) {
		global $wpmem;
		$settings = $wpmem->admin->settings( 'options' );
		if ( array_key_exists( $args[0], $settings ) && 'captcha' !== $args[0] ) {
			$this->update_option( $args[0], 1 );
			WP_CLI::success( $settings[ $args[0] ] . ' enabled' );
		}
		if ( array_key_exists( $args[0], $settings ) && 'captcha' === $args[0] ) {
			switch( $args[1] ) {
				case 'rs_captcha':
					$which = 2;
					break;
				case 'recaptcha_v2':
					$which = 3;
					break;
				case 'recaptcha_v3':
					$which = 4;
					break;
			}
			$this->update_option( $args[0], $which );
			WP_CLI::success( $settings[ $args[0] ] . ' ' . $args[1] . ' enabled' );
		}
	}
	
	/**
	 * Disables a WP-Members setting.
	 *
	 * ## OPTIONS
	 *
	 * <option>
	 * : The WP-Members option setting to disable.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mem settings enable mod_reg
	 */
	public function disable( $args ) {
		global $wpmem;
		$settings = $wpmem->admin->settings( 'options' );
		if ( array_key_exists( $args[0], $settings ) ) {
			$this->update_option( $args[0], 0 );
			WP_CLI::success( $settings[ $args[0] ] . ' disabled' );
		}
	}
	
	/**
	 * Set, clear, or list the WP-Members page settings.
	 *
	 * ## OPTIONS
	 *
	 * <list>
	 * : Lists all page settings.
	 *
	 * <clear>
	 * : Clears page or pages specified.
	 *
	 * <set>
	 * : Set a page ID for the user page.
	 *
	 * [--all]
	 * : used with <clear> option, clears all pages.
	 *
	 * [--login=<ID>]
	 * : Leave empty (--login) to clear, or set a page ID for the login page.
	 *
	 * [--register=<ID>]
	 * : Leave empty (--register) to clear, or set a page ID for the registration page.
	 *
	 * [--profile=<ID>]
	 * : Leave empty (--profile) to clear, or set a page ID for the profile page.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mem settings pages clear --all
	 *     wp mem settings pages clear --register
	 *     wp mem settings pages set --login=123
	 *     wp mem settings pages list
	 */
	public function pages( $args, $assoc_args ) {
		if ( empty( $args ) ) {
			WP_CLI::error( 'You must specify clear|set|list', true );
		}
		if ( 'clear' == $args[0] ) {
			if ( empty( $assoc_args ) ) {
				WP_CLI::error( 'You must specify --all or --login|register|profile', true );
			}
			if ( isset( $assoc_args['all'] ) ) {
				unset( $assoc_args['all'] );
				$assoc_args = array( 'login'=>'', 'register'=>'', 'profile'=>'' );
			}
			foreach ( $assoc_args as $page => $value ) {
				if ( isset( $assoc_args[ $page ] ) ) {
					$this->update_option( 'user_pages/' . $page, '' );
					WP_CLI::success( ucfirst( $page ) . ' page cleared' );
				}	
			}
			return;
		}
		if ( 'set' == $args[0] ) {
			if ( empty( $assoc_args ) ) {
				WP_CLI::error( 'You must specify which page(s) to set: --login=<ID>, --register=<ID>, --profile=<ID>', true );
			}
			foreach ( $assoc_args as $page => $value ) {
				if ( isset( $assoc_args[ $page ] ) ) {
					$this->update_option( 'user_pages/' . $page, $assoc_args[ $page ] );
					WP_CLI::success( ucfirst( $page ) . ' page set to ID ' . $assoc_args[ $page ] );
				}
			}
			return;
		}
		if ( 'list' == $args[0] ) {
			global $wpmem;
			$raw_settings = get_option( 'wpmembers_settings' );
			foreach ( $wpmem->user_pages as $key => $page ) {
				$list[] = array(
					'Page' => ucfirst( $key ),
					'ID' => $raw_settings['user_pages'][ $key ],
					'URL' => $wpmem->user_pages[ $key ],
				);
			}
			
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Page', 'ID', 'URL' ) );
			$formatter->display_items( $list );
		}
	}
	
	/**
	 * Updates a WP-Members option value and saves.
	 *
	 * @since 3.3.5
	 */
	private function update_option( $key, $value ){
		$current = get_option( 'wpmembers_settings' );
		if ( strpos( $key, '/' ) ) {
			$keys = explode( '/', $key );
			$current[ $keys[0] ][ $keys[1] ] = $value;
		} else {
			$current[ $key ] = $value;
		}
		update_option( 'wpmembers_settings', $current );
	}
}

WP_CLI::add_command( 'mem settings', 'WP_Members_CLI_Settings' );