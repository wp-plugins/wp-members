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

	/**
	 * Product post type.
	 *
	 * @since  3.4.0
	 * @access public
	 * @var    string
	 */
	public $post_type = 'wpmem_product';
	
	/**
	 * Product meta key.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    string
	 */
	public $post_meta = '_wpmem_products';

	/**
	 * Product meta key stem.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    string
	 */
	public $post_stem = '_wpmem_products_';

	/**
	 * Product details.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    array {
	 *     Array of membership product information.
	 *
	 *     @type array $product_slug {
	 *         Array of membership product settings.
	 *
	 *         @type string $title   The product title (user view).
	 *         @type string $role    User role, if a role is required.
	 *         @type string $name    The product slug.
	 *         @type string $default
	 *         @type array  $expires {
	 *              If the membership has expiration periods.
	 *
	 *              $type string number|period Number of periods|Period (year/month/week/day).
	 *         }
	 *     }
	 * }
	 */
	public $products = array();

	/**
	 * Product meta keyed by ID.
	 *
	 * @since  3.2.4
	 * @access public
	 * @var    array {
	 *     Array of membership products keyed by CPT ID.
	 *
	 *     @type string $ID The membership product slug.
	 * }
	 */
	public $product_by_id = array();
	
	/**
	 * Class constructor.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem The WP_Members object class.
	 */
	function __construct() {
		
		$this->load_products();
		
		add_filter( 'wpmem_securify', array( $this, 'product_access' ) );
	}
	
	/**
	 * Loads product settings.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpdb The WPDB object class.
	 */
	function load_products() {
		global $wpdb;
		$sql = "SELECT ID, post_title, post_name FROM " 
			. $wpdb->prefix 
			. "posts WHERE post_type = 'wpmem_product' AND post_status = 'publish';";
		$result = $wpdb->get_results( $sql );
		foreach ( $result as $plan ) {
			$this->product_by_id[ $plan->ID ] = $plan->post_name;
			$this->products[ $plan->post_name ]['title'] = $plan->post_title;			
			$post_meta = get_post_meta( $plan->ID );
			foreach ( $post_meta as $key => $meta ) {
				if ( false !== strpos( $key, 'wpmem_product' ) ) {
					if ( $key == 'wpmem_product_expires' ) {
						$meta[0] = unserialize( $meta[0] );
					}
					$this->products[ $plan->post_name ][ str_replace( 'wpmem_product_', '', $key ) ] = $meta[0];
				}
			}
		}
	}
	
	/**
	 * Gets products assigned to a post.
	 *
	 * @since 3.2.4
	 *
	 * @param  integer $post_id
	 * @return array   $products {
	 *     Membership product slugs the post is restricted to.
	 *
	 *     @type string $slug
	 * }
	 */
	function get_post_products( $post_id ) {
		$products = get_post_meta( $post_id, $this->post_meta, true );
		return $products;
	}

	/**
	 * Gets default membership products.
	 *
	 * @since 3.3.0
	 *
	 * @return array $defaults
	 */
	function get_default_products() {
		// Get any default membership products.
		$args = array(
			'numberposts' => -1,
			'post_type'   => $this->post_type,
			'meta_key'    => 'wpmem_product_default',
			'meta_value'  => 1,
			'fields'      => array( 'post_name' ),
		);
		$default_products = get_posts( $args );
		$defaults = array();
		if ( $default_products ) {
			foreach ( $default_products as $product ) {
				$defaults[] = $product->post_name;
			}
		}
		return $defaults;
	}

	/**
	 * Sets up custom access restriction by product.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Merged check_product_access() logic for better messaging.
	 *
	 * @global object $post    The WordPress Post object.
	 * @global object $wpmem   The WP_Members object class.
	 * @param  string $content
	 * @return string $content
	 */
	function product_access( $content ) {
		
		global $post, $wpmem;
		// Is the user logged in and is this blocked content?
		if ( is_user_logged_in() && wpmem_is_blocked() ) {

			// Get the post access products.
			$post_products = $this->get_post_products( $post->ID );
			// If the post is restricted to a product.
			if ( $post_products ) {
				if ( wpmem_user_has_access( $post_products ) ) {
					$access = true;
				} else {
					// The error message for invalid users.
					$access = false;
				}
			} else {
				// Content that has no product restriction.
				$access = true;
			}
			
			// Handle content.
			/**
			 * Filter the product restricted message.
			 *
			 * @since 3.2.3
			 *
			 * @param string                The message.
			 * @param array  $post_products {
			 *     Membership product slugs the post is restricted to.
			 *
			 *     @type string $slug
			 * }
			 */
			$content = ( $access ) ? $content : apply_filters( 'wpmem_product_restricted_msg', $wpmem->get_text( 'product_restricted' ), $post_products );
			
			// Handle comments.
			if ( ! $access ) {
				add_filter( 'wpmem_securify_comments', '__return_false' );
			}
		}
		// Return unfiltered content for all other cases.
		return $content;
	}
	
	/**
	 * Register Membership Plans Custom Post Type
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem The WP_Members object class.
	 */
	function add_cpt() {
		
		global $wpmem;
		
		$singular = __( 'Product', 'wp-members' );
		$plural   = __( 'Products', 'wp-members' );

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
		register_post_type( $this->post_type, $args );
	}
	
	/**
	 * Get all posts tagged to a specified product.
	 *
	 * @since 3.3.0
	 *
	 * @global  stdClass  $wpdb
	 *
	 * @param   string    $product_meta
	 * @return  array     $post_ids
	 */
	function get_all_posts( $product_meta ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT post_id 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = %s
			", $this->post_stem . $product_meta ), ARRAY_N );
		foreach ( $results as $result ) {
			$post_ids[] = $result[0];
		}
		return $post_ids;
	}
}