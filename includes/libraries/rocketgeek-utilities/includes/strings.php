<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it useful
 * for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Strings
 * @version    1.0.0
 *
 * @link       https://github.com/rocketgeek/rocketgeek-utilities/
 * @author     Chad Butler <https://butlerblog.com>
 * @author     RocketGeek <https://rocketgeek.com>
 * @copyright  Copyright (c) 2021 Chad Butler
 * @license    Apache-2.0
 *
 * Copyright [2021] Chad Butler, RocketGeek
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