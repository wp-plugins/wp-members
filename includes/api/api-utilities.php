<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2020  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2020
 */

if ( ! function_exists( 'wpmem_securify' ) ):
/**
 * The Securify Content Filter.
 *
 * This is the primary function that picks up where wpmem() leaves off.
 * Determines whether content is shown or hidden for both post and pages.
 * Since 3.0, this function is a wrapper for $wpmem->do_securify().
 *
 * @since 2.0.0
 * @since 3.0.0 Now a wrapper for $wpmem->do_securify().
 * @since 3.2.4 Moved to utility API (could be deprecated).
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @param  string $content Content of the current post.
 * @return string $content Content of the current post or replaced content if post is blocked and user is not logged in.
 */
function wpmem_securify( $content = null ) {
	global $wpmem;
	return $wpmem->do_securify( $content );
}
endif;

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
 * @deprecated 3.2.3 Use wpmem_force_ssl() instead.
 *
 * @return string https://|http:// depending on whether ssl is being used.
 */
function wpmem_use_ssl() {
	return ( is_ssl() ) ? 'https://' : 'http://';
}

/**
 * Forces a URL to be secure (ssl).
 *
 * @since 3.2.3
 *
 * @param  string $url URL to be make secure.
 * @return string      The secure URL.
 */
function wpmem_force_ssl( $url ) {
	return ( is_ssl() ) ? preg_replace( "/^http:/i", "https:", $url ) : $url;
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

if ( ! function_exists( 'wpmem_do_excerpt' ) ):
/**
 * Creates an excerpt on the fly if there is no 'more' tag.
 *
 * @since 2.6
 * @since 3.2.3 Now a wrapper for WP_Members::do_excerpt().
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_do_excerpt( $content ) {
	global $wpmem;
	$content = $wpmem->do_excerpt( $content );
	return $content;
}
endif;

if ( ! function_exists( 'wpmem_texturize' ) ):
/**
 * Overrides the wptexturize filter.
 *
 * Currently only used for the login form to remove the <br> tag that WP puts in after the "Remember Me".
 *
 * @since 2.6.4
 * @since 3.2.3 Now a wrapper for WP_Members::texturize().
 *
 * @todo Possibly deprecate or severely alter this process as its need may be obsolete.
 *
 * @global object $wpmem
 * @param  string $content
 * @return string $new_content
 */
function wpmem_texturize( $content ) {
	global $wpmem;
	return $wpmem->texturize( $content );
}
endif;

/**
 * Inserts array items at a specific point in an array.
 *
 * @since 3.1.6
 * @since 3.2.3 Moved to utilities api.
 *
 * @param  array  $array Original array.
 * @param  array  $new   Array of new items to insert into $array.
 * @param  string $key   Array key to insert new items before or after.
 * @param  string $loc   Location to insert relative to $key (before|after) default:after.
 * @return array         Original array with new items inserted.
 */
function wpmem_array_insert( array $array, array $new, $key, $loc = 'after' ) {
	$keys = array_keys( $array );
	if ( 'before' == $loc ) {
		$pos = (int) array_search( $key, $keys );
	} else {
		$index = array_search( $key, $keys );
		$pos = ( false === $index ) ? count( $array ) : $index + 1;
	}
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}

/**
 * Wrapper for load_dropins()
 *
 * @since 3.1.4
 * @since 3.2.3 Moved to utilities api.
 *
 * @global object $wpmem The WP_Members object.
 */
function wpmem_load_dropins() {
	global $wpmem;
	$wpmem->load_dropins();
}

/**
 * Display a localized date based on the WP date format setting.
 *
 * @since 3.2.4
 *
 * @param mixed $args
 * @return date $date
 */
function wpmem_format_date( $args ) {
	if ( ! is_array( $args ) ) {
		$args = array( 'date' => $args );
	}
	
	$defaults = array( 
		'date_format' => get_option( 'date_format' ),
		'localize'    => true,
		'timestamp'   => false,
	);
	
	$args = wp_parse_args( $args, $deafults );
	
	/**
	 * Filter the date display and format settings.
	 *
	 * @since 3.2.4
	 *
	 * @param arrag $args
	 */
	$args = apply_filters( 'wpmem_format_date_args', $args );
	$date = ( true === $args['timestamp'] ) ? $args['date'] : strtotime( $args['date'] );
	$date = ( true === $args['localize']  ) ? date_i18n( $args['date_format'], $date ) : date( $args['date_format'], $date );
	return $date;
}

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
 * @since 3.2.5
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 */
function wpmem_do_shortcode( $tag, array $atts = array(), $content = null ) {
 
	global $shortcode_tags;

	if ( ! isset( $shortcode_tags[ $tag ] ) ) {
		return false;
	}

	return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
}

/**
 * Checks if a password is part of registration.
 *
 * Used for moderated registration to determine if a user sets their
 * own password at registration. If so, password is not set during
 * user activation.
 *
 * @since 3.3.0
 */
function wpmem_user_sets_password() {
	$chk_pass = false;
	$wpmem_fields = wpmem_fields();
	foreach ( $wpmem_fields as $field ) {
		if ( $field['type'] == 'password' && $field['register'] ) {
			$chk_pass = true;
			break;
		}
	}
	return $chk_pass;
}

/**
 * Better unserialization than WP's maybe_unserialize().
 *
 * Sanitizes array output before returning. If the unserialized result is an
 * array, then it runs the result through wpmem_sanitize_array(), which 
 * sanitizes each individual array element.
 *
 * @since 3.3.0
 *
 * @param  mixed  $original
 * @return mixed  $original
 */
function wpmem_maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
		$original = unserialize( $original );
	}
	return ( is_array( $original ) ) ? wpmem_sanitize_array( $original ) : $original;
}

/**
 * Determines whether to use a .min suffix for a script/style file.
 *
 * @since 3.3.0
 *
 * @param boolean $echo
 */
function wpmem_get_suffix( $echo = false ) {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
	if ( true === $echo ) {
		echo $suffix;
		return;
	} else {
		return $suffix;
	}
}