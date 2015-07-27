<?php
/**
 * WP-Members Core Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2015  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler 
 * @copyright 2006-2015
 */


// Include utility functions.
require_once( WPMEM_PATH . 'inc/utilities.php' );


/**
 * The Main Action Function.
 *
 * Does actions required at initialization prior to headers being sent.
 * Since 3.0, this function is a wrapper for get_action().
 *
 * @since 0.1 
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
 * Since 3.0, this function is a wrapper for do_securify().
 *
 * @since 2.0
 *
 * @global object $wpmem
 * @param  string $content
 * @return string $content
 */
function wpmem_securify( $content = null ) {
	global $wpmem;
	return $wpmem->do_securify( $content );
}
endif;


if ( ! function_exists( 'wpmem_block' ) ):
/**
 * Determines if content should be blocked.
 *
 * Since 3.0, this function is a wrapper for is_blocked().
 *
 * @since 2.6
 *
 * @return bool $block true|false
 */
function wpmem_block() {
	global $wpmem;
	return $wpmem->is_blocked();
}
endif;


if ( ! function_exists( 'wpmem_check_activated' ) ):
/**
 * Checks if a user is activated.
 *
 * @since 2.7.1
 *
 * @uses   wp_check_password
 * @param  int    $user
 * @param  string $username
 * @param  string $password
 * @return int    $user
 */ 
function wpmem_check_activated( $user, $username, $password ) {

	// Password must be validated.
	$pass = ( ( ! is_wp_error( $user ) ) && $password ) ? wp_check_password( $password, $user->user_pass, $user->ID ) : false;

	if ( ! $pass ) { 
		return $user;
	}

	// Activation flag must be validated.
	$active = get_user_meta( $user->ID, 'active', true );
	if ( $active != 1 ) {
		return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: User has not been activated.', 'wp-members' ) );
	}

	// If the user is validated, return the $user object.
	return $user;
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
 * @since 0.1
 *
 * @uses   wp_signon
 * @uses   wp_set_auth_cookie
 * @uses   wp_redirect        Redirects to $redirect_to if login is successful.
 * @return string             Returns "loginfailed" if the login fails.
 */
function wpmem_login() {

	if ( $_POST['log'] && $_POST['pwd'] ) {

		// Get username and sanitize.
		$user_login = sanitize_user( $_POST['log'] );

		// Are we setting a forever cookie?
		$rememberme = ( isset( $_POST['rememberme'] ) == 'forever' ) ? true : false;

		// Assemble login credentials.
		$creds = array();
		$creds['user_login']    = $user_login;
		$creds['user_password'] = $_POST['pwd'];
		$creds['remember']      = $rememberme;

		// Log in the user and get the user object.
		$user = wp_signon( $creds, is_ssl() );

		// If no error, user is a valid signon. continue.
		if ( ! is_wp_error( $user ) ) {

			// Determine where to put the user after login.
			if ( isset( $_POST['redirect_to'] ) )  {
				$redirect_to = trim( $_POST['redirect_to'] );
			} else {
				$redirect_to = $_SERVER['REQUEST_URI'] . ( ( isset( $_SERVER['QUERY_STRING'] ) ) ? $_SERVER['QUERY_STRING'] : '' );
			}

			/**
			 * Filter the redirect url.
			 *
			 * @since 2.7.7
			 *
			 * @param string $redirect_to The url to direct to.
			 * @param int    $user->ID    The user's primary key ID.
			 */
			$redirect_to = apply_filters( 'wpmem_login_redirect', $redirect_to, $user->ID );

			// And do the redirect.
			wp_redirect( $redirect_to );

			// wp_redirect requires us to exit()
			exit();
	
		} else {

			return "loginfailed";
		}

	} else {
		// Login failed.
		return "loginfailed";
	}
} // End of login function.
endif;


if ( ! function_exists( 'wpmem_logout' ) ):
/**
 * Logs the user out then redirects.
 *
 * @since 2.0
 *
 * @uses  wp_clearcookie
 * @uses  wp_logout
 * @uses  nocache_headers
 * @uses  wp_redirect
 * @param string $redirect_to The URL to redirect to at logout.
 */
function wpmem_logout( $redirect_to = null ) {

	// Default redirect URL.
	$redirect_to = ( $redirect_to ) ? $redirect_to : get_bloginfo( 'url' );

	/**
	 * Filter where the user goes when logged out.
	 *
	 * @since 2.7.1
	 *
	 * @param string The blog home page.
	 */
	$redirect_to = apply_filters( 'wpmem_logout_redirect', $redirect_to );

	wp_clear_auth_cookie();

	// This action is defined in /wp-includes/pluggable.php.
	do_action( 'wp_logout' );

	nocache_headers();

	wp_redirect( $redirect_to );
	exit();
}
endif;


if ( ! function_exists( 'wpmem_login_status' ) ):
/**
 * Displays the user's login status.
 *
 * @since 2.0
 *
 * @uses   wpmem_inc_memberlinks()
 * @param  boolean $echo           Determines whether function should print result or not (default: true).
 * @return string  $status         The user status string produced by wpmem_inc_memberlinks().
 */
function wpmem_login_status( $echo = true ) {

	include_once( WPMEM_PATH . 'inc/dialogs.php' );
	if ( is_user_logged_in() ) { 
		$status = wpmem_inc_memberlinks( 'status' );
		if ( $echo ) {
			echo $status; 
		}
		return $status;
	}
}
endif;


if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
/**
 * Displays the sidebar.
 *
 * This function is a wrapper for wpmem_do_sidebar().
 *
 * @since 2.0
 *
 * @uses wpmem_do_sidebar()
 */
function wpmem_inc_sidebar() {
	include_once( WPMEM_PATH . 'inc/sidebar.php' );
	wpmem_do_sidebar();
}
endif;


if ( ! function_exists( 'widget_wpmemwidget_init' ) ):
/**
 * Initializes the widget.
 *
 * @since 2.0
 *
 * @uses register_widget
 */
function widget_wpmemwidget_init() {
	include_once( WPMEM_PATH . 'inc/class-wp-members-widget.php' );
	include_once( WPMEM_PATH . 'inc/sidebar.php' );
	register_widget( 'widget_wpmemwidget' );
}
endif;


if ( ! function_exists( 'wpmem_change_password' ) ):
/**
 * Handles user password change (not reset).
 *
 * @since 2.1
 *
 * @global $user_ID
 * @return string the value for $wpmem->regchk
 */
function wpmem_change_password() {

	global $user_ID;
	if ( isset( $_POST['formsubmit'] ) ) {

		$pass1 = trim( $_POST['pass1'] );
		$pass2 = trim( $_POST['pass2'] );

		if ( ! $pass1 && ! $pass2 ) { // Check for both fields being empty.

			return "pwdchangempty";

		} elseif ( $pass1 != $pass2 ) { // Make sure the fields match.

			return "pwdchangerr";

		} else { // Update password in db (wp_update_user hashes the password).

			wp_update_user( array ( 'ID' => $user_ID, 'user_pass' => $pass1 ) );

			/**
			 * Fires after password change.
			 *
			 * @since 2.9.0
			 *
			 * @param int $user_ID The user's numeric ID.
			 */
			do_action( 'wpmem_pwd_change', $user_ID );

			return "pwdchangesuccess";

		}
	}
	return;
}
endif;


if ( ! function_exists( 'wpmem_reset_password' ) ):
/**
 * Resets a forgotten password.
 *
 * @since 2.1
 *
 * @uses   wp_generate_password
 * @uses   wp_update_user
 * @return string value for $wpmem->regchk
 */
function wpmem_reset_password() {

	global $wpmem;

	if ( isset( $_POST['formsubmit'] ) ) {

		/**
		 * Filter the password reset arguments.
		 *
		 * @since 2.7.1
		 *
		 * @param array The username and email.
		 */
		$arr = apply_filters( 'wpmem_pwdreset_args', array( 
			'user'  => ( isset( $_POST['user']  ) ) ? trim( $_POST['user'] )  : '', 
			'email' => ( isset( $_POST['email'] ) ) ? trim( $_POST['email'] ) : '',
		) );

		if ( ! $arr['user'] || ! $arr['email'] ) { 

			// There was an empty field.
			return "pwdreseterr";

		} else {

			if ( username_exists( $arr['user'] ) ) {

				$user = get_user_by( 'login', $arr['user'] );

				if ( strtolower( $user->user_email ) !== strtolower( $arr['email'] ) || ( ( $wpmem->mod_reg == 1 ) && ( get_user_meta( $user->ID,'active', true ) != 1 ) ) ) {
					// The username was there, but the email did not match OR the user hasn't been activated.
					return "pwdreseterr";

				} else {

					// Generate a new password.
					$new_pass = wp_generate_password();

					// Update the users password.
					wp_update_user( array ( 'ID' => $user->ID, 'user_pass' => $new_pass ) );

					// Send it in an email.
					require_once( WPMEM_PATH . 'inc/email.php' );
					wpmem_inc_regemail( $user->ID, $new_pass, 3 );

					/**
					 * Fires after password reset.
					 *
					 * @since 2.9.0
					 *
					 * @param int $user_ID The user's numeric ID.
					 */
					do_action( 'wpmem_pwd_reset', $user->ID );

					return "pwdresetsuccess";
				}
			} else {

				// Username did not exist.
				return "pwdreseterr";
			}
		}
	}
	return;
}
endif;


if ( ! function_exists( 'wpmem_no_reset' ) ):
/**
 * Keeps users not activated from resetting their password 
 * via wp-login when using registration moderation.
 *
 * @since 2.5.1
 *
 * @return bool
 */
function wpmem_no_reset() {

	global $wpmem;

	if ( strpos( $_POST['user_login'], '@' ) ) {
		$user = get_user_by( 'email', trim( $_POST['user_login'] ) );
	} else {
		$username = trim( $_POST['user_login'] );
		$user     = get_user_by( 'login', $username );
	}

	if ( $wmem->mod_reg == 1 ) { 
		if ( get_user_meta( $user->ID, 'active', true ) != 1 ) {
			return false;
		}
	}

	return true;
}
endif;


/**
 * Add registration fields to the native WP registration.
 *
 * @since 2.8.3
 */
function wpmem_wp_register_form() {
	include_once( WPMEM_PATH . 'inc/wp-registration.php' );
	wpmem_do_wp_register_form();
}


/**
 * Validates registration fields in the native WP registration.
 *
 * @since 2.8.3
 *
 * @param $errors
 * @param $sanatized_user_login
 * @param $user_email
 * @return $errors
 */
function wpmem_wp_reg_validate( $errors, $sanitized_user_login, $user_email ) {

	global $wpmem;
	$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
	$exclude = wpmem_get_excluded_meta( 'register' );

	foreach ( $wpmem_fields as $field ) {
		$is_error = false;
		if ( $field[5] == 'y' && $field[2] != 'user_email' && ! in_array( $field[2], $exclude ) ) {
			if ( ( $field[3] == 'checkbox' ) && ( ! isset( $_POST[$field[2]] ) ) ) {
				$is_error = true;
			} 
			if ( ( $field[3] != 'checkbox' ) && ( ! $_POST[$field[2]] ) ) {
				$is_error = true;
			}
			if ( $is_error ) { $errors->add( 'wpmem_error', sprintf( __('Sorry, %s is a required field.', 'wp-members'), $field[1] ) ); }
		}
	}

	return $errors;
}


/**
 * Inserts registration data from the native WP registration.
 *
 * @since 2.8.3
 *
 * @param $user_id
 */
function wpmem_wp_reg_finalize( $user_id ) {

	global $wpmem;
	$native_reg = ( isset( $_POST['wp-submit'] ) && $_POST['wp-submit'] == esc_attr( __( 'Register' ) ) ) ? true : false;
	$add_new  = ( isset( $_POST['action'] ) && $_POST['action'] == 'createuser' ) ? true : false;
	if ( $native_reg || $add_new ) {
		// Get the fields.
		$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
		// Get any excluded meta fields.
		$exclude = wpmem_get_excluded_meta( 'register' );
		foreach ( $wpmem_fields as $meta ) {
			if ( isset( $_POST[$meta[2]] ) && ! in_array( $meta[2], $exclude ) ) {
				update_user_meta( $user_id, $meta[2], sanitize_text_field( $_POST[$meta[2]] ) );
			}
		}
	}
	return;
}


/**
 * Loads the stylesheet for backend registration.
 *
 * @since 2.8.7
 */
function wpmem_wplogin_stylesheet() {
	echo '<link rel="stylesheet" id="custom_wp_admin_css"  href="' . WPMEM_DIR . 'css/wp-login.css" type="text/css" media="all" />';
}


/**
 * Securifies the comments.
 *
 * If the user is not logged in and the content is blocked
 * (i.e. wpmem_block() returns true), function loads a
 * dummy/empty comments template.
 *
 * @since 2.9.9
 *
 * @return bool $open Whether the current post is open for comments.
 */
function wpmem_securify_comments( $open ) {

	$open = ( ! is_user_logged_in() && wpmem_block() ) ? false : $open;
	
	/**
	 * Filters whether comments are open or not.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $open Whether the current post is open for comments.
	 */
	$open = apply_filters( 'wpmem_securify_comments', $open );
	
	if ( ! $open ) {
		add_filter( 'comments_array' , 'wpmem_securify_comments_array' , 10, 2 );
	}

	return $open;
}


/**
 * Empties the comments array if content is blocked.
 *
 * @since 3.0.1
 *
 * @return array $comments The comments array.
 */
function wpmem_securify_comments_array( $comments , $post_id ) {
	global $wpmem;
	$comments = ( ! is_user_logged_in() && $wpmem->is_blocked() ) ? array() : $comments;
	return $comments;
}


/**
 * Redirects a user to defined login page with return redirect.
 *
 * @since 3.0.2
 */
function wpmem_redirect_to_login() {
	
	global $wpmem;

	if( ! is_user_logged_in() && $wpmem->is_blocked() ) {
		global $post;
		// Get the page location.
		$page = urlencode( "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		
		$url  = wpmem_chk_qstr( $wpmem->user_pages['login'] ) . 'redirect_to=' . $page;
		
		wp_redirect( $url );
		exit();
	}
	return;
}


// End of File.