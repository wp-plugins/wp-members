<?php
/**
 * WP-Members Dropins Admin Functions
 *
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com/plugins/wp-members/extensions/editor/
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Editor
 * @author Chad Butler
 * @copyright 2006-2017
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Load WP_Members_Fields_Table object
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Display the Dropins tab.
 *
 * @since 3.1.9
 *
 * @global $wpmem
 */
function wpmem_dropins_render_tab() { ?>
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
function wpmem_add_dropins_tab( $tabs ) {
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
function wpmem_render_dropins_tab( $tab ) {
	if ( $tab == 'dropins' ) {
		wpmem_a_render_dropins_table();
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
function wpmem_dropins_check_dir() {

	/** This filter is documented in inc/class-wp-members.php */
	$folder = apply_filters( 'wpmem_dropin_folder', WPMEM_DROPIN_DIR );
	$check  = false;
	if ( file_exists( $folder ) ) {
		$file   = $folder . '.htaccess';
		if ( ! file_exists ( $file ) ) {
			$check = wpmem_dropins_create_htaccess( $file );
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
			$check = ( false === $check ) ? wpmem_dropins_create_htaccess( $file ) : $check;
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
function wpmem_dropins_create_htaccess( $file ) {
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
function wpmem_a_render_dropins_table() {
	global $wpmem; 
	
	// Get the dropin folder.
	$folder = apply_filters( 'wpmem_dropin_folder', WPMEM_DROPIN_DIR );

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
				'dropin_name'        => $file_data['Dropin Name'],
				'dropin_file'        => $filename,
				'dropin_version'     => $file_data['Version'],
				'dropin_description' => $file_data['Dropin Description'],
			);
		}
	}

	// Set up table.
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

/**
 * Extends the WP_List_Table to create a table of dropin files.
 *
 * @since 3.1.9
 */
class WP_Members_Dropins_Table extends WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @since 3.1.9
	 */
	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular'  => 'dropin',
				'plural'    => 'dropins',
				'ajax'      => false,
		) );
		
		$this->dropins = get_option( 'wpmembers_dropins', array() ); //print_r( $this->dropins );
	}
	
	/**
	 * Checkbox at start of row.
	 *
	 * @since 3.1.9
	 *
	 * @param $item
	 * @return string The checkbox.
	 */
	function column_cb( $item ) {
		global $wpmem;
		$checked = checked( true, in_array( $item['dropin_file'], $wpmem->dropins_enabled ), false );
		//return sprintf( '<input type="checkbox" name="delete[]" value="%s" title="%s" />', $item['dropin_file'], __( 'delete', 'wp-members' ) );
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" %s />', $this->_args['singular'], $item['dropin_file'], $checked );
	}

	/**
	 * Returns table columns.
	 *
	 * @since 3.1.9
	 *
	 * @return array
	 */
	function get_columns() {
		return array(
			'cb'                 =>  '<input type="checkbox" />',
			'dropin_name'        => __( 'Name',        'wp-members' ),
			'dropin_file'        => __( 'File',        'wp-members' ),
			'dropin_version'     => __( 'Version',     'wp-members' ),
			'dropin_description' => __( 'Description', 'wp-members' ),
		);
	}

	/**
	 * Set up table columns.
	 *
	 * @since 3.1.9
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
	}

	/**
	 * Iterates through the columns
	 *
	 * @since 3.1.9
	 *
	 * @param  array  $item
	 * @param  string $column_name
	 * @return string $item[ $column_name ]
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			default:
	  		return $item[ $column_name ];
		}
	}

	/**
	 * Sets actions in the bulk menu.
	 *
	 * @since 3.1.9
	 *
	 * @return array $actions
	 */
	function get_bulk_actions() {
		$actions = array(
			//'delete' => __( 'Delete Selected', 'wp-members' ),
			'save'   => __( 'Save Settings', 'wp-members' ),
		);
		return $actions;
	}

	/**
	 * Handles "delete" column - checkbox
	 *
	 * @since 3.1.9
	 *
	 * @param  array  $item
	 * @return string 
	 */
	function column_delete( $item ) {

	}
	
	/**
	 * Sets rows so that they have field IDs in the id.
	 *
	 * @since 3.1.9
	 *
	 * @global wpmem
	 * @param  array $columns
	 */
	function single_row( $columns ) {
		echo '<tr id="list_items_' . $columns['dropin_name'] . '" class="list_item" list_item="' . $columns['dropin_name'] . '">';
		echo $this->single_row_columns( $columns );
		echo "</tr>\n";
	}
	
	public function process_bulk_action() {
		
		global $wpmem;
	
		//nonce validations,etc
		
		$dir_chk = wpmem_dropins_check_dir();
		
		//echo ( $dir_chk ) ? '.htaccess OK!' : 'NO .htaccess!!!';
	
		$action = $this->current_action();
	
		switch ( $action ) {
	
			case 'delete':
	
				// Do whatever you want
				//wp_redirect( esc_url( add_query_arg() ) );
				break;
				
			case 'save':
				$settings = array();
				//echo "SAVING SETTINGS";print_r( $_REQUEST['dropin'] );
				if ( wpmem_get( 'dropin' ) ) {
					foreach( wpmem_get( 'dropin' ) as $dropin ) {
						$settings[] = $dropin;
					}
					update_option( 'wpmembers_dropins', $settings, true );
				} else {
					delete_option( 'wpmembers_dropins' );
				}
				$wpmem->dropins_enabled = $settings;
				echo '<div id="message" class="message"><p><strong>' . __( 'WP-Members Dropin settings were updated', 'wp-members' ) . '</strong></p></div>';
				break;
	
			default:
				// do nothing or something else
				return;
				break;
		}
		return;
	}
	
}

// End of file.