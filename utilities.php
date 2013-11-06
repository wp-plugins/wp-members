<?php
/**
 * WP-Members Utility Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler 
 * @copyright 2006-2013
 */


if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8
 *
 * @param  string $name the name of the field
 * @param  string $type the field type
 * @param  string $value the default value for the field
 * @param  string $valtochk optional for comparing the default value of the field
 * @param  string $class optional for setting a specific CSS class for the field 
 * @return string $str the field returned as a string
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' )
{
	switch( $type ) {

	case "checkbox":
		if( $class = 'textbox' ) { $class = "checkbox"; }
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" " . wpmem_selected( $value, $valtochk, $type ) . " />\n";
		break;

	case "text":
		$value = stripslashes( $value );
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />\n";
		break;

	case "textarea":
		$value = stripslashes( $value );
		if( $class = 'textbox' ) { $class = "textarea"; }
		$str = "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\">$value</textarea>";
		break;

	case "password":
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\" />\n";
		break;

	case "hidden":
		$str = "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;

	case "option":
		$str = "<option value=\"$value\" " . wpmem_selected( $value, $valtochk, 'select' ) . " >$name</option>\n";
		break;

	case "select":
		if( $class == 'textbox' ) { $class = "dropdown"; }
		$str = "<select name=\"$name\" id=\"$name\" class=\"$class\">\n";
		foreach( $value as $option ) {
			$pieces = explode( '|', $option );
			$str = $str . "<option value=\"$pieces[1]\"" . wpmem_selected( $pieces[1], $valtochk, 'select' ) . ">$pieces[0]</option>\n";
		}
		$str = $str . "</select>\n";
		break;

	}
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes)
 *
 * @since 0.1
 *
 * @param  string $value
 * @param  string $valtochk
 * @param  string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type=null )
{
	$issame = ( $type == 'select' ) ? 'selected' : 'checked';
	if( $value == $valtochk ){ return $issame; }
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings
 *
 * @since 2.0
 *
 * @uses   get_permalink
 * @param  string $url
 * @return string $return_url
 */
function wpmem_chk_qstr( $url = null )
{
	$permalink = get_option( 'permalink_structure' );
	if( ! $permalink ) {
		if( ! $url ) { $url = get_option( 'home' ) . "/?" . $_SERVER['QUERY_STRING']; }
		$return_url = $url . "&amp;";
	} else {
		if( !$url ) { $url = get_permalink(); }
		$return_url = $url . "?";
	}
	return $return_url;
}
endif;


if ( ! function_exists( 'wpmem_generatePassword' ) ):
/**
 * Generates a random password 
 *
 * @since 2.0
 *
 * @return string the random password
 */
function wpmem_generatePassword()
{	
	return substr( md5( uniqid( microtime() ) ), 0, 7);
}
endif;


if ( ! function_exists( 'wpmem_texturize' ) ):
/**
 * Overrides the wptexturize filter
 *
 * Currently only used for the login form to remove the <br> tag that WP puts in after the "Remember Me"
 *
 * @since 2.6.4
 *
 * @param  string $content
 * @return string $new_content
 */
function wpmem_texturize( $content ) 
{
	$new_content = '';
	$pattern_full = '{(\[wpmem_txt\].*?\[/wpmem_txt\])}is';
	$pattern_contents = '{\[wpmem_txt\](.*?)\[/wpmem_txt\]}is';
	$pieces = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );

	foreach( $pieces as $piece ) {
		if( preg_match( $pattern_contents, $piece, $matches ) ) {
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
 * Loads the stylesheet for tableless forms
 *
 * @since 2.6
 *
 * @uses wp_register_style
 * @uses wp_enqueue_style
 */
function wpmem_enqueue_style()
{		
	$css_path = ( WPMEM_CSSURL != null ) ? WPMEM_CSSURL : WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) . "css/wp-members.css";
	wp_register_style('wp-members', $css_path);
	wp_enqueue_style( 'wp-members');
}
endif;


if ( ! function_exists( 'wpmem_do_excerpt' ) ):
/**
 * Creates an excerpt on the fly if there is no 'more' tag
 *
 * @since 2.6
 *
 * @uses apply_filters Calls 'wpmem_auto_excerpt'
 * @uses apply_filters Calls 'the_content_more_link'
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_do_excerpt( $content )
{	
	$arr = get_option( 'wpmembers_autoex' );
	if( $arr['auto_ex'] == true ) {
		
		if( ! stristr( $content, 'class="more-link"' ) ) {
		
			$words = explode(' ', $content, ( $arr['auto_ex_len'] + 1 ) );
			if( count( $words ) > $arr['auto_ex_len'] ) { array_pop( $words ); }
			$content = implode( ' ', $words );
			
			/** check for common html tags */
			$common_tags = array( 'i', 'b', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5' );
			foreach ( $common_tags as $tag ) {
				if( stristr( $content, '<' . $tag . '>' ) ) {
					$after = stristr( $content, '</' . $tag . '>' );
					$content = ( ! stristr( $after, '</' . $tag . '>' ) ) ? $content . '</' . $tag . '>' : $content;
				}
			}
		} 		
	}

	global $post, $more;
	if( ! $more && ( $arr['auto_ex'] == true ) ) {
		$more_link_text = '(more...)';
		$more_link = ' <a href="'. get_permalink( $post->ID ) . '" class="more-link">' . $more_link_text . '</a>';
		$more_link = apply_filters( 'the_content_more_link' , $more_link, $more_link_text );
		
		$content = $content . $more_link;
	}
	
	$content = apply_filters( 'wpmem_auto_excerpt', $content );
	
	return $content;
}
endif;

/** End of File **/