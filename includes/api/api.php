<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2020  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2020
 *
 * Functions included:
 * - wpmem_redirect_to_login
 * - wpmem_is_blocked
 * - wpmem_login_url
 * - wpmem_register_url
 * - wpmem_profile_url
 * - wpmem_current_url
 * - wpmem_current_post_id
 * - wpmem_gettext
 * - wpmem_use_custom_dialog
 * - wpmem_login_status
 * - wpmem_get
 * - wpmem_is_reg_page
 * - wpmem_loginout
 * - wpmem_display_message
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Redirects a user to defined login page with return redirect.
 *
 * While a specific URL can be passed as an argument, the default will
 * redirect the user back to the original page using wpmem_current_url().
 *
 * @since 3.0.2
 * @since 3.1.1 Moved to API.
 * @since 3.1.3 Added $redirect_to argument.
 *
 * @param string $redirect_to URL to redirect to (default: false).
 */
function wpmem_redirect_to_login( $redirect_to = false ) {
	if ( ! is_user_logged_in() ) {
		$redirect_to = ( $redirect_to ) ? $redirect_to : wpmem_current_url();
		wp_safe_redirect( wpmem_login_url( $redirect_to ) );
		exit();
	}
	return;
}

/**
 * Checks if content is blocked (replaces wpmem_block()).
 *
 * @since 3.1.1
 * @since 3.3.0 Added $post_id
 *
 * @global object $wpmem   The WP-Members object class.
 * @param  int    $post_id 
 * @return bool   $block   True if content is blocked, otherwise false.
 */
function wpmem_is_blocked( $post_id = false ) {
	global $wpmem;
	return $wpmem->is_blocked( $post_id );
}

/**
 * Checks if specific post is marked as hidden.
 *
 * @since 3.3.2
 *
 * @param  int    $post_id 
 * @return bool   $block   True if content is hidden, otherwise false.
 */
function wpmem_is_hidden( $post_id = false ) {
	return ( 2 == get_post_meta( $post_id, '_wpmem_block', true ) ) ? true : false;
}

/** 
 * Returns the block setting for a post.
 *
 * @since 3.3.0
 *
 * @global object $wpmem
 *
 * @param  int    $post_id
 * @return int    $block_value
 */
function wpmem_get_block_setting( $post_id ) {
	return get_post_meta( $post_id, '_wpmem_block', true );
}

/**
 * Wrapper to get the login page location.
 *
 * @since 3.1.1
 * @since 3.1.2 Added redirect_to parameter.
 *
 * @global object $wpmem       The WP_Members object.
 * @param  string $redirect_to URL to return to (optional).
 * @return string $url         The login page url.
 */
function wpmem_login_url( $redirect_to = false ) {
	global $wpmem;
	if ( $redirect_to ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $wpmem->user_pages['login'] );
	} else {
		$url = $wpmem->user_pages['login'];
	}
	return $url;
}

/**
 * Wrapper to get the register page location.
 *
 * @since 3.1.1
 *
 * @global object $wpmem The WP_Members object.
 * @return string        The register page url.
 */
function wpmem_register_url() {
	global $wpmem;
	return $wpmem->user_pages['register'];
}

/**
 * Wrapper to get the profile page location.
 *
 * @since 3.1.1
 * @since 3.1.2 Added $a parameter.
 *
 * @global object $wpmem The WP_Members object.
 * @param  string $a     Action (optional).
 * @return string        The profile page url.
 */
function wpmem_profile_url( $a = false ) {
	global $wpmem;
	return ( $a ) ? add_query_arg( 'a', $a, $wpmem->user_pages['profile'] ) : $wpmem->user_pages['profile'];
}

/**
 * Returns an array of user pages.
 *
 * @since 3.1.2
 * @since 3.1.3 Added array keys.
 *
 * @return array $pages {
 *     The URLs of login, register, and user profile pages.
 *
 *     @type string $login
 *     @type string $register
 *     @type string $profile
 * }
 */
function wpmem_user_pages() {
	$pages = array( 
		'login'    => trailingslashit( wpmem_login_url() ), 
		'register' => trailingslashit( wpmem_register_url() ),
		'profile'  => trailingslashit( wpmem_profile_url() ),
	);
	return $pages;
}

/**
 * Returns the current full url.
 *
 * @since 3.1.1
 * @since 3.1.7 Added check for query string.
 * 
 * @global object  $wp
 * @param  boolean $slash Trailing slash the end of the url (default:true).
 * @param  boolean $getq  Toggles getting the query string (default:true).
 * @return string  $url   The current page full url path.
 */
function wpmem_current_url( $slash = true, $getq = true ) {
	global $wp;
	$url = home_url( add_query_arg( array(), $wp->request ) );
	$url = ( $slash ) ? trailingslashit( $url ) : $url;
	$url = ( $getq && count( $_GET ) > 0 ) ? $url . '?' . $_SERVER['QUERY_STRING'] : $url;
	return $url;
}

/**
 * Gets post ID of current URL.
 *
 * @since 3.1.7
 *
 * @return int Post ID.
 */
function wpmem_current_post_id() {
	return url_to_postid( wpmem_current_url() );
}

/**
 * Wrapper to return a string from the get_text function.
 *
 * @since 3.1.1
 * @since 3.1.2 Added $echo argument.
 *
 * @global object $wpmem The WP_Members object.
 * @param  string $str   The string to retrieve.
 * @param  bool   $echo  Print the string (default: false).
 * @return string $str   The localized string.
 */
function wpmem_gettext( $str, $echo = false ) {
	global $wpmem;
	if ( $echo ) {
		echo $wpmem->get_text( $str );
	} else {
		return $wpmem->get_text( $str );
	}
}

/**
 * Wrapper to use custom dialog.
 *
 * @since 3.1.1
 *
 * @param  array  $defaults Dialog message defaults from the wpmem_msg_dialog_arr filter.
 * @param  string $tag      The dialog tag/name.
 * @param  array  $dialogs  The dialog settings array (passed through filter).
 * @return array  $dialogs  The dialog settings array (filtered).
 */
function wpmem_use_custom_dialog( $defaults, $tag, $dialogs ) {
	$defaults['msg'] = __( $dialogs[ $tag ], 'wp-members' );
	return $defaults;
}

/**
 * Returns or displays the user's login status.
 *
 * @since 2.0.0
 * @since 3.1.2 Moved to api.php, no longer pluggable.
 * @since 3.1.6 Dependencies now loaded by object.
 *
 * @param  boolean $echo   Determines whether function should print result or not (default: true).
 * @return string  $status The user status string produced by wpmem_inc_memberlinks().
 */
function wpmem_login_status( $echo = true ) {

	if ( is_user_logged_in() ) { 
		$status = wpmem_inc_memberlinks( 'status' );
		if ( $echo ) {
			echo $status; 
		}
		return $status;
	}
}

/**
 * Utility function to validate $_POST, $_GET, and $_REQUEST.
 *
 * While this function retrieves data, remember that the data should generally be
 * sanitized or escaped depending on how it is used.
 *
 * @since 3.1.3
 *
 * @param  string $tag     The form field or query string.
 * @param  string $default The default value (optional).
 * @param  string $type    post|get|request (optional).
 * @return string 
 */
function wpmem_get( $tag, $default = '', $type = 'post' ) {
	switch ( $type ) {
		case 'get':
			return ( isset( $_GET[ $tag ] ) ) ? $_GET[ $tag ] : $default;
			break;
		case 'request':
			return ( isset( $_REQUEST[ $tag ] ) ) ? $_REQUEST[ $tag ] : $default;
			break;
		default: // case 'post':
			return ( isset( $_POST[ $tag ] ) ) ? $_POST[ $tag ] : $default;
			break;
	}
}

/**
 * Compares wpmem_reg_page value with the register page URL. 
 *
 * @since 3.1.4
 * @since 3.1.7 Added default of current page ID.
 *
 * @param  string|int $check_page
 * @return bool
 */
function wpmem_is_reg_page( $check = false ) {
	if ( ! $check ) {
		$check = get_the_ID();
	} else {
		if ( ! is_int( $check ) ) {
			global $wpdb;
			$sql   = "SELECT ID FROM $wpdb->posts WHERE post_name = '$check' AND post_status = 'publish' LIMIT 1";	
			$arr   = $wpdb->get_results( $sql, ARRAY_A  ); 
			$check = $arr[0]['ID'];
		}
	}
	$reg_page = wpmem_get( 'wpmem_reg_page' );
	$check_page = get_permalink( $check );
	return ( $check_page == $reg_page ) ? true : false;
}

/**
 * Creates a login/logout link.
 *
 * @since 3.1.6
 *
 * @param  array   $args {
 *     Array of arguments to customize output.
 *
 *     @type string  $login_redirect_to  The url to redirect to after login (optional).
 *     @type string  $logout_redirect_to The url to redirect to after logout (optional).
 *     @type string  $login_text         Text for the login link (optional).
 *     @type string  $logout_text        Text for the logout link (optional).
 * }
 * @param  boolean $echo (default: false)
 * @return string  $link
 */
function wpmem_loginout( $args = array(), $echo = false ) {
	global $wpmem;
	return $wpmem->loginout_args( $args );
}

/**
 * Dispalays requested dialog.
 *
 * @since 3.2.0
 *
 * @todo Needs testing and finalization before release.
 */
function wpmem_display_message( $tag, $echo = true ) {
	if ( $echo ) {
		echo wpmem_inc_regmessage( $tag );
	} else {
		return wpmem_inc_regmessage( $tag );
	}
}

/**
 * Wrapper function for adding custom dialogs.
 *
 * @since 3.1.1
 * @since 3.3.0 Moved to main API.
 *
 * @param  array  $dialogs Dialog settings array.
 * @param  string $tag     Slug for dialog to be added.
 * @param  string $msg     The dialog message.
 * @param  string $label   Label for admin panel.
 * @return array  $dialogs Dialog settings array with prepped custom dialog added.
 */
function wpmem_add_custom_dialog( $dialogs, $tag, $msg, $label ) {
	$msg = ( ! isset( $dialogs[ $tag ] ) ) ? $msg : $dialogs[ $tag ];
	$dialogs[ $tag ] = array(
		'name'  => $tag,
		'label' => $label,
		'value' => $msg,
	);
	return $dialogs;
}

/**
 * Gets an array of hidden post IDs.
 *
 * @since 3.3.1
 *
 * @global stdClass $wpmem
 * @return array
 */
function wpmem_get_hidden_posts() {
	global $wpmem;
	return $wpmem->get_hidden_posts();
}

/**
 * Updates the hiddent posts array.
 *
 * @since 3.3.5
 *
 * @global stdClass $wpmem
 */
function wpmem_update_hidden_posts() {
	global $wpmem;
	$wpmem->update_hidden_posts();
}

/**
 * Conditional if REST request.
 *
 * @since 3.3.2
 *
 * @global stdClass $wpmem
 * @return boolean
 */
function wpmem_is_rest() {
	global $wpmem;
	return $wpmem->is_rest;
}

/**
 * Gets registration type.
 *
 * @since 3.3.5
 *
 * @global  stdClass  $wpmem
 * @param   string    $type (wpmem|native|add_new|woo|woo_checkout)
 * @return  boolean
 */
function wpmem_is_reg_type( $type ) {
	global $wpmem;
	return $wpmem->reg_type[ 'is_' . $type ];
}

// End of file.