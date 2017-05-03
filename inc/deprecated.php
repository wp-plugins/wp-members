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


if ( ! function_exists( 'wpmem_do_sc_pages' ) ):
/**
 * Builds the shortcode pages (login, register, user-profile, user-edit, password).
 *
 * Some of the logic here is similar to the wpmem_securify() function. 
 * But where that function handles general content, this function 
 * handles building specific pages generated by shortcodes.
 *
 * @since 2.6.0
 * @deprecated 3.1.8 Use wpmem_sc_user_profile() or wpmem_sc_forms() instead.
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_themsg The WP-Members message container.
 * @global object $post         The WordPress post object.
 *
 * @param  string $atts {
 *     The shortcode attributes.
 *
 *     @type string $page
 *     @type string $redirect_to
 *     @type string $register
 * }
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_do_sc_pages( $atts, $content, $tag ) {
	
	$page = ( isset( $atts['page'] ) ) ? $atts['page'] : $tag; 
	$redirect_to = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
	$hide_register = ( isset( $atts['register'] ) && 'hide' == $atts['register'] ) ? true : false;

	global $wpmem, $wpmem_themsg, $post;
	include_once( WPMEM_PATH . 'inc/dialogs.php' );

	$content = '';

	// Deprecating members-area parameter to be replaced by user-profile.
	$page = ( $page == 'user-profile' ) ? 'members-area' : $page;

	if ( $page == 'members-area' || $page == 'register' ) {

		if ( $wpmem->regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		if ( $wpmem->regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}

		if ( ! is_user_logged_in() ) {
			if ( $wpmem->action == 'register' && ! $hide_register ) {

				switch( $wpmem->regchk ) {

				case "success":
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;

				default:
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}

			} elseif ( $wpmem->action == 'pwdreset' ) {

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );

			} elseif( $wpmem->action == 'getusername' ) {
				
				$content = wpmem_page_forgot_username( $wpmem->regchk, $content );
				
			} else {

				$content = ( $page == 'members-area' ) ? $content . wpmem_inc_login( 'members' ) : $content;
				$content = ( ( $page == 'register' || $wpmem->show_reg[ $post->post_type ] != 0 ) && ! $hide_register ) ? $content . wpmem_inc_registration() : $content;
			}

		} elseif ( is_user_logged_in() && $page == 'members-area' ) {

			/**
			 * Filter the default heading in User Profile edit mode.
			 *
			 * @since 2.7.5
			 *
			 * @param string The default edit mode heading.
			 */
			$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );

			switch( $wpmem->action ) {

			case "edit":
				$content = $content . wpmem_inc_registration( 'edit', $heading );
				break;

			case "update":

				// Determine if there are any errors/empty fields.

				if ( $wpmem->regchk == "updaterr" || $wpmem->regchk == "email" ) {

					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_registration( 'edit', $heading );

				} else {

					//Case "editsuccess".
					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_memberlinks();

				}
				break;

			case "pwdchange":

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
				break;

			case "renew":
				$content = wpmem_renew();
				break;

			default:
				$content = wpmem_inc_memberlinks();
				break;
			}

		} elseif ( is_user_logged_in() && $page == 'register' ) {

			$content = $content . wpmem_inc_memberlinks( 'register' );

		}

	}

	if ( $page == 'login' ) {
		$content = ( $wpmem->regchk == "loginfailed" ) ? wpmem_inc_loginfailed() : $content;
		$content = ( ! is_user_logged_in() ) ? $content . wpmem_inc_login( 'login', $redirect_to ) : wpmem_inc_memberlinks( 'login' );
	}

	if ( $page == 'password' ) {
		$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
	}

	if ( $page == 'user-edit' ) {
		$content = wpmem_page_user_edit( $wpmem->regchk, $content );
	}

	return $content;
} // End wpmem_do_sc_pages.
endif;