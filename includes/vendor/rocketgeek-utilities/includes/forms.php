<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Forms
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

if ( ! function_exists( 'rktgk_get' ) ):
/**
 * Utility function to validate $_POST, $_GET, and $_REQUEST.
 *
 * While this function retrieves data, remember that the data should generally be
 * sanitized or escaped depending on how it is used.
 *
 * @since 1.0.0
 *
 * @param  string $tag     The form field or query string.
 * @param  string $default The default value (optional).
 * @param  string $type    post|get|request (optional).
 * @return string 
 */
function rktgk_get( $tag, $default = '', $type = 'post' ) {
	switch ( $type ) {
		case 'get':
			return ( isset( $_GET[ $tag ] ) ) ? $_GET[ $tag ] : $default;
			break;
		case 'request':
			return ( isset( $_REQUEST[ $tag ] ) ) ? $_REQUEST[ $tag ] : $default;
			break;
		default: // case 'post':
			return ( isset( $_POST[ $tag ] ) ) ? $_POST[ $tag ] : $default;
			break;
	}
}
endif;

if ( ! function_exists( 'rktgk_sanitize_class' ) ):
/**
 * Sanitizes classes passed to the WP-Members form building functions.
 *
 * This generally uses just sanitize_html_class() but allows for 
 * whitespace so multiple classes can be passed (such as "regular-text code").
 *
 * @since 1.0.0
 *
 * @param	string $class
 * @return	string sanitized_class
 */
function rktgk_sanitize_class( $class ) {
	// If no whitespace, just return WP sanitized class.
	if ( ! strpos( $class, ' ' ) ) {
		return sanitize_html_class( $class );
	} else {
		// Break string by whitespace, sanitize individual class names.
		$class_array = explode( ' ', $class );
		$len = count( $class_array ); $i = 0;
		$sanitized_class = '';
		foreach ( $class_array as $single_class ) {
			$sanitized_class .= sanitize_html_class( $single_class );
			$sanitized_class .= ( $i == $len - 1 ) ? '' : ' ';
			$i++;
		}
		return $sanitized_class;
	}
}
endif;

if ( ! function_exists( 'rktgk_sanitize_array' ) ):
/**
 * Sanitizes the text in an array.
 *
 * @since 1.0.0
 *
 * @param  array  $data
 * @param  string $type The data type integer|int (default: false)
 * @return array  $data
 */
function rktgk_sanitize_array( $data, $type = false ) {
	if ( is_array( $data ) ) {
		foreach( $data as $key => $val ) {
			$data[ $key ] = ( 'integer' == $type || 'int' == $type ) ? intval( $val ) : sanitize_text_field( $val );
		}
	}
	return $data;
}
endif;

if ( ! function_exists( 'rktgk_sanitize_field' ) ):
/**
 * Sanitizes field based on field type.
 *
 * Obviously, this isn't an all inclusive function of every WordPress
 * sanitization function. It is intended to handle sanitization of 
 * WP-Members form input and therefore includes the necessary methods
 * that would relate to the WP-Members custom field types and can thus
 * be used by looping through form data when the WP-Members fields are
 * handled and validated.
 *
 * @since 1.0.0
 * @since 1.0.1 Added text, url, array, class as accepted $type
 *
 * @param  string $data
 * @param  string $type (text|array|multiselect|multicheckbox|textarea|email|file|image|int|integer|number|url|class) Default:text
 * @return string $sanitized_data
 */
function rktgk_sanitize_field( $data, $type = '' ) {

	switch ( $type ) {

		case 'array':
		case 'multiselect':
		case 'multicheckbox':
		case 'multipleselect':
		case 'multiplecheckbox':
			$sanitized_data = rktgk_sanitize_array( $data );
			break;

		case 'textarea':
			$sanitized_data = sanitize_textarea_field( $data );
			break;

		case 'email':
			$sanitized_data = sanitize_email( $data );
			break;

		case 'file':
		case 'image':
			$sanitized_data = sanitize_file_name( $data );
			break;

		case 'int':
		case 'integer':
		case 'number':
			$sanitized_data = intval( $data );
			break;
			
		case 'url':
			$sanitized_data = sanitize_url( $data );
			break;
		
		case 'class':
			$sanitized_data = rktgk_sanitize_class( $data );
			break;

		case 'text':
		default:
			$sanitized_data = sanitize_text_field( $data );
			break;	
	}

	return $sanitized_data;
}
endif;