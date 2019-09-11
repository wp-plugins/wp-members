<?php
/**
 * WP-Members Admin functions
 *
 * Static functions to manage the plugin dropins tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_Tab_Dropins {

	function __construct() {
		self::load_dependencies();
	}
	
	public static function load_dependencies() {
		/**
		 * Load WP_Members_Fields_Table object
		 */
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
	}
	
	/**
	 * Display the Dropins tab.
	 *
	 * @since 3.1.9
	 *
	 * @global $wpmem
	 */
	public static function render_tab() { ?>
		<div class="wrap">
			<?php 

			global $wpmem;

			// Get old settings to see if they are being updated.
			$old_settings = get_option( 'wpmembers_dropins' );

			// Update settings.
			$wpmem_dropins_saved = false;
			if (  ( isset( $_GET['tab'] ) && $_GET['tab'] == 'dropins' ) && isset( $_POST['wpmembers_dropins'] ) ) {
				$settings = array();
				$post_vals = wpmem_get( 'wpmembers_dropins', false );
				if ( $post_vals ) {
					foreach ( $post_vals as $key => $val ) {
						// Check against default strings. Only save if different.
						if ( html_entity_decode( wpmem_gettext( $key ) ) != html_entity_decode( $val ) ) {
							$settings['text'][ $key ] = $val;
						} else {
							if ( ! empty( $old_settings['text'] ) && array_key_exists( $key, $old_settings['text'] ) ) {
								$settings['text'][ $key ] = $val;
							}
						}
					}
					// Double check settings for defaults.
					foreach ( $settings['text'] as $k => $v ) {
						if ( wpmem_gettext( $k ) == $v ) {
							unset( $settings['text'][ $k ] );
						}
					}
				}

				// If there are any changes, update settings.
				if ( ! empty( $settings ) ) {
					update_option( 'wpmembers_dropins', $settings );
				} else {
					// Delete if empty.
					delete_option( 'wpmembers_dropins' );
				}

				$wpmem_dropins_saved = true;
			}
			if ( $wpmem_dropins_saved ) { ?>
			<div id="message" class="message"><p><strong><?php _e( 'WP-Members Dropin settings were updated', 'wp-members' ); ?></strong></p></div>
			<?php } ?>

	<?php
	}

	/**
	 * Adds Dropins Tab to the admin tab array.
	 *
	 * @since 3.1.9
	 *
	 * @param array $tabs The WP-Members admin panel tabs array.
	 */
	public static function add_tab( $tabs ) {
		return array_merge( 
			array_slice( $tabs, 0, 1 ),
			array( 'dropins' => 'Dropins' ),
			array_slice( $tabs, 1 )
		);
	}

	/**
	 * Builds the Dropins tab in the admin.
	 *
	 * @since 3.1.9
	 *
	 * @param string $tab The WP-Members admin panel tab being displayed.
	 */
	public static function do_tab( $tab ) {
		if ( $tab == 'dropins' ) {
			self::do_table();
		}	
		return;
	}

	/**
	 * Check dropins directory.
	 *
	 * @since 3.1.9
	 *
	 * @return boolean
	 */
	public static function check_dir() {

		/** This filter is documented in inc/class-wp-members.php */
		$dir = apply_filters( 'wpmem_dropin_dir', $wpmem->dropin_dir );
		$check  = false;
		if ( file_exists( $dir ) ) {
			$file   = $dir . '.htaccess';
			if ( ! file_exists ( $file ) ) {
				$check = self::create_htaccess( $file );
			} else {
				$handle = fopen( $file, "r" );
				if ( $handle ) {
				// Read file line-by-line
				while ( ( $buffer = fgets( $handle ) ) !== false ) {
					if ( strpos( $buffer, "Options -Indexes" ) !== false )
						$check = true;
						break;
					}
				}
				fclose( $handle );
				$check = ( false === $check ) ? self::create_htaccess( $file ) : $check;
			}
		}
		return $check;
	}

	/**
	 * Creates .htaccess in dropins directory if none exists.
	 *
	 * @since 3.1.9
	 *
	 * @param  string
	 * @return boolean
	 */
	public static function create_htaccess( $file ) {
		$handle = fopen( $file, "w" );
		fwrite( $handle, "Options -Indexes" );
		fclose( $handle );
		return ( $handle ) ? true : false;
	}

	/**
	 * Function to display the table of fields in the field manager tab.
	 * 
	 * @since 3.1.9
	 *
	 * @global object $wpmem
	 */
	public static function do_table() {
		global $wpmem; 

		// Get the dropin folder.
		/** This filter is documented in inc/class-wp-members.php */
		$folder = apply_filters( 'wpmem_dropin_folder', $wpmem->dropin_dir );

		// Set file headers for dropins.
		$headers = array(
			'Dropin Name'        => 'Dropin Name',
			'Dropin Description' => 'Dropin Description',
			'Version'            => 'Version',
		);

		// Array container for dropin file info.
		$field_items = array();

		// Parse dropins.
		foreach ( glob( $folder . '*.php' ) as $filename ) {
			$file_data = get_file_data( $filename, $headers );

			$filename = explode( '/', $filename );
			$filename = end( $filename );
			if ( ! empty( $file_data['Dropin Name'] ) ) {
				$field_items[] = array(
					'dropin_enabled'     => '',
					'dropin_name'        => $file_data['Dropin Name'],
					'dropin_file'        => $filename,
					'dropin_version'     => $file_data['Version'],
					'dropin_description' => $file_data['Dropin Description'],
				);
			}
		}

		// Set up table.
		include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-dropins-table.php' );
		$table = new WP_Members_Dropins_Table();

		$heading  = __( 'Manage Dropins', 'wp-members' );
		$loc_info = __( 'Current dropin folder: ', 'wp-members' );
		$loc_desc = __( 'You can change location of the dropin folder using the <code>wpmem_dropin_folder</code> filter.', 'wp-members' );
		echo '<div class="wrap">';
		printf( '<h3 class="title">%s</h3>', $heading );
		printf( '<p><strong>%s</strong></p>', $loc_info );
		printf( '<p>%s</p>', $loc_desc );
		printf( '<form name="updatedropinsform" id="updatedropinsform" method="post" action="%s">', wpmem_admin_form_post_url() );
		$table->items = $field_items;
		$table->prepare_items(); 
		$table->display(); 
		echo '</form>';
		echo '</div>'; 
	}

}