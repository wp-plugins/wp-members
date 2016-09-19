<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2016
 *
 * Functions included:
 * - wpmem_redirect_to_login
 * - wpmem_is_blocked
 * - wpmem_login_url
 * - wpmem_register_url
 * - wpmem_profile_url
 * - wpmem_current_url
 * - wpmem_fields
 * - wpmem_gettext
 * - wpmem_use_custom_dialog
 * - wpmem_user_has_role
 * - wpmem_create_membership_number
 * - wpmem_login_status
 * - wpmem_is_reg_page
 * - wpmem_load_dropins
 */


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
		wp_redirect( wpmem_login_url( $redirect_to ) );
		exit();
	}
	return;
}


/**
 * Checks if content is blocked (replaces wpmem_block()).
 *
 * @since 3.1.1
 *
 * @global object $wpmem The WP-Members object class.
 * @return bool   $block True if content is blocked, false otherwise.
 */
function wpmem_is_blocked() {
	global $wpmem;
	return $wpmem->is_blocked();
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
 * @global object $wpmem
 * @return string The register page url.
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
 * @global object $wpmem
 * @param  string $a      Action (optional).
 * @return string         The profile page url.
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
 * @return array $pages
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
 * 
 * @global object  $wp
 * @param  boolean $slash Trailing slash the end of the url (default:true).
 * @return string  $url   The current page full url path.
 */
function wpmem_current_url( $slash = true ) {
	global $wp;
	$url = home_url( add_query_arg( array(), $wp->request ) );
	return ( $slash ) ? trailingslashit( $url ) : $url;
}


/**
 * Wrapper for $wpmem->create_form_field().
 *
 * @since 3.1.2
 *
 * @param  array  $args
 * @return string 
 */
function wpmem_form_field( $args ) {
	global $wpmem;
	return $wpmem->forms->create_form_field( $args );
}


/**
 * Wrapper to get form fields.
 *
 * @since 3.1.1
 * @since 3.1.5 Checks if fields array is set or empty before returning.
 *
 * @global object $wpmem
 * @param  string $form The form being generated.
 * @return array  $fields The form fields.
 */
function wpmem_fields( $form = 'default' ) {
	global $wpmem;
	if ( ! isset( $wpmem->fields ) || empty( $wpmem->fields ) ) {
		$wpmem->load_fields( $form );
	}
	return $wpmem->fields;
}


/**
 * Wrapper to return a string from the get_text function.
 *
 * @since 3.1.1
 * @since 3.1.2 Added $echo argument.
 *
 * @global object $wpmem The WP_Members object class.
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
 * Checks if user has a particular role.
 *
 * Utility function to check if a given user has a specific role. Users can
 * have multiple roles assigned, so it checks the role array rather than using
 * the incorrect method of current_user_can( 'role_name' ). The function can
 * check the role of the current user (default) or a specific user (if $user_id
 * is passed).
 *
 * @since 3.1.1
 *
 * @global object  $current_user Current user object.
 * @param  string  $role         Slug of the role being checked.
 * @param  int     $user_id      ID of the user being checked (optional).
 * @return boolean $has_role     True if user has the role, otherwise false.
 */
function wpmem_user_has_role( $role, $user_id = false ) {
	global $current_user, $wpmem;
	$has_role = false;
	if ( $user_id ) {
		$user = get_userdata( $user_id );
	}
	if ( is_user_logged_in() && ! $user_id ) {
		$user = ( isset( $current_user ) ) ? $current_user : wp_get_current_user();
	}
	return ( in_array( $role, $user->roles ) ) ? true : $has_role;
}


/**
 * Creates a membership number.
 *
 * @since 3.1.1
 *
 * @param  array  $args
 * @return string $membersip_number
 */
function wpmem_create_membership_number( $args ) {
	global $wpmem;
	return $wpmem->api->generate_membership_number( $args );
}


/**
 * Returns or displays the user's login status.
 *
 * @since 2.0.0
 * @since 3.1.2 Moved to api.php, no longer pluggable.
 *
 * @param  boolean $echo   Determines whether function should print result or not (default: true).
 * @return string  $status The user status string produced by wpmem_inc_memberlinks().
 */
function wpmem_login_status( $echo = true ) {

	/**
	 * Load the dialogs functions.
	 */
	require_once( WPMEM_PATH . 'inc/dialogs.php' );

	if ( is_user_logged_in() ) { 
		$status = wpmem_inc_memberlinks( 'status' );
		if ( $echo ) {
			echo $status; 
		}
		return $status;
	}
}


/**
 * Compares wpmem_reg_page value with the register page URL. 
 *
 * @since 3.1.4
 *
 * @param  string|int $check_page
 * @return bool
 */
function wpmem_is_reg_page( $check ) {
	if ( ! is_int( $check ) ) {
		global $wpdb;
		$sql   = "SELECT ID FROM $wpdb->posts WHERE post_name = '$check' AND post_status = 'publish' LIMIT 1";	
		$arr   = $wpdb->get_results( $sql, ARRAY_A  ); 
		$check = $arr[0]['ID'];
	}
	$reg_page = wpmem_get( 'wpmem_reg_page' );
	$check_page = get_permalink( $check );
	return ( $check_page == $reg_page ) ? true : false;
}


/**
 * Wrapper for load_dropins()
 *
 * @since 3.1.4
 */
function wpmem_load_dropins() {
	global $wpmem;
	$wpmem->load_dropins();
}

// End of file.