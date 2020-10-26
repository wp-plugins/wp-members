<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2020  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2020
 */

/**
 * Gets all posts by product key.
 *
 * @since Unknown
 *
 * @global  stdClass  $wpmem
 * @param   string    $product_key
 * @return  array
 */
function wpmem_get_product_post_list( $product_key ) {
	global $wpmem;
	return $wpmem->membership->get_all_posts( $product_key );
}

/**
 * Gets the membership products for a given post.
 *
 * @since 3.3.7
 *
 * @global  stdClass  $wpmem
 * @param   integer   $post_id
 * @return  array
 */
function wpmem_get_post_products( $post_id ) {
	global $wpmem;
	return $wpmem->membership->get_post_products( $post_id );
}