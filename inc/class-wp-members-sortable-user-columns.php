<?php
/**
 * WP-Members Sortable Columns Class.
 *
 * @since 3.0
 */
class WP_Members_Sortable_User_Columns {

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

	function __construct( $args ) {
		$this->args = ( is_array( $args ) ) ? $args : array();
		add_action( 'pre_user_query', array( &$this, 'query' ) );
		add_action( 'manage_users_custom_column', array( &$this, 'content' ), 10, 3 );
		add_filter( 'manage_users_columns', array( &$this, 'columns' ) );
		add_filter( 'manage_users_sortable_columns', array( &$this, 'sortable') );
	}
	
	function query( $query ) {
		$vars = $query->query_vars;
		if ( in_array( $vars['orderby'], $this->defaults ) ) {
			return;
		}
		$title = $this->args[ $vars['orderby'] ];
		if ( ! empty( $title ) ) {
			$query->query_from .= " LEFT JOIN wp_usermeta m ON (wp_users.ID = m.user_id  AND m.meta_key = '" . $vars['orderby'] . "')";
			$query->query_orderby = "ORDER BY m.meta_value ".$vars['order'];
		}
	}
	
	function columns( $columns ) {
		foreach ( $this->args as $key => $value ) {
			$columns[ $key ] = $value;
		}
		return $columns;
	}
	
	function sortable( $columns ) {
		foreach ( $this->args as $key => $value ) {
			$columns[ $key ] = $key;
		}
		return $columns;
	}
	
	function content( $value, $column_name, $user_id ) {
		$user = get_userdata( $user_id );
		return $user->$column_name;
	}

}