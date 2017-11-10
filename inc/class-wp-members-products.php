<?php
/**
 * The WP_Members Membership Products Class.
 *
 * This is the main WP_Members object class. This class contains functions
 * for loading settings, shortcodes, hooks to WP, plugin dropins, constants,
 * and registration fields. It also manages whether content should be blocked.
 *
 * @package WP-Members
 * @subpackage WP_Members Membership Products Object Class
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Products {

	public $post_meta = '_wpmem_products';
	public $post_stem = '_wpmem_products_';
	public $product_detail = array();
	
	function __construct() {
		
		$this->load_products();
		
		add_filter( 'wpmem_securify', array( $this, 'check_access' ) );
	}
	
	function load_products() {
		global $wpdb;
		$sql    = "SELECT ID, post_title, post_name FROM " . $wpdb->prefix . "posts WHERE post_type = 'wpmem_mem_plan' AND post_status = 'publish';";
		$result = $wpdb->get_results( $sql );
		$this->products = array();
		foreach ( $result as $plan ) {
			$this->products[ $plan->post_name ] = $plan->post_title;
		}
	}

	/**
	 * Sets up custom access restriction by product.
	 *
	 * @since 3.2.0
	 *
	 * @global object $post
	 * @global object $wpmem
	 * @param  string $content
	 * @return string $content
	 */
	function check_access( $content ) {
		
		global $post, $wpmem;
		// Is the user logged in and is this blocked content?
		if ( is_user_logged_in() && wpmem_is_blocked() ) {
			// Get the post access products.
			$post_products = get_post_meta( $post->ID, $wpmem->membership->post_meta, true );
			// If the post is restricted to a product.
			if ( $post_products ) {
				// The error message for invalid users.
				// @todo Filter this and also translate it.
				$error_msg = 'Sorry, you do not have access to this page.';
				
				// @todo This is the nuts and bolts - work around whether a user has access
				// to this product or not. 
				if ( $wpmem->user->has_access( $post_products ) ) {
					return $content;
				}
				return $error_msg;
			} else {
				// Content that has no product restriction.
				return $content;
			}
		}
		// Return unfiltered content for all other cases.
		return $content;
	}
	
	/**
	 * Register Membership Plans Custom Post Type
	 *
	 * @since 3.2.0
	 */
	function add_cpt() {
		
		global $wpmem;
		
		$singular = __( 'Product', 'license-wp' );
		$plural   = __( 'Products', 'license-wp' );

		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => __( 'Memberships', 'wp-members' ),
			'all_items'             => sprintf( __( 'All %s', 'wp-members' ), $plural ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'wp-members' ), $singular ),
			'add_new'               => __( 'Add New', 'wp-members' ),
			'new_item'              => sprintf( __( 'New %s', 'wp-members' ), $singular ),
			'edit_item'             => sprintf( __( 'Edit %s', 'wp-members' ), $singular ),
			'update_item'           => sprintf( __( 'Update %s', 'wp-members' ), $singular ),
			'view_item'             => sprintf( __( 'View %s', 'wp-members' ), $singular ),
			'view_items'            => sprintf( __( 'View %s', 'wp-members' ), $plural ),
			'search_items'          => sprintf( __( 'Search %s', 'wp-members' ), $plural ),
			'not_found'             => __( 'Not found', 'wp-members' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wp-members' ),
			'insert_into_item'      => __( 'Insert into item', 'wp-members' ),
			'publish'               => sprintf( __( 'Save %s Details', 'wp-members' ), $singular ),
		);
		$args = array(
			'label'                 => __( 'Membership Product', 'wp-members' ),
			'description'           => __( 'WP-Members Membership Products', 'wp-members' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'page-attributes' ),
			'hierarchical'          => true,
			'public'                => false,
			'show_ui'               => ( $wpmem->enable_products ) ? true : false,
			'show_in_menu'          => ( $wpmem->enable_products ) ? true : false,
			'menu_position'         => 58,
			'menu_icon'             => 'dashicons-groups',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'query_var'             => 'membership_product',
			'rewrite'               => false,
			'capability_type'       => 'page',
			'show_in_rest'          => false,
			//'register_meta_box_cb'  => '', // callback for meta box
		);
		register_post_type( 'wpmem_mem_plan', $args );
	}
	
}