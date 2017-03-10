<?php
/**
 * WP-Members Deprecated Functions
 *
 * These functions have been deprecated and are now obsolete.
 * Use alternative functions as these will be removed in a 
 * future release.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package   WP-Members
 * @author    Chad Butler 
 * @copyright 2006-2017
 */


if ( ! function_exists( 'wpmem_block' ) ):
/**
 * Determines if content is blocked.
 *
 * @since 2.6.0
 * @since 3.0.0 Now a wrapper for $wpmem->is_blocked().
 * @deprecated 3.1.1 Use wpmem_is_blocked() instead.
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @return bool $block true if content is blocked, false otherwise.
 */
function wpmem_block() {
	wpmem_write_log( "wpmem_block() is deprecated as of WP-Members 3.1.1, use wpmem_is_blocked() instead" );
	global $wpmem;
	return $wpmem->is_blocked();
}
endif;

if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
/**
 * Displays the sidebar.
 *
 * This function is a wrapper for wpmem_do_sidebar().
 *
 * @since 2.0.0
 * @deprecated Unknown
 */
function wpmem_inc_sidebar() {
	wpmem_write_log( "WP-Members function wpmem_inc_sidebar() is deprecated. No alternative function exists" );
	/**
	 * Load the sidebar functions.
	 */
	include_once( WPMEM_PATH . 'inc/sidebar.php' );
	// Render the sidebar.
	wpmem_do_sidebar();
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

if ( ! function_exists( 'wpmem_shortcode' ) ):
/**
 * Executes various shortcodes.
 *
 * This function executes shortcodes for pages (settings, register, login, user-list,
 * and tos pages), as well as login status and field attributes when the wp-members tag
 * is used.  Also executes shortcodes for login status with the wpmem_logged_in tags
 * and fields when the wpmem_field tags are used.
 *
 * @since 2.4.0
 * @deprecated 3.1.2 
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param  array  $attr {
 *     The shortcode attributes.
 *
 *     @type string $page
 *     @type string $url
 *     @type string $status
 *     @type string $msg
 *     @type string $field
 *     @type int    $id
 * }
 * @param  string $content
 * @param  string $tag
 * @return string Returns the result of wpmem_do_sc_pages|wpmem_list_users|wpmem_sc_expmessage|$content.
 */
function wpmem_shortcode( $attr, $content = null, $tag = 'wp-members' ) {
	
	$error = "wpmem_shortcode() is deprecated as of WP-Members 3.1.2. The [wp-members] shortcode tag should be replaced. ";
	$error.= 'See replacement shortcodes: http://rkt.bz/logsc ';
	$error.= "post ID: " . get_the_ID() . " ";
	$error.= "page url: " . wpmem_current_url();
	wpmem_write_log( $error );

	global $wpmem;

	// Set all default attributes to false.
	$defaults = array(
		'page'        => false,
		'redirect_to' => null,
		'url'         => false,
		'status'      => false,
		'msg'         => false,
		'field'       => false,
		'id'          => false,
		'underscores' => 'off',
	);

	// Merge defaults with $attr.
	$atts = shortcode_atts( $defaults, $attr, $tag );

	// Handles the 'page' attribute.
	if ( $atts['page'] ) {
		if ( $atts['page'] == 'user-list' ) {
			if ( function_exists( 'wpmem_list_users' ) ) {
				$content = do_shortcode( wpmem_list_users( $attr, $content ) );
			}
		} elseif ( $atts['page'] == 'tos' ) {
			return $atts['url'];
		} else {
			$content = do_shortcode( wpmem_do_sc_pages( $atts, $content, $tag ) );
		}

		// Resolve any texturize issues.
		if ( strstr( $content, '[wpmem_txt]' ) ) {
			// Fixes the wptexturize.
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			add_filter( 'the_content', 'wpmem_texturize', 999 );
		}
		return $content;
	}

	// Handles the 'status' attribute.
	if ( ( $atts['status'] ) || $tag == 'wpmem_logged_in' ) {
		return wpmem_sc_logged_in( $atts, $content, $tag );
	}

	// Handles the 'field' attribute.
	if ( $atts['field'] || $tag == 'wpmem_field' ) {
		return wpmem_sc_fields( $atts, $content, $tag );
	}

}
endif;