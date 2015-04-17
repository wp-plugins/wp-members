<?php
/**
 * The WP-Members Class.
 *
 * @since 3.0
 */
class WP_Members {

	function __construct() {
		
		/**
		 * Load dependencies.
		 */
		require_once( 'wp-members-core.php' );
		
		/**
		 * Filter the options before they are loaded into constants.
		 *
		 * @since 2.9.0
		 *
		 * @param array $this->settings An array of the WP-Members settings.
		 */
		$settings = apply_filters( 'wpmem_settings', get_option( 'wpmembers_settings' ) );
		
		/**
		 * Assemble settings.
		 */
		foreach ( $settings as $key => $val ) {
			$this->$key = $val;
		}

		/**
		 * Set the stylesheet.
		 */
		$this->cssurl = ( $this->style == 'use_custom' ) ? $this->cssurl : $this->style;

		/**
		 * Get the action being done (if any).
		 */
		$this->action = $this->get_action();
		
		/**
		 * Get the regchk value (if any).
		 */
		$this->regchk = $this->get_regchk( $this->action );

		/**
		 * Filter wpmem_regchk.
		 *
		 * The value of regchk is determined by functions that may be run in the get_regchk function.
		 * This value determines what happens in the wpmem_securify() function.
		 *
		 * @since 2.9.0
		 *
		 * @param  string $this->regchk The value of wpmem_regchk.
		 * @param  string $this->action The $wpmem_a action.
		 */
		$this->regchk = apply_filters( 'wpmem_regchk', $this->regchk, $this->action );	
	
	}
	
	/**
	 * Gets the current action.
	 *
	 * @since 3.0.0
	 */
	function get_action() {
		return ( isset( $_REQUEST['a'] ) ) ? trim( $_REQUEST['a'] ) : '';
	}
	
	/**
	 * Gets the regchk value.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $action The action being done.
	 * @return string         The regchk value.
	 *
	 * @todo Describe regchk.
	 */
	function get_regchk( $action ) {

		switch ( $action ) {

			case 'login':
				return wpmem_login();
				break;

			case 'logout':
				wpmem_logout();
				break;
			
			case 'pwdchange':
				return wpmem_change_password();
				break;
			
			case 'pwdreset':
				return wpmem_reset_password();
				break;
			
			case 'register':
			case 'update':
				require_once( 'wp-members-register.php' );
				return wpmem_registration( $action  );
				break;
		}
		return;
	}

}