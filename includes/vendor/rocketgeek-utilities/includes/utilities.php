<?php
/**
 * This file is part of the RocketGeek Utility Functions library.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @subpackage RocketGeek_Utilities_Utilities
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

if ( ! function_exists( 'rktgk_force_ssl' ) ):
/**
 * Forces a URL to be secure (ssl).
 *
 * @since 1.0.0
 *
 * @param  string $url URL to be make secure.
 * @return string      The secure URL.
 */
function rktgk_force_ssl( $url ) {
	return ( is_ssl() ) ? preg_replace( "/^http:/i", "https:", $url ) : $url;
}
endif;

if ( ! function_exists( 'rktgk_get_suffix' ) ):
/**
 * Determines whether to use a .min suffix for a script/style file.
 *
 * @since 1.0.0
 *
 * @param boolean $echo
 */
function rktgk_get_suffix( $echo = false ) {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
	if ( true === $echo ) {
		echo $suffix;
		return;
	} else {
		return $suffix;
	}
}
endif;

if ( ! function_exists( 'rktgk_maybe_unserialize' ) ):
/**
 * Better unserialization than WP's maybe_unserialize().
 *
 * Sanitizes array output before returning. If the unserialized result is an
 * array, then it runs the result through wpmem_sanitize_array(), which 
 * sanitizes each individual array element.
 *
 * @since 1.0.0
 *
 * @param  mixed  $original
 * @return mixed  $original
 */
function rktgk_maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
		$original = unserialize( $original );
	}
	return ( is_array( $original ) ) ? wpmem_sanitize_array( $original ) : $original;
}
endif;

if ( ! function_exists( 'rktgk_maybe_wpautop' ) ):
/**
 * Run wpautop on content. Defaults to true.
 *
 * Useful for shortcodes that don't autop on their own.
 * Toggle boolean can be passed as a string without pre-filtering
 * since it runs rktgk_str_to_bool().
 * 
 * @since 1.0.0
 * 
 * @param  string  $content
 * @param  mixed   $do_autop
 * @return string  $content either autop'ed or not.
 */
function rktgk_maybe_wpautop( $content, $do_autop = true ) {
	return ( true === rktgk_str_to_bool( $do_autop ) ) ? wpautop( $content ) : $content;
}
endif;

if ( ! function_exists( 'rktgk_do_shortcode' ) ):
/**
 * Call a shortcode function by tag name.
 *
 * Use this function for directly calling a shortcode without using do_shortcode.
 * do_shortcode() runs an extensive regex that goes through every shortcode in
 * the WP global $shortcode_tags. That's a lot of processing wasted if all you
 * want to do is run a specific shortcode/function. Yes, you could run the callback
 * directly, but what if that callback is in a class instance method? This utlitiy
 * allows you to run a shortcode function directly, regardless of whether it is
 * a direct function or in a class. It comes from an article by J.D. Grimes on this
 * subject and I've provided a link to that article.
 *
 * @author J.D. Grimes
 * @link https://codesymphony.co/dont-do_shortcode/
 *
 * @since 1.0.0
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 */
function rktgk_do_shortcode( $tag, array $atts = array(), $content = null ) {
 
	global $shortcode_tags;

	if ( ! isset( $shortcode_tags[ $tag ] ) ) {
		return false;
	}

	return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
}
endif;

if ( ! function_exists( 'rktgk_is_woo_active' ) ):
/**
 * Checks if WooCommerce is active.
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function rktgk_is_woo_active() {
	return ( class_exists( 'woocommerce' ) ) ? true : false;
}
endif;

if ( ! function_exists( 'rktgk_get_user_ip' ) ):
/**
 * Get user IP address.
 *
 * From Pippin.
 * @link https://gist.github.com/pippinsplugins/9641841
 *
 * @since 1.0.0
 *
 * @return string $ip.
 */
function rktgk_get_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	/**
	 * Filter the IP result.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip
	 */
	return apply_filters( 'rktgk_get_user_ip', $ip );
}
endif;