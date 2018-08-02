<?php
/**
 * WP-Members Core Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package   WP-Members
 * @author    Chad Butler 
 * @copyright 2006-2018
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * The Main Action Function.
 *
 * Does actions required at initialization prior to headers being sent.
 * Since 3.0, this function is a wrapper for $wpmem->get_action().
 *
 * @since 0.1.0
 * @since 3.0.0 Now a wrapper for $wpmem->get_action().
 *
 * @global object $wpmem The WP-Members object class.
 */
function wpmem() {
	global $wpmem;
	$wpmem->get_action();
}


if ( ! function_exists( 'wpmem_securify' ) ):
/**
 * The Securify Content Filter.
 *
 * This is the primary function that picks up where wpmem() leaves off.
 * Determines whether content is shown or hidden for both post and pages.
 * Since 3.0, this function is a wrapper for $wpmem->do_securify().
 *
 * @since 2.0.0
 * @since 3.0.0 Now a wrapper for $wpmem->do_securify().
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


if ( ! function_exists( 'wpmem_login' ) ):
/**
 * Logs in the user.
 *
 * Logs in the the user using wp_signon (since 2.5.2). If login is
 * successful, it will set a cookie using wp_set_auth_cookie (since 2.7.7),
 * then it redirects and exits; otherwise "loginfailed" is returned.
 *
 * @since 0.1.0
 * @since 2.5.2 Now uses wp_signon().
 * @since 2.7.7 Sets cookie using wp_set_auth_cookie().
 * @since 3.0.0 Removed wp_set_auth_cookie(), this already happens in wp_signon().
 * @since 3.1.7 Now a wrapper for login() in WP_Members_Users Class.
 *
 * @global object $wpmem
 * @return string Returns "loginfailed" if the login fails.
 */
function wpmem_login() {
	global $wpmem;
	return $wpmem->user->login();
} // End of login function.
endif;


if ( ! function_exists( 'wpmem_logout' ) ):
/**
 * Logs the user out then redirects.
 *
 * @since 2.0.0
 * @since 3.1.6 Added wp_destroy_current_session(), removed nocache_headers().
 * @since 3.1.7 Now a wrapper for logout() in WP_Members_Users Class.
 *
 * @global object $wpmem
 * @param  string $redirect_to The URL to redirect to at logout.
 */
function wpmem_logout( $redirect_to = false ) {
	global $wpmem;
	$wpmem->user->logout( $redirect_to );
}
endif;


if ( ! function_exists( 'wpmem_change_password' ) ):
/**
 * Handles user password change (not reset).
 *
 * @since 2.1.0
 * @since 3.1.7 Now a wrapper for password_update() in WP_Members_Users Class.
 *
 * @global int $user_ID The WordPress user ID.
 *
 * @return string The value for $wpmem->regchk
 */
function wpmem_change_password() {
	global $wpmem;
	return $wpmem->user->password_update( 'change' );
}
endif;


if ( ! function_exists( 'wpmem_reset_password' ) ):
/**
 * Resets a forgotten password.
 *
 * @since 2.1.0
 * @since 3.1.7 Now a wrapper for password_update() in WP_Members_Users Class.
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @return string The value for $wpmem->regchk
 */
function wpmem_reset_password() {
	global $wpmem;
	return $wpmem->user->password_update( 'reset' );
}
endif;


if ( ! function_exists( 'wpmem_no_reset' ) ):
/**
 * Prevents users not activated from resetting their password.
 *
 * @since 2.5.1
 * @since 3.2.0 Now a wrapper for $wpmem->user->no_reset().
 *
 * @return bool Returns false if the user is not activated, otherwise true.
 */
function wpmem_no_reset() {
	global $wpmem;
	return $wpmem->user->no_reset();
}
endif;

/**
 * Handles retrieving a forgotten username.
 *
 * @since 3.0.8
 * @since 3.1.6 Dependencies now loaded by object.
 * @since 3.1.8 Now a wrapper for $wpmem->retrieve_username() in WP_Members_Users Class.
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @return string $regchk The regchk value.
 */
function wpmem_retrieve_username() {
	global $wpmem;
	return $wpmem->user->retrieve_username();
}

// End of file.