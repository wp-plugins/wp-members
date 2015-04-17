<?php
/**
 * The WP-Members Class.
 *
 * @since 3.0
 */
class WP_Members {

	function __construct() {
		
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
		 * Handle the stylesheet.
		 */
		$this->cssurl = ( $this->style == 'use_custom' ) ? $this->cssurl : $this->style;
	
	}

}