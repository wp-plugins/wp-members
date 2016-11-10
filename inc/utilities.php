<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2016
 *
 * Functions included:
 * - wpmem_create_formfield
 * - wpmem_selected @deprecated 3.1.0 Use selected() or checked() instead.
 * - wpmem_chk_qstr @deprecated 3.1.0 Use add_query_arg() instead.
 * - wpmem_generatePassword @deprecated Unknown Use wp_generate_password() instead.
 * - wpmem_texturize
 * - wpmem_enqueue_style
 * - wpmem_do_excerpt
 * - wpmem_test_shortcode @deprecated 3.1.2 Use has_shortcode() instead.
 * - wpmem_get_excluded_meta
 * - wpmem_use_ssl
 * - wpmem_wp_reserved_terms
 * - wpmem_write_log
 */


if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8.0
 * @since 3.1.0 Converted to wrapper for create_form_field() in utlities object.
 *
 * @global object $wpmem    The WP_Members object class.
 * @param  string $name     The name of the field.
 * @param  string $type     The field type.
 * @param  string $value    The default value for the field.
 * @param  string $valtochk Optional for comparing the default value of the field.
 * @param  string $class    Optional for setting a specific CSS class for the field.
 * @return string $str      The field returned as a string.
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' ) {
	global $wpmem;
	$args = array(
		'name'     => $name,
		'type'     => $type,
		'value'    => $value,
		'compare'  => $valtochk,
		'class'    => $class,
	);
	return $wpmem->forms->create_form_field( $args );
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes).
 *
 * @since 0.1.0
 * @deprecated 3.1.0 Use selected() or checked() instead.
 *
 * @param  string $value
 * @param  string $valtochk
 * @param  string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type = null ) {
	wpmem_write_log( "wpmem_selected() is deprecated as of WP-Members 3.1.0. Use selected() or checked() instead" );
	$issame = ( $type == 'select' ) ? ' selected' : ' checked';
	return ( $value == $valtochk ) ? $issame : '';
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings.
 *
 * @since 2.0.0
 * @deprecated 3.1.0 Use add_query_arg() instead.
 *
 * @param  string $url
 * @return string $return_url
 */
function wpmem_chk_qstr( $url = null ) {
	wpmem_write_log( "wpmem_chk_qstr() is deprecated as of WP-Members 3.1.0. Use add_query_arg() instead" );
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
 * @deprecated Unknown Use wp_generate_password() instead.
 *
 * @return string The random password.
 */
function wpmem_generatePassword() {
	wpmem_write_log( "WP-Members function wpmem_generatePassword() is deprecated. Use wp_generate_password() instead" );
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
 * @global object $post  The post object.
 * @global object $wpmem The WP_Members object.
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_do_excerpt( $content ) {

	global $post, $more, $wpmem;

	$autoex = ( isset( $wpmem->autoex[ $post->post_type ] ) && 1 == $wpmem->autoex[ $post->post_type ]['enabled'] ) ? $wpmem->autoex[ $post->post_type ] : false;

	// Is there already a 'more' link in the content?
	$has_more_link = ( stristr( $content, 'class="more-link"' ) ) ? true : false;

	// If auto_ex is on.
	if ( $autoex ) {

		// Build an excerpt if one does not exist.
		if ( ! $has_more_link ) {
			
			$is_singular = ( is_singular( $post->post_type ) ) ? true : false;
			
			if ( $is_singular ) {
				// If it's a single post, we don't need the 'more' link.
				$more_link_text = '';
				$more_link      = '';
			} else {
				// The default $more_link_text.
				if ( isset( $wpmem->autoex[ $post->post_type ]['text'] ) && '' != $wpmem->autoex[ $post->post_type ]['text'] ) {
					$more_link_text = __( $wpmem->autoex[ $post->post_type ]['text'], 'wp-members' );
				} else {
					$more_link_text = __( '(more&hellip;)' );
				}
				// The default $more_link.
				$more_link = ' <a href="'. get_permalink( $post->ID ) . '" class="more-link">' . $more_link_text . '</a>';
			}
			
			// Apply the_content_more_link filter if one exists (will match up all 'more' link text).
			/** This filter is documented in /wp-includes/post-template.php */
			$more_link = apply_filters( 'the_content_more_link', $more_link, $more_link_text );
			
			$defaults = array(
				'length'           => $autoex['length'],
				'more_link'        => $more_link,
				'blocked_only'     => false,
			);
			/**
			 * Filter auto excerpt defaults.
			 *
			 * @since 3.0.9
			 * @since 3.1.5 Deprecated add_ellipsis, strip_tags, close_tags, parse_shortcodes, strip_shortcodes.
			 *
			 * @param array {
			 *     An array of settings to override the function defaults.
			 *
			 *     @type int         $length           The default length of the excerpt.
			 *     @type string      $more_link        The more link HTML.
			 *     @type boolean     $blocked_only     Run autoexcerpt only on blocked content. default: false.
			 * }
			 * @param string $post->ID        The post ID.
			 * @param string $post->post_type The content's post type.					 
			 */
			$args = apply_filters( 'wpmem_auto_excerpt_args', '', $post->ID, $post->post_type );
			
			// Merge settings.
			$args = wp_parse_args( $args, $defaults );
			
			// Are we only excerpting blocked content?
			if ( $args['blocked_only'] ) {
				$post_meta = get_post_meta( $post->ID, '_wpmem_block', true );
				if ( 1 == $wpmem->block[ $post->post_type ] ) {
					// Post type is blocked, if post meta unblocks it, don't do excerpt.
					$do_excerpt = ( "0" == $post_meta ) ? false : true;
				} else {
					// Post type is unblocked, if post meta blocks it, do excerpt.
					$do_excerpt = ( "1" == $post_meta ) ? true : false;
				} 
			} else {
				$do_excerpt = true;
			}
		
			if ( $do_excerpt ) {
				$content = wp_trim_words( $content, $args['length'], $args['more_link'] );
				// Check if the more link was added (note: singular has no more_link):
				if ( ! $is_singular && ! strpos( $content, $args['more_link'] ) ) {
					$content = $content . $args['more_link'];
				}
			}

		}
	}

	/**
	 * Filter the auto excerpt.
	 *
	 * @since 2.8.1
	 * @since 3.0.9 Added post ID and post type parameters.
	 * 
	 * @param string $content         The content excerpt.
	 * @param string $post->ID        The post ID.
	 * @param string $post->post_type The content's post type.
	 */
	$content = apply_filters( 'wpmem_auto_excerpt', $content, $post->ID, $post->post_type );

	// Return the excerpt.
	return $content;
}
endif;


if ( ! function_exists( 'wpmem_test_shortcode' ) ):
/**
 * Tests $content for the presence of the [wp-members] shortcode.
 *
 * @since 2.6.0
 * @deprecated 3.1.2 Use has_shortcode() instead.
 *
 * @global string $shortcode_tags
 * @return bool
 *
 * @example http://codex.wordpress.org/Function_Reference/get_shortcode_regex
 */
function wpmem_test_shortcode( $content, $tag ) {

	wpmem_write_log( "wpmem_test_shortcode() is deprecated as of WP-Members 3.1.2. Use has_shortcode() instead." );
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
	$reserved_terms = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'fields', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 'role', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments', 'year' );
	
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

// End of file.