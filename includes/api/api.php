<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2019
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
 * @return bool   $block   True if content is blocked, false otherwise.
 */
function wpmem_is_blocked( $post_id = false ) {
	global $wpmem;
	return $wpmem->is_blocked( $post_id );
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
	$defaults = array(
		'login_redirect_to'  => ( isset( $args['login_redirect_to']  ) ) ? $args['login_redirect_to']  : wpmem_current_url(),
		'logout_redirect_to' => ( isset( $args['logout_redirect_to'] ) ) ? $args['logout_redirect_to'] : wpmem_current_url(), // @todo - This is not currently active.
		'login_text'         => ( isset( $args['login_text']         ) ) ? $args['login_text']         : __( 'log in',  'wp-members' ),
		'logout_text'        => ( isset( $args['logout_text']        ) ) ? $args['logout_text']        : __( 'log out', 'wp-members' ),
	);
	$args     = wp_parse_args( $args, $defaults );
	$redirect = ( is_user_logged_in() ) ? $args['logout_redirect_to'] : $args['login_redirect_to'];
	$text     = ( is_user_logged_in() ) ? $args['logout_text']        : $args['login_text'];
	if ( is_user_logged_in() ) {
		/** This filter is defined in /inc/dialogs.php */
		$link = apply_filters( 'wpmem_logout_link', add_query_arg( 'a', 'logout' ) );
	} else {
		$link = wpmem_login_url( $redirect );
	}
	$link = sprintf( '<a href="%s">%s</a>', $link, $text );
	return $link;
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
 * Register function.
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 * @since 2.7.2 Added pre/post process actions.
 * @since 2.8.2 Added validation and data filters.
 * @since 2.9.3 Added validation for multisite.
 * @since 3.0.0 Moved from wp-members-register.php to /inc/register.php.
 * @since 3.3.0 Ported from wpmem_registration in /inc/register.php (now deprecated).
 *
 * @todo Review what should be in the API function and what should be moved to object classes.
 *
 * @global int    $user_ID
 * @global object $wpmem
 * @global string $wpmem_themsg
 * @global array  $userdata
 *
 * @param  string $tag           Identifies 'register' or 'update'.
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_user_register( $tag ) {

	// Get the globals.
	global $user_ID, $wpmem, $wpmem_themsg, $userdata; 
	
	$wpmem->user->register_validate( $tag );

	switch ( $tag ) {

	case "register":
		
		/**
		 * Filter registration data after validation before data insertion.
		 *
		 * @since 2.8.2
		 *
		 * @param array  $wpmem->user->post_data An array of the registration field data.
		 * @param string $tag    A switch to indicate the action (new|edit).
		 */
		$wpmem->user->post_data = apply_filters( 'wpmem_register_data', $wpmem->user->post_data, 'new' ); 

		/**
		 * Fires before any insertion/emails.
		 *
		 * This action is the final step in pre registering a user. This
		 * can be used for attaching custom validation to the registration
		 * process. It cannot be used for changing any user registration
		 * data. Use the wpmem_register_data filter for that.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_pre_register_data', $wpmem->user->post_data );

		// If the _pre_register_data hook sends back an error message.
		if ( $wpmem_themsg ) { 
			return $wpmem_themsg;
		}

		// Main new user fields are ready.
		$new_user_fields = array (
			'user_pass'       => $wpmem->user->post_data['password'], 
			'user_login'      => $wpmem->user->post_data['username'],
			'user_nicename'   => $wpmem->user->post_data['user_nicename'],
			'user_email'      => $wpmem->user->post_data['user_email'],
			'display_name'    => $wpmem->user->post_data['display_name'],
			'nickname'        => $wpmem->user->post_data['nickname'],
			'user_registered' => $wpmem->user->post_data['user_registered'],
			'role'            => $wpmem->user->post_data['user_role']
		);

		// Get any excluded meta fields.
		$wpmem->excluded_meta = wpmem_get_excluded_meta( 'register' );

		// Fields for wp_insert_user: user_url, first_name, last_name, description, jabber, aim, yim.
		$new_user_fields_meta = array( 'user_url', 'first_name', 'last_name', 'description', 'jabber', 'aim', 'yim' );
		foreach ( $wpmem->fields as $meta_key => $field ) {
			if ( in_array( $meta_key, $new_user_fields_meta ) ) {
				if ( $field['register'] && ! in_array( $meta_key, $wpmem->excluded_meta ) ) {
					$new_user_fields[ $meta_key ] = $wpmem->user->post_data[ $meta_key ];
				}
			}
		}

		// Inserts to wp_users table.
		$wpmem->user->post_data['ID'] = wp_insert_user( $new_user_fields );

		/**
		 * Fires after user insertion but before email.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_register_data', $wpmem->user->post_data );

		/**
		 * Fires after registration is complete.
		 *
		 * @since 2.7.1
		 * @since 3.1.0 Added $fields
		 * @since 3.1.7 Changed $fields to $this->post_data
		 * @since 3.3.0 Moved to registration function.
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_register_redirect', $wpmem->user->post_data );

		// successful registration message
		return "success";
		break;

	case "update":

		if ( $wpmem_themsg ) { 
			return "updaterr";
			exit();
		}

		/*
		 * Doing a check for existing email is not the same as a new reg. check first to 
		 * see if it's different, then check if it is a valid address and it exists.
		 */
		global $current_user; wp_get_current_user();
		if ( isset( $wpmem->user->post_data['user_email'] ) ) {
			if ( $wpmem->user->post_data['user_email'] != $current_user->user_email ) {
				if ( email_exists( $wpmem->user->post_data['user_email'] ) ) { 
					return "email";
					exit();
				} 
				if ( in_array( 'user_email', $wpmem->fields ) && ! is_email( $wpmem->user->post_data['user_email']) ) { 
					$wpmem_themsg = $wpmem->get_text( 'reg_valid_email' );
					return "updaterr";
					exit();
				}
			}
		}

		// If form includes email confirmation, validate that they match.
		if ( array_key_exists( 'confirm_email', $wpmem->user->post_data ) && $wpmem->user->post_data['confirm_email'] != $wpmem->user->post_data ['user_email'] ) { 
			$wpmem_themsg = $wpmem->get_text( 'reg_email_match' );
			return "updaterr";
			exit();
		}
		
		// Add the user_ID to the fields array.
		$wpmem->user->post_data['ID'] = $user_ID;
		
		/** This filter is documented in register.php */
		$wpmem->user->post_data = apply_filters( 'wpmem_register_data', $wpmem->user->post_data, 'edit' ); 
		
		/**
		 * Fires before data insertion.
		 *
		 * This action is the final step in pre updating a user. This
		 * can be used for attaching custom validation to the update
		 * process. It cannot be used for changing any user update
		 * data. Use the wpmem_register_data filter for that.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted update data.
		 */
		do_action( 'wpmem_pre_update_data', $wpmem->user->post_data );
		
		// If the _pre_update_data hook sends back an error message.
		if ( $wpmem_themsg ){ 
			return "updaterr";
		}

		// A list of fields that can be updated by wp_update_user.
		$native_fields = array( 
			'user_nicename',
			'user_url',
			'user_email',
			'display_name',
			'nickname',
			'first_name',
			'last_name',
			'description',
			'role',
			'jabber',
			'aim',
			'yim' 
		);
		$native_update = array( 'ID' => $wpmem->user->post_data['ID'] );

		foreach ( $wpmem->fields as $meta_key => $field ) {
			// If the field is not excluded, update accordingly.
			if ( ! in_array( $meta_key, wpmem_get_excluded_meta( 'update' ) ) ) {
				if ( 'file' != $field['type'] && 'image' != $field['type'] ) {
					switch ( $meta_key ) {
	
					// If the field can be updated by wp_update_user.
					case( in_array( $meta_key, $native_fields ) ):
						$wpmem->user->post_data[ $meta_key ] = ( isset( $wpmem->user->post_data[ $meta_key ] ) ) ? $wpmem->user->post_data[ $meta_key ] : '';
						$native_update[ $meta_key ] = $wpmem->user->post_data[ $meta_key ];
						break;
	
					// If the field is password.
					case( 'password' ):
						// Do nothing.
						break;
	
					// Everything else goes into wp_usermeta.
					default:
						if ( ( 'register' == $tag && true == $field['register'] ) || ( 'update' == $tag && true == $field['profile'] ) ) {
							update_user_meta( $wpmem->user->post_data['ID'], $meta_key, $wpmem->user->post_data[ $meta_key ] );
						}
						break;
					}
				}
			}
		}
		
		// Handle file uploads, if any.
		if ( ! empty( $_FILES ) ) {
			$wpmem->user->upload_user_files( $wpmem->user->post_data['ID'], $wpmem->fields );
		}

		// Update wp_update_user fields.
		wp_update_user( $native_update );

		/**
		 * Fires at the end of user update data insertion.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_update_data', $wpmem->user->post_data );

		return "editsuccess"; exit();
		break;
	}
} // End registration function.

// End of file.