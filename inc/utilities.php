<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2015  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2015
 *
 * Functions included:
 * - wpmem_create_formfield
 * - wpmem_selected
 * - wpmem_chk_qstr
 * - wpmem_generatePassword (deprecated)
 * - wpmem_texturize
 * - wpmem_enqueue_style
 * - wpmem_do_excerpt
 * - wpmem_test_shortcode
 * - wpmem_get_excluded_meta
 * - wpmem_use_ssl
 */


if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8.0
 *
 * @param  string $name     The name of the field.
 * @param  string $type     The field type.
 * @param  string $value    The default value for the field.
 * @param  string $valtochk Optional for comparing the default value of the field.
 * @param  string $class    Optional for setting a specific CSS class for the field.
 * @return string $str      The field returned as a string.
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' ) {

	switch ( $type ) {

	case "file":
		$class = ( $class == 'textbox' ) ? "file" : $class;
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />";
		break;

	case "checkbox":
		$class = ( $class == 'textbox' ) ? "checkbox" : $class;
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\"" . wpmem_selected( $value, $valtochk, $type ) . " />";
		break;

	case "text":
		$value = stripslashes( esc_attr( $value ) );
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />";
		break;

	case "textarea":
		$value = stripslashes( esc_textarea( $value ) );
		$class = ( $class == 'textbox' ) ? "textarea" : $class;
		$str = "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\">$value</textarea>";
		break;

	case "password":
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\" />";
		break;

	case "hidden":
		$str = "<input name=\"$name\" type=\"$type\" value=\"$value\" />";
		break;

	case "option":
		$str = "<option value=\"$value\" " . wpmem_selected( $value, $valtochk, 'select' ) . " >$name</option>";
		break;

	case "select":
		$class = ( $class == 'textbox' ) ? "dropdown" : $class;
		$str = "<select name=\"$name\" id=\"$name\" class=\"$class\">\n";
		foreach ( $value as $option ) {
			$pieces = explode( '|', $option );
			$str = $str . "<option value=\"$pieces[1]\"" . wpmem_selected( $pieces[1], $valtochk, 'select' ) . ">" . __( $pieces[0], 'wp-members' ) . "</option>\n";
		}
		$str = $str . "</select>";
		break;

	}

	return $str;
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes).
 *
 * @since 0.1.0
 *
 * @param  string $value
 * @param  string $valtochk
 * @param  string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type=null ) {
	$issame = ( $type == 'select' ) ? ' selected' : ' checked';
	return ( $value == $valtochk ) ? $issame : '';
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings.
 *
 * @since 2.0.0
 *
 * @param  string $url
 * @return string $return_url
 */
function wpmem_chk_qstr( $url = null ) {

	$permalink = get_option( 'permalink_structure' );
	if ( ! $permalink ) {
		$url = ( ! $url ) ? get_option( 'home' ) . "/?" . $_SERVER['QUERY_STRING'] : $url;
		$return_url = $url . "&";
	} else {
		$url = ( ! $url ) ? get_permalink() : $url;
		$return_url = $url . "?";
	}
	return $return_url;
}
endif;


if ( ! function_exists( 'wpmem_generatePassword' ) ):
/**
 * Generates a random password.
 *
 * @since 2.0.0
 * @deprecated Unknown
 *
 * @return string The random password.
 */
function wpmem_generatePassword() {
	return substr( md5( uniqid( microtime() ) ), 0, 7 );
}
endif;


if ( ! function_exists( 'wpmem_texturize' ) ):
/**
 * Overrides the wptexturize filter.
 *
 * Currently only used for the login form to remove the <br> tag that WP puts in after the "Remember Me".
 *
 * @since 2.6.4
 *
 * @param  string $content
 * @return string $new_content
 */
function wpmem_texturize( $content ) {

	$new_content = '';
	$pattern_full = '{(\[wpmem_txt\].*?\[/wpmem_txt\])}is';
	$pattern_contents = '{\[wpmem_txt\](.*?)\[/wpmem_txt\]}is';
	$pieces = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );

	foreach ( $pieces as $piece ) {
		if ( preg_match( $pattern_contents, $piece, $matches ) ) {
			$new_content .= $matches[1];
		} else {
			$new_content .= wptexturize( wpautop( $piece ) );
		}
	}

	return $new_content;
}
endif;


if ( ! function_exists( 'wpmem_enqueue_style' ) ):
/**
 * Loads the stylesheet for tableless forms.
 *
 * @since 2.6
 *
 * @global object $wpmem The WP_Members object. 
 */
function wpmem_enqueue_style() {
	global $wpmem;
	wp_register_style( 'wp-members', $wpmem->cssurl, '', WPMEM_VERSION );
	wp_enqueue_style ( 'wp-members' );
}
endif;


if ( ! function_exists( 'wpmem_do_excerpt' ) ):
/**
 * Creates an excerpt on the fly if there is no 'more' tag.
 *
 * @since 2.6
 *
 * @global object $wpmem The WP_Members object. 
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_do_excerpt( $content ) {

	global $wpmem;

	$arr = $wpmem->autoex; // get_option( 'wpmembers_autoex' );

	// Is there already a 'more' link in the content?
	$has_more_link = ( stristr( $content, 'class="more-link"' ) ) ? true : false;

	// If auto_ex is on.
	if ( $arr['auto_ex'] == true ) {

		// Build an excerpt if one does not exist.
		if ( ! $has_more_link ) {

			$words = explode( ' ', $content, ( $arr['auto_ex_len'] + 1 ) );
			if ( count( $words ) > $arr['auto_ex_len'] ) {
				array_pop( $words );
			}
			$content = implode( ' ', $words );

			// Check for common html tags.
			$common_tags = array( 'i', 'b', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5' );
			foreach ( $common_tags as $tag ) {
				if ( stristr( $content, '<' . $tag . '>' ) ) {
					$after = stristr( $content, '</' . $tag . '>' );
					$content = ( ! stristr( $after, '</' . $tag . '>' ) ) ? $content . '</' . $tag . '>' : $content;
				}
			}
		}
	}

	global $post, $more;
	// If there is no 'more' link and auto_ex is on.
	if ( ! $has_more_link && ( $arr['auto_ex'] == true ) ) {
		// The default $more_link_text.
		$more_link_text = __( '(more&hellip;)' );
		// The default $more_link.
		$more_link = ' <a href="'. get_permalink( $post->ID ) . '" class="more-link">' . $more_link_text . '</a>';
		// Apply the_content_more_link filter if one exists (will match up all 'more' link text).
		$more_link = apply_filters( 'the_content_more_link' , $more_link, $more_link_text );
		// Add the more link to the excerpt.
		$content = $content . $more_link;
	}

	/**
	 * Filter the auto excerpt.
	 *
	 * @since 2.8.1
	 * 
	 * @param string $content The excerpt.
	 */
	$content = apply_filters( 'wpmem_auto_excerpt', $content );

	// Return the excerpt.
	return $content;
}
endif;


if ( ! function_exists( 'wpmem_test_shortcode' ) ):
/**
 * Tests $content for the presence of the [wp-members] shortcode.
 *
 * @since 2.6.0
 *
 * @global $shortcode_tags
 *
 * @return bool
 *
 * @example http://codex.wordpress.org/Function_Reference/get_shortcode_regex
 */
function wpmem_test_shortcode( $content, $tag ) {

	global $shortcode_tags;
	if ( array_key_exists( $tag, $shortcode_tags ) ) {
		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return false;
		}

		foreach ( $matches as $shortcode ) {
			if ( $tag === $shortcode[2] ) {
				return true;
			}
		}
	}
	return false;
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
 *
 * @return string https://|http:// depending on whether ssl is being used.
 */
function wpmem_use_ssl() {
	return ( is_ssl() ) ? 'https://' : 'http://';
}


/**
 * Returns an array of WordPress reserved terms.
 *
 * @since 3.0.2
 *
 * @return array An array of WordPress reserved terms.
 */
function wpmem_wp_reserved_terms() {
	$reserved_terms = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'fields', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments', 'year' );
	
	/**
	 * Filter the array of reserved terms.
	 *
	 * @since 3.0.2
	 *
	 * @param array $reserved_terms
	 */
	$reserved_terms = apply_filters( 'wpmem_wp_reserved_terms', $reserved_terms );
	
	return $reserved_terms;
}

/** End of File **/