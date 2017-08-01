<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2017
 *
 * Functions included:
 * - wpmem_create_formfield
 * - wpmem_texturize
 * - wpmem_enqueue_style
 * - wpmem_do_excerpt
 * - wpmem_get_excluded_meta
 * - wpmem_use_ssl
 * - wpmem_wp_reserved_terms
 * - wpmem_write_log
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

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


if ( ! function_exists( 'wpmem_texturize' ) ):
/**
 * Overrides the wptexturize filter.
 *
 * Currently only used for the login form to remove the <br> tag that WP puts in after the "Remember Me".
 *
 * @since 2.6.4
 *
 * @todo Possibly deprecate or severely alter this process as its need may be obsolete.
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

/**
 * Convert form tag.
 *
 * @todo This is temporary to handle form tag conversion.
 *
 * @since 3.1.7
 *
 * @param  string $tag
 * @return string $tag
 */
function wpmem_convert_tag( $tag ) {
	switch ( $tag ) {
		case 'new':
			return 'register';
			break;
		case 'edit':
		case 'update':
			return 'profile';
			break;
		case 'wp':
		case 'wp_validate':
		case 'wp_finalize':
			return 'register_wp';
			break;
		case 'dashboard_profile':
		case 'dashboard_profile_update':
			return 'profile_dashboard';
			break;
		case 'admin_profile':
		case 'admin_profile_update':
			return 'profile_admin';
			break;
		default:
			return $tag;
			break;
	}
	return $tag;
}

// End of file.