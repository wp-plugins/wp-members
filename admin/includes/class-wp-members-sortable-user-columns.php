<?php
/**
 * WP-Members Sortable Columns Class.
 *
 * @since 3.0
 */

if( ! class_exists( 'WP_Members_Sortable_User_Columns' ) ):
/**
 * Class to sort WP-Members custom user meta columns
 */
class WP_Members_Sortable_User_Columns 
{
	var $defaults = array(
			'login',
			'nicename', 
			'email', 
			'url', 
			'registered',
			'user_login',
			'user_nicename', 
			'user_email', 
			'user_url', 
			'user_registered',
			'display_name',
			'name',
			'post_count',
			'ID',
			'id'
		);
	
	/**
	 * Initial contruct function.
	 *
	 * @since 3.0
	 *
	 * @param array $args
	 */
	function __construct( $args ) {
		$this->args = $args;
		//add_action( 'pre_user_query', array(&$this, 'query' ) );
		add_action( 'manage_users_custom_column', array( &$this, 'column_content' ), 10, 3 );
		add_filter( 'manage_users_columns', array( &$this, 'add_to_columns' ) );
		add_filter( 'manage_users_sortable_columns', array( &$this, 'make_column_sortable') );
		
		add_filter( 'request', array( &$this, 'custom_column_orderby' ) );
	}
	
	/**
	 * Prequery function.
	 *
	 * @since 3.0
	 * 
	 * @param string $query
	 */
	function query( $query ) {
		global $wpdb;
		$vars = $query->query_vars;
		if ( in_array( $vars['orderby'], $this->defaults ) ) {
			return;
		}
		$title = $this->args[ $vars['orderby'] ];
		if ( ! empty( $title ) ) {
			$query->query_from .= " LEFT JOIN " . $wpdb->prefix . "usermeta m ON (" . $wpdb->prefix . "users.ID = m.user_id AND m.meta_key = '" . $vars['orderby'] . "')";
			$query->query_orderby = "ORDER BY m.meta_value " . $vars['order'];
		}
	}
	
	/**
	 * Adds selected WP-Members columns to the Users > All Users columns.
	 * 
	 * @since 3.0
	 * 
	 * @param array $columns
	 */
	function add_to_columns( $columns ) {
		foreach ( $this->args as $key => $value ) {
			$columns[ $key ] = $value;
		}
		return $columns;
	}
	
	/**
	 * Sets selected WP-Members columns as sortable.
	 * 
	 * @since 3.0
	 * 
	 * @param array $columns
	 */
	function make_column_sortable( $columns ) {
		$custom = array();
		foreach ( $this->args as $key => $value ) {
			$custom[ $key ] = $key;
		}
		return wp_parse_args( $custom, $columns );
	}
	
	/**
	 * Returns the column content value for WP-Members selected columns.
	 * 
	 * @since 3.0
	 * 
	 * @param string $value
	 * @param string $column_name
	 * @param int    $user_id
	 */
	function column_content( $value, $column_name, $user_id ) {
		foreach ( $this->args as $key => $val ) {
			if ( $column_name == $key ) {
				$user = get_userdata( $user_id );
				return $user->$column_name;
			}
		}
		return $value;
	}
	
	/**
	 * Sort custom column.
	 *
	 * @since 3.0.5
	 */
	function custom_column_orderby( $vars ) {
		foreach ( $this->args as $key => $val ) {
			if ( isset( $vars[ $key ] ) && $val == $vars[ $key ] ) {
				$vars = array_merge( $vars, array(
					'meta_key' => $key,
					'orderby'  => $key,
				) );
			}
		}
		return $vars;
	}	
}
endif;