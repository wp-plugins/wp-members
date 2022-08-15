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
 * @return string  Valud of $wpmem->membership->products[ $membership_slug ]['title'] if set, otherwise, $membership_slug.
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