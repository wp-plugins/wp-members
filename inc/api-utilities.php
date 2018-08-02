<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2018
 */

/**
 * Sets an array of user meta fields to be excluded from update/insert.
 *
 * @since 2.9.3
 * @since Unknown Now a wrapper for get_excluded_fields().
 *
 * @param  string $tag A tag so we know where the function is being used.
 * @return array       Array of fields to be excluded from the registration form.
 */
function wpmem_get_excluded_meta( $tag ) {
	global $wpmem;
	return $wpmem->excluded_fields( $tag );
}

