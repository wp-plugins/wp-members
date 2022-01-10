<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Debug
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

/**

 Changelog
 
 1.0.0 Initial version.
 1.0.1 Added test for what_is() if $var is empty.
 
 **/

if ( ! function_exists('rktgk_break_point')):
function rktgk_break_point( $print = 'you are here' ) {
	if ( false == $print ) {
		exit();
	}
	echo $print;
	exit();
}
endif;

if ( ! function_exists('rktgk_write_log')):
function rktgk_write_log( $log )  {
    if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
    } else {
        error_log( $log );
    }
}
endif;

if ( ! function_exists('rktgk_what_is')):
function rktgk_what_is( $var, $exit = false, $output = true, $title = true ) {
	
	$is_obj = false;
	
	switch( $var ) {
			
		case empty( $var ) :
			echo '$var is empty';
			break;
		
		case is_string( $var ) :
			echo ( true === $title )  ? '$var is a string' : '';
			echo ( true === $title && true === $output ) ? ": " : "";
			echo ( true === $output ) ? $var : '';
			break;
			
		case is_object( $var ) :
			echo ( true === $title )  ? '$var is an object' : '';
			$is_obj = true;
			
		case is_array( $var ) :
			echo ( true === $title && false === $is_obj )  ? '$var is an array' : '';
			
			echo ( true === $title && true === $output ) ? ": " : "";
			
			if ( true === $output ) {
				echo '<pre>';
				print_r( $var );
				echo '</pre>'; 
			}
			break;
	}
	if ( 'exit' == $exit || true == $exit ) {
		exit();
	}
}
endif;

if ( ! function_exists( 'rktgk_write_line' ) ):
function rktgk_write_line( $line ) {
	echo $line . "<br />";
}
endif;