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

function wpmem_add_membership_to_post( $membership ) {

}

function wpmem_create_membership( $args ) {

	$default_args = array(
		'post_title' => 'Test',
		'post_name' => '', // sanitized title
		'post_status' => 'publish',
		'post_author' => '', // should get the admin user
		'post_type' => 'wpmem_product',
		'meta_input' => array(
			'wpmem_product_name' => '',
			'wpmem_product_default' => '',
			'wpmem_product_role' => '',
			'wpmem_product_expires' => '',
			'wpmem_product_no_gap' => '',
			'wpmem_product_fixed_period' => '',
			'wpmem_product_message' => '',
			'wpmem_product_child_access' => '',
		),
	);

	$post = get_post( $post_id );
	
	$product_name = wpmem_get( 'wpmem_product_name', false );
	$product_name = ( $product_name ) ? $product_name : $post->post_name;
	update_post_meta( $post_id, 'wpmem_product_name', sanitize_text_field( $product_name ) );
	
	$product_default = wpmem_get( 'wpmem_product_default', false );
	update_post_meta( $post_id, 'wpmem_product_default', ( ( $product_default ) ? true : false ) );
	
	$role_required = wpmem_get( 'wpmem_product_role_required', false );
	if ( ! $role_required ) {
		update_post_meta( $post_id, 'wpmem_product_role', false );
	} else {
		update_post_meta( $post_id, 'wpmem_product_role', sanitize_text_field( wpmem_get( 'wpmem_product_role' ) ) );
	}
	
	$expires = wpmem_get( 'wpmem_product_expires', false );
	if ( ! $expires ) {
		update_post_meta( $post_id, 'wpmem_product_expires', false );
	} else {
		$number  = sanitize_text_field( wpmem_get( 'wpmem_product_number_of_periods' ) );
		$period  = sanitize_text_field( wpmem_get( 'wpmem_product_time_period' ) );
		$no_gap  = sanitize_text_field( wpmem_get( 'wpmem_product_no_gap' ) );
		$expires_array = array( $number . "|" . $period );
		update_post_meta( $post_id, 'wpmem_product_expires', $expires_array );
		if ( $no_gap ) {
			update_post_meta( $post_id, 'wpmem_product_no_gap', 1 );
		} else {
			delete_post_meta( $post_id, 'wpmem_product_no_gap' );
		}
		
		$fixed_period = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period' ) );
		if ( $fixed_period ) {
			
			// Start and end.
			$period_start = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period_start' ) );
			$period_end   = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period_end' ) );
			
			// Is there an entry grace period?
			$grace_number = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period_grace_number', false ) );
			$grace_period = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period_grace_period', false ) );
			$save_fixed_period = $period_start . '-' . $period_end;
			if ( $grace_number && $grace_period ) {
				$save_fixed_period .= '-' . $grace_number . '-' . $grace_period;
			}
			update_post_meta( $post_id, 'wpmem_product_fixed_period', $save_fixed_period );
		} else {
			delete_post_meta( $post_id, 'wpmem_product_fixed_period' );
		}
	}
	
	foreach( $this->get_post_types() as $key => $post_type ) {
		if ( false !== wpmem_get( 'wpmem_product_set_default_' . $key, false ) ) {
			update_post_meta( $post_id, 'wpmem_product_set_default_' . $key, 1 );
		} else {
			delete_post_meta( $post_id, 'wpmem_product_set_default_' . $key );
		}
	}

	$product_message =  wp_kses_post( wpmem_get( 'product_message', false ) );
	if ( false !== $product_message ) {
		if ( '' != $product_message ) {
			update_post_meta( $post_id, 'wpmem_product_message', $product_message );
		} else {
			delete_post_meta( $post_id, 'wpmem_product_message' );
		}
	}
	
	$child_access = intval( wpmem_get( 'wpmem_product_child_access', 0 ) );
	if ( 1 == $child_access ) {
		update_post_meta( $post_id, 'wpmem_product_child_access', $child_access );
	} else {
		delete_post_meta( $post_id, 'wpmem_product_child_access' );
	}		
}