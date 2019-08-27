<?php
/**
 * WP-Members WP_Members_Dropins_Table class
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
			'dropin_enabled'     => __( 'Enabled',     'wp-members' ),
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

		$dir_chk = WP_Members_Admin_Tab_Dropins::check_dir();

		//echo ( $dir_chk ) ? '.htaccess OK!' : 'NO .htaccess!!!';

		$action = $this->current_action();

		switch ( $action ) {

			case 'delete':

				// Do whatever you want
				//wp_safe_redirect( esc_url( add_query_arg() ) );
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