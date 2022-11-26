<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2022  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2022
 */

/**
 * Gets all posts by product key.
 *
 * @since Unknown
 * @since 3.4.5 Alias of wpmem_get_membership_post_list().
 *
 * @global  stdClass  $wpmem
 * @param   string    $product_key
 * @return  array
 */
function wpmem_get_product_post_list( $product_key ) {
	return wpmem_get_membership_post_list( $product_key );
}

/**
 * Gets all posts by product key.
 *
 * @since 3.4.5
 *
 * @global  stdClass  $wpmem
 * @param   string    $membership_key
 * @return  array
 */
function wpmem_get_membership_post_list( $membership_key ) {
	global $wpmem;
	return $wpmem->membership->get_all_posts( $membership_key );
}

/**
 * Gets the membership products for a given post.
 *
 * @since 3.3.7
 * @since 3.4.5 Alias of wpmem_get_post_memberships().
 *
 * @global  stdClass  $wpmem
 * @param   integer   $post_id
 * @return  array
 */
function wpmem_get_post_products( $post_id ) {
	return wpmem_get_post_memberships( $post_id );
}

/**
 * Gets the membership products for a given post.
 *
 * @since 3.4.5
 *
 * @global  stdClass  $wpmem
 * @param   integer   $post_id
 * @return  array
 */
function wpmem_get_post_memberships( $post_id ) {
	global $wpmem;
	return $wpmem->membership->get_post_products( $post_id );
}

/**
 * Gets access message if user does not have required membership.
 *
 * @since 3.4.0
 *
 * @global  stdClass  $wpmem
 * @param   array     $post_products
 * @return  string    $message
 */
function wpmem_get_access_message( $post_products ) {
	global $wpmem;
	return $wpmem->membership->get_access_message( $post_products );
}

/**
 * Gets all memberships for the site.
 * Alias of wpmem_get_memberships().
 * 
 * @since Unknown
 * 
 * @return array
 */
function wpmem_get_products() {
	return wpmem_get_memberships();
}

/**
 * Gets all memberships for the site.
 * 
 * @since Unknown
 * 
 * @return array
 */
function wpmem_get_memberships() {
	global $wpmem;
	return ( ! empty( $wpmem->membership->products ) ) ? $wpmem->membership->products : false;
}

/**
 * Get array of memberships keyed by ID.
 * 
 * @since Unknown
 * 
 * return array
 */
function wpmem_get_memberships_ids() {
	global $wpmem;
	return array_flip( $wpmem->membership->product_by_id );
}

/**
 * Get membership display title by slug.
 * 
 * @since 3.4.5
 * 
 * @param  string  $membership_slug
 * @return string  Value of $wpmem->membership->products[ $membership_slug ]['title'] if set, otherwise, $membership_slug.
 */
function wpmem_get_membership_name( $membership_slug ) {
	global $wpmem;
	return ( isset( $wpmem->membership->products[ $membership_slug ]['title'] ) ) ? $wpmem->membership->products[ $membership_slug ]['title'] : $membership_slug;
}

/**
 * Get meta key for membership (with stem).
 * 
 * @since 3.4.5
 * 
 * @param  string  $membership_slug
 * @return string
 */
function wpmem_get_membership_meta( $membership_slug ) {
	global $wpmem;
	return $wpmem->membership->post_stem . $membership_slug;
}

/**
 * Adds a membership to a post.
 * 
 * @since 3.4.6
 * 
 * @param  string  $membership_meta
 * @param  int     $post_id
 */
function wpmem_add_membership_to_post( $membership_meta, $post_id ) {
	// Handle single or array.
	if ( is_array( $membership_meta ) ) {
		$products = wpmem_sanitize_array( $membership_meta );
	} else {
		$products = array( $membership_meta );
	}

	// Update post meta with restriction info.
	update_post_meta( $post_id, $membership_meta, $products );

	// Set meta for each individual membership.
	foreach ( wpmem_get_memberships() as $key => $value ) {
		if ( in_array( $key, $products ) ) {
			update_post_meta( $post_id, wpmem_get_membership_meta( $key ), 1 );
		}
	}
}

/**
 * Adds a membership to an array of post IDs.
 * 
 * @since 3.4.6
 * 
 * @param  string         $membership_meta
 * @param  string|array   $post_ids
 */
function wpmem_add_membership_to_posts( $membership_meta, $post_ids ) {
	// Make sure $post_ids is an array (prepare comma separated values)
	$posts_array = ( ! is_array( $post_ids ) ) ? explode( ",", $post_ids ) : $post_ids;
	
	// Run wpmem_add_membership_to_post() for each ID.
	foreach ( $posts_array as $ID ) {
		wpmem_add_membership_to_post( $membership_meta, $ID );
	}
}

/**
 * Create a membership.
 * 
 * @since 3.4.6
 * 
 * @param  array  $args {
 *     Parameters for creating the membership CPT.
 * 
 *     @type string $title      User readable name of membership.
 *     @type string $name       Sanitized title of the membership to be used as the meta key.
 *     @type string $status     Published status: publish|draft (default: publish)
 *     @type int    $author     User ID of membership author, Optional, defaults to site admin.
 *     @type array  $meta_input
 *         Meta fields for membership CPT (not all are required).
 * 
 *         @type string $name         The sanitized title of the membership.
 *         @type string $default
 *         @type string $role         Roles if a role is required.
 *         @type string $expires      Expiration period if used (num|per).
 *         @type int    $no_gap       If renewal is "no gap" renewal.
 *         @type string $fixed_period (start|end|grace_num|grace_per)
 *         @type int    $set_default_{$key}
 *         @type string $message      Custom message for restriction.
 *         @type int    $child_access If membership hierarchy is used.
 *     }
 * }
 * @return mixed  $post_id|WP_Error
 */
function wpmem_create_membership( $args ) {

	// Get the admin user for default post_author.
	$admin_email = get_option( 'admin_email' );
	$admin_user  = get_user_by( 'email', $admin_email );

	// Set up post args.
	$pre_postarr = array();
	foreach ( $args as $key => $value ) {
		if ( 'meta_input' == $key ) {
			foreach( $value as $meta_key => $meta_value ) {
				$pre_postarr['meta_input'][ 'wpmem_product_' . $meta_key ] = $meta_value;
			}
		} else {
			$pre_postarr[ 'post_' . $key ] = $value;
		}
	}

	// Setup defaults.
	$default_args = array(
		'post_title'  => '',
		'post_name'   => ( isset( $pre_postarr['post_name'] ) ) ? sanitize_title( $pre_postarr['post_name'] ) : sanitize_title( $pre_postarr['post_title'] ),
		'post_status' => 'publish',
		'post_author' => $admin_user->ID,
		'post_type'   => 'wpmem_product',
		'meta_input'  => array(
			'wpmem_product_name'    =>  ( isset( $pre_postarr['meta_input']['wpmem_product_name'] ) ) ? sanitize_title( $pre_postarr['meta_input']['wpmem_product_name'] ) : ( ( isset( $pre_postarr['post_name'] ) ) ? sanitize_title( $pre_postarr['post_name'] ) : sanitize_title( $pre_postarr['post_title'] ) ),
			'wpmem_product_default' => false,
			'wpmem_product_role'    => false,
			'wpmem_product_expires' => false,
		),
	);

	/**
	 * Filter the defaults.
	 * 
	 * @since 3.4.6
	 * 
	 * @param array $default_args {
	 *     Mmembership CPT params for wp_insert_post().
	 * 
	 *     @type string $post_title      User readable name of membership.
	 *     @type string $post_name       Sanitized title of the membership to be used as the meta key.
	 *     @type string $post_status     Published status: publish|draft (default: publish)
	 *     @type int    $post_author     User ID of membership author, Optional, defaults to site admin.
	 *     @type string $post_type       Should not change this: default: wpmem_product.
	 *     @type array  $meta_input
	 *         Meta fields for membership CPT (not all are required).
	 * 
	 *         @type string $wpmem_product_name         The sanitized title of the membership.
	 *         @type string $wpmem_product_default
	 *         @type string $wpmem_product_role         Roles if a role is required.
	 *         @type string $wpmem_product_expires      Expiration period if used (num|per).
	 * 
	 *         The following are optional and are not passed in the default args but could be returned by filter.
	 * 
	 *         @type int    $wpmem_product_no_gap       If renewal is "no gap" renewal.
	 *         @type string $wpmem_product_fixed_period (start|end|grace_num|grace_per)
	 *         @type int    $wpmem_product_set_default_{$wpmem_product_key}
	 *         @type string $wpmem_product_message      Custom message for restriction.
	 *         @type int    $wpmem_product_child_access If membership hierarchy is used.
	 *     }
	 * }
	 */
	$default_args = apply_filters( 'wpmem_create_membership_defaults', $default_args );

	if ( isset( $pre_postarr['meta_input']['wpmem_product_message'] ) ) {
		$pre_postarr['meta_input']['wpmem_product_message'] = wp_kses_post( $pre_postarr['meta_input']['wpmem_product_message'] );
	}

	// Merge with defaults.
	$postarr = rktgk_wp_parse_args( $pre_postarr, $default_args );

	// Insert the new membership as a CPT.
	$post_id = wp_insert_post( $postarr );

	// wp_insert_post() returns post ID on success, WP_Error on fail.
	return $post_id;
}