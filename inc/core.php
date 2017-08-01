<?php
/**
 * WP-Members Core Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
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


if ( ! function_exists( 'wpmem_check_activated' ) ):
/**
 * Checks if a user is activated.
 *
 * @since 2.7.1
 *
 * @param  object $user     The WordPress User object.
 * @param  string $username The user's username (user_login).
 * @param  string $password The user's password.
 * @return object $user     The WordPress User object.
 */ 
function wpmem_check_activated( $user, $username, $password ) {

	// Password must be validated.
	$pass = ( ( ! is_wp_error( $user ) ) && $password ) ? wp_check_password( $password, $user->user_pass, $user->ID ) : false;

	if ( ! $pass ) { 
		return $user;
	}

	// Activation flag must be validated.
	if ( ! wpmem_is_user_activated( $user->ID ) ) {
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


if ( ! function_exists( 'widget_wpmemwidget_init' ) ):
/**
 * Initializes the WP-Members widget.
 *
 * @since 2.0.0
 * @since 3.1.6 Dependencies now loaded by object.
 */
function widget_wpmemwidget_init() {
	// Register the WP-Members widget.
	register_widget( 'widget_wpmemwidget' );
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
 *
 * @return bool Returns false if the user is not activated, otherwise true.
 */
function wpmem_no_reset() {

	global $wpmem;

	if ( strpos( $_POST['user_login'], '@' ) ) {
		$user = get_user_by( 'email', trim( $_POST['user_login'] ) );
	} else {
		$username = trim( $_POST['user_login'] );
		$user     = get_user_by( 'login', $username );
	}

	if ( $wpmem->mod_reg == 1 ) { 
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
 * @since 3.1.8 Added $process argument.
 */
function wpmem_wp_register_form( $process = 'wp' ) {
	/**
	 * Load native WP registration functions.
	 */
	require_once( WPMEM_PATH . 'inc/wp-registration.php' );
	wpmem_do_wp_register_form( $process );
}


/**
 * Validates registration fields in the native WP registration.
 *
 * @since 2.8.3
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @param  array  $errors               A WP_Error object containing any errors encountered during registration.
 * @param  string $sanitized_user_login User's username after it has been sanitized.
 * @param  string $user_email           User's email.
 * @return array  $errors               A WP_Error object containing any errors encountered during registration.
 */
function wpmem_wp_reg_validate( $errors, $sanitized_user_login, $user_email ) {

	global $wpmem;

	// Get any meta fields that should be excluded.
	$exclude = wpmem_get_excluded_meta( 'register' );

	foreach ( wpmem_fields( 'wp_validate' ) as $meta_key => $field ) {
		$is_error = false;
		if ( $field['required'] && $meta_key != 'user_email' && ! in_array( $meta_key, $exclude ) ) {
			if ( ( $field['type'] == 'checkbox' || $field['type'] == 'multicheckbox' || $field['type'] == 'multiselect' || $field['type'] == 'radio' ) && ( ! isset( $_POST[ $meta_key ] ) ) ) {
				$is_error = true;
			} 
			if ( ( $field['type'] != 'checkbox' && $field['type'] != 'multicheckbox' && $field['type'] != 'multiselect' && $field['type'] != 'radio' ) && ( ! $_POST[ $meta_key ] ) ) {
				$is_error = true;
			}
			if ( $is_error ) { $errors->add( 'wpmem_error', sprintf( $wpmem->get_text( 'reg_empty_field' ), __( $field['label'], 'wp-members' ) ) ); }
		}
	}

	return $errors;
}


/**
 * Inserts registration data from the native WP registration.
 *
 * @since 2.8.3
 * @since 3.1.1 Added new 3.1 field types and activate user support.
 *
 * @todo Compartmentalize file upload along with main register function.
 *
 * @global object $wpmem The WP-Members object class.
 * @param int $user_id The WP user ID.
 */
function wpmem_wp_reg_finalize( $user_id ) {

	global $wpmem;
	// Is this WP's native registration? Checks the native submit button.
	$is_native  = ( isset( $_POST['wp-submit'] ) && $_POST['wp-submit'] == esc_attr( __( 'Register' ) ) ) ? true : false;
	// Is this a Users > Add New process? Checks the post action.
	$is_add_new = ( isset( $_POST['action'] ) && $_POST['action'] == 'createuser' ) ? true : false;
	// Is this a WooCommerce checkout registration? Checks for WC fields.
	$is_woo     = ( isset( $_POST['woocommerce_checkout_place_order'] ) || isset( $_POST['woocommerce-register-nonce'] ) ) ? true : false;
	if ( $is_native || $is_add_new || $is_woo ) {
		// Get any excluded meta fields.
		$exclude = wpmem_get_excluded_meta( 'register' );
		foreach ( wpmem_fields( 'wp_finalize' ) as $meta_key => $field ) {
			if ( isset( $_POST[ $meta_key ] ) && ! in_array( $meta_key, $exclude ) && 'file' != $field['type'] && 'image' != $field['type'] ) {
				if ( 'multiselect' == $field['type'] || 'multicheckbox' == $field['type'] ) {
					$data = implode( $field['delimiter'], $_POST[ $meta_key ] );
				} else {
					$data = $_POST[ $meta_key ];
				}
				update_user_meta( $user_id, $meta_key, sanitize_text_field( $data ) );
			}
		}
	}
	
	// If this is Users > Add New.
	if ( is_admin() && $is_add_new ) {
		// If moderated registration and activate is checked, set active flags.
		if ( 1 == $wpmem->mod_reg && isset( $_POST['activate_user'] ) ) {
			update_user_meta( $user_id, 'active', 1 );
			wpmem_set_user_status( $user_id, 0 );
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
	// @todo Should this enqueue styles?
	echo '<link rel="stylesheet" id="custom_wp_admin_css"  href="' . WPMEM_DIR . 'css/wp-login.css" type="text/css" media="all" />';
}


/**
 * Securifies the comments.
 *
 * If the user is not logged in and the content is blocked
 * (i.e. wpmem->is_blocked() returns true), function loads a
 * dummy/empty comments template.
 *
 * @since 2.9.9
 *
 * @return bool $open true if current post is open for comments, otherwise false.
 */
function wpmem_securify_comments( $open ) {

	$open = ( ! is_user_logged_in() && wpmem_is_blocked() ) ? false : $open;

	/**
	 * Filters whether comments are open or not.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $open true if current post is open for comments, otherwise false.
	 */
	$open = apply_filters( 'wpmem_securify_comments', $open );

	if ( ! $open ) {
		/** This filter is documented in wp-includes/comment-template.php */
		add_filter( 'comments_array' , 'wpmem_securify_comments_array' , 10, 2 );
	}

	return $open;
}


/**
 * Empties the comments array if content is blocked.
 *
 * @since 3.0.1
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @return array $comments The comments array.
 */
function wpmem_securify_comments_array( $comments , $post_id ) {
	global $wpmem;
	$comments = ( ! is_user_logged_in() && $wpmem->is_blocked() ) ? array() : $comments;
	return $comments;
}


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


/**
 * Adds the successful registration message on the login page if reg_nonce validates.
 *
 * @since 3.1.7
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_reg_securify( $content ) {
	global $wpmem, $wpmem_themsg;
	$nonce = wpmem_get( 'reg_nonce', false, 'get' );
	if ( $nonce && wp_verify_nonce( $nonce, 'register_redirect' ) ) {
		$content = wpmem_inc_regmessage( 'success', $wpmem_themsg );
		$content = $content . wpmem_inc_login();
	}
	return $content;
}


/**
 * Enqueues the admin javascript and css files.
 *
 * Replaces wpmem_admin_enqueue_scripts().
 * Only loads the js and css on admin screens that use them.
 *
 * @since 3.1.7
 *
 * @param str $hook The admin screen hook being loaded.
 */
function wpmem_dashboard_enqueue_scripts( $hook ) {
	if ( $hook == 'edit.php' || $hook == 'settings_page_wpmem-settings' ) {
		wp_enqueue_style( 'wpmem-admin', WPMEM_DIR . 'admin/css/admin.css', '', WPMEM_VERSION );
	}
	if ( $hook == 'settings_page_wpmem-settings' ) {
		wp_enqueue_script( 'wpmem-admin', WPMEM_DIR . 'admin/js/admin.js', '', WPMEM_VERSION );
	}
}

// End of file.