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

/**
 * Returns http:// or https:// depending on ssl.
 *
 * @since 2.9.8
 *
 * @return string https://|http:// depending on whether ssl is being used.
 */
function wpmem_use_ssl() {
	return ( is_ssl() ) ? 'https://' : 'http://';
}

/**
 * Log debugging errors.
 *
 * @since 3.1.2
 * 
 * @param mixed (string|array|object) $log Information to write in the WP debug file.
 */
function wpmem_write_log ( $log ) {
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ) );
	} else {
		error_log( $log );
	}
}

/**
 * String manipulation utility.
 *
 * Manipulates a given string based on the location of another string to return
 * a requested part or parts of the original string.  For extracting a string
 * to get what is before or after, the returned result is a string.  If the
 * string is requested to be "split" by the needle string, an array containing
 * the parts before, after, and the "needle" are returned.
 *
 * @since 3.2.0
 *
 * @param  string       $needle
 * @param  string       $haystack
 * @param  string       $position (before|after|split default: 'after')
 * @param  boolean      $keep_needle (default:true)
 * @return string|array $new {
 *     An array of the original string, as split by the "needle" string.
 *
 *     @type string $before
 *     @type string $after
 *     @type string $needle
 * }
 */
function wpmem_get_sub_str( $needle, $haystack, $position = 'after', $keep_needle = true ) {
	$pos = strpos( $haystack, $needle );
	if ( false === $pos ) {
		return $haystack;
	} else {
		if ( 'before' == $position ) {
			$new = ( substr( $haystack, 0, $pos ) );
			$new = ( $keep_needle ) ? $string . $needle : $new;
		} elseif ( 'after' == $position ) {
			$new = ( substr( $haystack, $pos+strlen( $needle ) ) );
			$new = ( $keep_needle ) ? $needle . $string : $new;
		} elseif ( 'split' == $position ) {
			$before = ( substr( $haystack, 0, $pos ) );
			$after  = ( substr( $haystack, $pos+strlen( $needle ) ) );
			$new    = array(
				'before' => $before,
				'after'  => $after,
				'needle' => $needle,
			);
		}
	}
	return $new;
}