<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Utility Functions
 * @author Chad Butler 
 * @copyright 2006-2018
 *
 * Functions included:
 * - wpmem_create_formfield
 * - wpmem_texturize
 * - wpmem_enqueue_style
 * - wpmem_do_excerpt
 * - wpmem_wp_reserved_terms
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


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
	wp_enqueue_style ( 'wp-members', $wpmem->cssurl, '', WPMEM_VERSION );
}
endif;




// End of file.