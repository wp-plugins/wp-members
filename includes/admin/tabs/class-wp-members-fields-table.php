<?php
/**
 * WP-Members WP_Members_Fields_Table class
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2022  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2022
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
	
	private $excludes = array( 'user_registered', '_wpmem_user_confirmed', 'active', 'wpmem_reg_ip', 'exp_type', 'expires', 'user_id' );
	
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

	function column_meta( $item ) {
		if ( '' == $item['edit'] ) {
			return $item['meta'];
		} else {
			$link = add_query_arg( array(
				'page'  => 'wpmem-settings',
				'tab'   => 'fields',
				'mode'  => 'edit',
				'edit'  => 'field',
				'field' => $item['meta'],
			), admin_url( 'options-general.php' ) );
			return '<a href="' . $link . '"><span class="dashicons dashicons-edit"></span></a> <a href="' . $link . '" data-tooltip="' . __( 'Edit this field', 'wp-members' ) . '">' . $item['meta'] . '</a>';
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
		$columns = array(
			'cb'   =>  '<input type="checkbox"  data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '" />',
			'label'    => __( 'Display Label', 'wp-members' ),
			'meta'     => __( 'Meta Key',      'wp-members' ),
			'type'     => __( 'Field Type',    'wp-members' ),
			'display'  => '<input name="wpmem_all_fields_display" type="checkbox" id="wpmem_all_fields_display" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> '   . __( 'Registration', 'wp-members' ), // __( 'Registration',  'wp-members' ), @todo Wait until fix
			'req'      => '<input name="wpmem_all_fields_required" type="checkbox" id="wpmem_all_fields_required" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> ' . __( 'Required',     'wp-members' ),
			//'profile'  => '<input name="wpmem_all_fields_profile" type="checkbox" id="wpmem_all_fields_profile" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> '   . __( 'Profile',      'wp-members' ),
			'userscrn' => '<input name="wpmem_all_fields_uscreen" type="checkbox" id="wpmem_all_fields_uscreen" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> '   . __( 'Users',        'wp-members' ),
			'usearch'  => '<input name="wpmem_all_fields_usearch" type="checkbox" id="wpmem_all_fields_usearch" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> '   . __( 'Search',       'wp-members' ),
		);

		/*
		if ( wpmem_is_woo_active() ) {
			if ( wpmem_is_enabled( 'woo/add_checkout_fields' ) ) {
				$columns['wcchkout'] = '<input name="wpmem_all_fields_wcchkout" type="checkbox" id="wpmem_all_fields_wcchkout" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> ' . __( 'WC Chkout', 'wp-members' );
			}
			if ( wpmem_is_enabled( 'woo/add_my_account_fields' ) ) {
				$columns['wcaccount'] = '<input name="wpmem_all_fields_wcaccount" type="checkbox" id="wpmem_all_fields_wcaccount" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> ' . __( 'WC My Acct', 'wp-members' );
			}
			if ( wpmem_is_enabled( 'woo/add_update_fields' ) ) {
				$columns['wcupdate'] = '<input name="wpmem_all_fields_wcupdate" type="checkbox" id="wpmem_all_fields_wcupdate" value="1" data-tooltip="' . __( 'Click to check all', 'wp-members' ) . '"> ' . __( 'WC Update', 'wp-members' );
			}
		}
		*/
		
		$columns['edit'] = '';

		return $columns;
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