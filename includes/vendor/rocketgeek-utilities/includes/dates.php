<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it 
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Dates
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

if ( ! function_exists( 'rktgk_format_date' ) ):
/**
 * Display a localized date based on the WP date format setting.
 *
 * @since 1.0.0
 *
 * @param mixed $args
 * @return date $date
 */
function rktgk_format_date( $args ) {
	if ( ! is_array( $args ) ) {
		$args = array( 'date' => $args );
	}
	
	$defaults = array( 
		'date_format' => get_option( 'date_format' ),
		'time_format' => get_option( 'time_format' ),
		'localize'    => true,
		'timestamp'   => true,
		'date_only'   => true,
	);
	
	$args = wp_parse_args( $args, $defaults );

	/**
	 * Filter the date display and format settings.
	 *
	 * @since 1.0.0
	 *
	 * @param arrag $args
	 */
	$args = apply_filters( 'rktgk_format_date_args', $args );
	
	$date_format = ( true === $args['date_only'] ) ? $args['date_format'] : $args['date_format'] . ' ' . $args['time_format'];
	
	$date = ( true === $args['timestamp'] ) ? $args['date'] : strtotime( $args['date'] );
	$date = ( true === $args['localize']  ) ? date_i18n( $date_format, $date ) : date( $date_format, $date );
	
	return $date;
}
endif;

if ( ! function_exists( 'rktgk_date_format_map' ) ):
/**
 * Returns a conversion map array for various date formats.
 *
 * @since 1.0.0
 */
function rktgk_date_format_map() {
	return array(
		'YYYY-MM-DD' => 'Y-m-d',
		'EUROPEAN'   => 'j F Y',
		'AMERICAN'   => 'F j, Y',
		'MM/DD/YYYY' => 'm/d/Y',
		'DD-MM-YYYY' => 'd-m-Y',
		'MYSQL'      => 'Y-m-d H:i:s',		
	);
}
endif;

if ( ! function_exists( 'rktgk_get_php_date_format' ) ):
/**
 * Converts certain date formats to PHP.
 *
 * If no format is matched, it returns the original format.
 *
 * @see https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
 *
 * @since 1.0.0
 *
 * @param  string  $format
 * @return string  
 */
function rktgk_get_php_date_format( $format ) {
	$convert = rktgk_date_format_map();
	$format_upper = strtoupper( $format );
	return ( isset( $convert[ $format_upper ] ) ) ? $convert[ $format ] : $format;
}
endif;

if ( ! function_exists( 'rktgk_date_format' ) ):
/**
 * Replaced by rktgk_get_php_date_format()
 */
function rktgk_date_format( $format ) {
	return rktgk_get_php_date_format( $format );
}
endif;