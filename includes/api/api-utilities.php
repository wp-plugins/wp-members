<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2022  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2022
 */

if ( ! function_exists( 'wpmem_securify' ) ):
/**
 * The Securify Content Filter.
 *
 * This is the primary function that picks up where wpmem() leaves off.
 * Determines whether content is shown or hidden for both post and pages.
 * Since 3.0, this function is an alias for $wpmem->do_securify().
 *
 * @since 2.0.0
 * @since 3.0.0 Now an alias for $wpmem->do_securify().
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
 * @since Unknown Now an alias for get_excluded_fields().
 * @since 3.3.9 excluded_fields() moved to forms object class.
 *
 * @param  string $tag A tag so we know where the function is being used.
 * @return array       Array of fields to be excluded from the registration form.
 */
function wpmem_get_excluded_meta( $tag ) {
	global $wpmem;
	return $wpmem->forms->excluded_fields( $tag );
}

/**
 * Forces a URL to be secure (ssl).
 *
 * @since 3.2.3
 * @since 3.4.0 Now an alias for rktgk_force_ssl()
 *
 * @param  string $url URL to be make secure.
 * @return string      The secure URL.
 */
function wpmem_force_ssl( $url ) {
	return rktgk_force_ssl( $url );
}

/**
 * Log debugging errors.
 *
 * @since 3.1.2
 * @since 3.4.0 Now an alias for rktgk_write_log().
 * 
 * @param mixed (string|array|object) $log Information to write in the WP debug file.
 */
function wpmem_write_log( $log ) {
	rktgk_write_log( $log );
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
 * @since 3.4.0 Now an alias for rktgk_get_sub_str().
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
	return rktgk_get_sub_str( $needle, $haystack, $position, $keep_needle );
}

if ( ! function_exists( 'wpmem_do_excerpt' ) ):
/**
 * Creates an excerpt on the fly if there is no 'more' tag.
 *
 * @since 2.6
 * @since 3.2.3 Now an alias for WP_Members::do_excerpt().
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
 * @since 3.2.3 Now an alias for WP_Members::texturize().
 * @deprecated 3.4.0. No replacement available.
 *
 * @todo Possibly deprecate or severely alter this process as its need may be obsolete.
 *
 * @global object $wpmem
 * @param  string $content
 * @return string $new_content
 */
function wpmem_texturize( $content ) {
	global $wpmem;
	//return $wpmem->texturize( $content );
	return $content;
}
endif;

/**
 * Inserts array items at a specific point in an array.
 *
 * @since 3.1.6
 * @since 3.2.3 Moved to utilities api.
 * @since 3.4.0 Now an alias for rktgk_array_insert()
 *
 * @param  array  $array Original array.
 * @param  array  $new   Array of new items to insert into $array.
 * @param  string $key   Array key to insert new items before or after.
 * @param  string $loc   Location to insert relative to $key (before|after) default:after.
 * @return array         Original array with new items inserted.
 */
function wpmem_array_insert( array $array, array $new, $key, $loc = 'after' ) {
	return rktgk_array_insert( $array, $new, $key, $loc );
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
 * @since 3.4.0 Now an alias for rktgk_format_date().
 *
 * @param mixed $args
 * @return date $date
 */
function wpmem_format_date( $args ) {
	/**
	 * Filter the date display and format settings.
	 *
	 * @since 3.2.4
	 * @deprecated 3.4.0 Use rktgk_format_date instead.
	 *
	 * @param arrag $args
	 */
	$args = apply_filters( 'wpmem_format_date_args', $args );
	return rktgk_format_date( $args );
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
 * @since 3.4.0 Now an alias for rktgk_do_shortcode().
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 */
function wpmem_do_shortcode( $tag, array $atts = array(), $content = null ) {
	return rktgk_do_shortcode( $tag, $atts, $content );
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
 * @since 3.4.0 Now an alias for rktgk_maybe_unserialize().
 *
 * @param  mixed  $original
 * @return mixed  $original
 */
function wpmem_maybe_unserialize( $original ) {
	return rktgk_maybe_unserialize( $original );
}

/**
 * Determines whether to use a .min suffix for a script/style file.
 *
 * @since 3.3.0
 * @since 3.4.0 Now an alias for rktgk_get_suffix().
 *
 * @param boolean $echo
 */
function wpmem_get_suffix( $echo = false ) {
	return rktgk_get_suffix( $echo );
}

/**
 * A utility to determine a redirect_to param.
 *
 * @since 3.4.0
 *
 * @param  array  $args
 * @return string $redirect_to
 */
function wpmem_get_redirect_to( $args = array() ) {
	// redirect_to in the form or URL will override a redirect set in the form args.
	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = $_REQUEST['redirect_to'];
	} else {
		if ( isset( $args['redirect_to'] ) ) {
			$raw_redirect_to = $args['redirect_to'];
			// Is it a URL?
			$redirect_to = ( false == filter_var( $raw_redirect_to, FILTER_VALIDATE_URL ) ) ? home_url( $raw_redirect_to ) : $raw_redirect_to;
		} else {
			$redirect_to = ( isset( $_SERVER['REQUEST_URI'] ) ) ? $_SERVER['REQUEST_URI'] : get_permalink();
		}
	}
	return $redirect_to;
}