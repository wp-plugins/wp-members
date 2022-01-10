<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it 
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Strings
 * @version    1.0.0
 *
 * @link       https://github.com/rocketgeek/rocketgeek-utilities/
 * @author     Chad Butler <https://butlerblog.com>
 * @author     RocketGeek <https://rocketgeek.com>
 * @copyright  Copyright (c) 2022 Chad Butler
 * @license    Apache-2.0
 *
 * Copyright [2022] Chad Butler, RocketGeek
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     https://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


if ( ! function_exists( 'rktgk_string_to_boolean' ) ):
function rktgk_string_to_boolean( $str, $return_string = true ) {
	return rktgk_str_to_bool( $str, $return_string );
}
endif;

if ( ! function_exists( 'rktgk_str_to_bool' ) ):
/**
 * Converts a true/false string to a boolean.
 * Useful for shortcodes that receive args as strings
 * but need a true/false or 1/0 boolean.
 */
function rktgk_str_to_bool( $str, $return_string = true ) {
	switch ( $str ) {
		case ( is_bool( $str ) ):
			// If the value is already cast as a boolean.
			return (bool)$str;
			break;
		case ( is_string( $str ) && 'true' === $str ):
		case ( 1 === $str ):
			// If the value is "true" or 1 as a string or integer.
			return true;
			break;
		case ( is_string( $str ) && 'false' === $str ):
		case ( 0 === $str ):
			// If the value is "false" or 0 as a string or integer.
			return false;
			break;
		default:
			// If it doesn't fit anything, return false.
			return ( true === $return_string ) ? $str : false;
			break;
	}
}
endif;

if ( ! function_exists( 'rktgk_get_sub_str' ) ):
/**
 * String manipulation utility.
 *
 * Manipulates a given string based on the location of another string to return
 * a requested part or parts of the original string.  For extracting a string
 * to get what is before or after, the returned result is a string.  If the
 * string is requested to be "split" by the needle string, an array containing
 * the parts before, after, and the "needle" are returned.
 *
 * @since 1.0.0
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
function rktgk_get_sub_str( $needle, $haystack, $position = 'after', $keep_needle = true ) {
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
endif;