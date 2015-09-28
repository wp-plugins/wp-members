<?php
/**
 * Class to add WP-Members shortcodes to the post editor.
 *
 * @since 3.0
 */
class WP_Members_TinyMCE_Buttons {
	
	/**
	 * Initialize WP-Members TinyMCE Button.
	 *
	 * @since 3.0
	 */
	function __construct() {
    	
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) == 'true' ) {  
			add_filter( 'mce_external_plugins', array( &$this, 'add_plugin' ) );  
			add_filter( 'mce_buttons', array( &$this, 'register_button' ) ); 
		}  
    }  

	/**
	 * Load the correct plugin file.
	 *
	 * @since 3.0
	 *
	 * @param  array $plugin_array
	 * @return array $plugin_array
	 */
	function add_plugin( $plugin_array ) {  
		
		$ver = WPMEM_VERSION;

		// WP version 3.9 updated to tinymce 4.0
		if ( version_compare( get_bloginfo( 'version' ), '3.9', '>=' ) ) {
			$plugin_array['wpmem_shortcodes'] = WPMEM_DIR .'admin/js/shortcodes_tinymce_4.js?ver=' . $ver;
		} else {
			$plugin_array['wpmem_shortcodes'] = WPMEM_DIR .'admin/js/shortcodes_tinymce.js?ver=' . $ver;
		}
		return $plugin_array; 
	}

	/**
	 * Register the button.
	 *
	 * @since 3.0
	 *
	 * @param  array $buttons
	 * @return array $buttons
	 */	
	function register_button( $buttons ) {  
		array_push( $buttons, "wpmem_shortcodes_button" );
		return $buttons; 
	}
}

// End of File.