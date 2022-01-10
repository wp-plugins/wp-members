<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it 
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Arrays
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
if ( ! function_exists( 'rktgk_compare_array_values' ) ):
/**
 * Compares two arrays, disregarding order.
 *
 * @since 1.0.0
 *
 * @param   array   $a
 * @param   array   $b
 * @return  boolean
 */
function rktgk_compare_array_values( array $a, array $b ) {
    // check size of both arrays
    if ( count( $a ) !== count( $b ) ) {
        return false;
    }

    foreach ( $b as $key => $b_value ) {

        // check that expected value exists in the array
        if ( ! in_array( $b_value, $a, true ) ) {
            return false;
        }

        // check that expected value occurs the same amount of times in both arrays
        if ( count( array_keys( $a, $b_value, true ) ) !== count( array_keys( $b, $b_value, true ) ) ) {
            return false;
        }

    }

    return true;
}
endif;

if ( ! function_exists( 'rktgk_array_insert' ) ):
/**
 * Inserts array items at a specific point in an array.
 *
 * @since 1.0.0
 *
 * @param  array  $array Original array.
 * @param  array  $new   Array of new items to insert into $array.
 * @param  string $key   Array key to insert new items before or after.
 * @param  string $loc   Location to insert relative to $key (before|after) default:after.
 * @return array         Original array with new items inserted.
 */
function rktgk_array_insert( array $array, array $new, $key, $loc = 'after' ) {
	$keys = array_keys( $array );
	if ( 'before' == $loc ) {
		$pos = (int) array_search( $key, $keys );
	} else {
		$index = array_search( $key, $keys );
		$pos = ( false === $index ) ? count( $array ) : $index + 1;
	}
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}
endif;
