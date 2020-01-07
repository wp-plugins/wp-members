<?php
/**
 * WP-Members WP_Members_Fields_Table class
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
 * Extends the WP_List_Table to create a table of form fields.
 *
 * @since 3.1.8
 */
class WP_Members_Fields_Table extends WP_List_Table {
	
	private $excludes = array( 'user_registered', 'active', 'wpmem_reg_ip', 'exp_type', 'expires', 'user_id' );
	
	private $no_delete = array( 'username', 'user_email', 'first_name', 'last_name', 'user_url' );
	
	/**
	 * Checkbox at start of row.
	 *
	 * @since 3.1.8
	 *
	 * @param $item
	 * @return string The checkbox.
	 */
	function column_cb( $item ) {
		if ( in_array( $item['meta'], $this->no_delete ) || in_array( $item['meta'], $this->excludes ) ) {
			return;
		} else {
			return sprintf( '<input type="checkbox" name="delete[]" value="%s" title="%s" />', $item['meta'], __( 'delete', 'wp-members' ) );
		}
	}

	/**
	 * Returns table columns.
	 *
	 * @since 3.1.8
	 *
	 * @return array
	 */
	function get_columns() {
		return array(
			'cb'   =>  '<input type="checkbox" />',
			'label'    => __( 'Display Label', 'wp-members' ),
			'meta'     => __( 'Meta Key',      'wp-members' ),
			'type'     => __( 'Field Type',    'wp-members' ),
			'display'  => __( 'Display?', 'wp-members' ), // __( 'Registration',  'wp-members' ), @todo Wait until fix
			'req'      => __( 'Required',      'wp-members' ),
			//'profile'  => __( 'Profile',       'wp-members' ), @todo Wait until fix
			'userscrn' => __( 'Users Screen',  'wp-members' ),
			'usearch'  => __( 'Users Search',  'wp-members' ),
			'edit'     => __( 'Edit',          'wp-members' ),
			'sort'     => __( 'Sort',          'wp-members' ),
		);
	}

	/**
	 * Set up table columns.
	 *
	 * @since 3.1.8
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Iterates through the columns
	 *
	 * @since 3.1.8
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
	 * @since 3.1.8
	 *
	 * @return array $actions
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete Selected', 'wp-members' ),
			'save'   => __( 'Save Settings', 'wp-members' ),
		);
		return $actions;
	}

	/**
	 * Handles "delete" column - checkbox
	 *
	 * @since 3.1.8
	 *
	 * @param  array  $item
	 * @return string 
	 */
	function column_delete( $item ) {
		$can_delete = ( $item['meta_key'] == 'user_nicename' || $item['meta_key'] == 'display_name' || $item['meta_key'] == 'nickname' ) ? true : false;
		return ( ( $can_delete ) || ! $item['native'] ) ? sprintf( $item['native'] . '<input type="checkbox" name="field[%s]" value="delete" />', $item['meta'] ) : '';
	}
	
	/**
	 * Sets rows so that they have field IDs in the id.
	 *
	 * @since 3.1.8
	 *
	 * @global wpmem
	 * @param  array $columns
	 */
	function single_row( $columns ) {
		if ( in_array( $columns['meta'], $this->excludes ) ) {
			echo '<tr id="' . esc_attr( $columns['meta'] ) . '" class="nodrag nodrop">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		} else {
			echo '<tr id="list_items_' . esc_attr( $columns['order'] ) . '" class="list_item" list_item="' . esc_attr( $columns['order'] ) . '">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		}
	}
	
	public function process_bulk_action() {

	//nonce validations,etc
	
		$action = $this->current_action();
	
		switch ( $action ) {
	
			case 'delete':
	
				// Do whatever you want
				wp_safe_redirect( esc_url( add_query_arg() ) );
				break;
	
			default:
				// do nothing or something else
				return;
				break;
		}
		return;
	}
	
}