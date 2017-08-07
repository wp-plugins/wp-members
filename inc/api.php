<?php
/**
 * WP-Members API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members API Functions
 * @author Chad Butler 
 * @copyright 2006-2017
 *
 * Functions included:
 * - wpmem_redirect_to_login
 * - wpmem_is_blocked
 * - wpmem_login_url
 * - wpmem_register_url
 * - wpmem_profile_url
 * - wpmem_current_url
 * - wpmem_form_field
 * - wpmem_form_label
 * - wpmem_fields
 * - wpmem_gettext
 * - wpmem_use_custom_dialog
 * - wpmem_user_has_role
 * - wpmem_create_membership_number
 * - wpmem_login_status
 * - wpmem_get
 * - wpmem_is_reg_page
 * - wpmem_load_dropins
 * - wpmem_loginout
 * - wpmem_array_insert
 * - wpmem_is_user_activated
 * - wpmem_current_post_id
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
 * Wrapper for $wpmem->create_form_field().
 *
 * @since 3.1.2
 *
 * @param array  $args {
 *     @type string  $name        (required) The field meta key.
 *     @type string  $type        (required) The field HTML type (url, email, image, file, checkbox, text, textarea, password, hidden, select, multiselect, multicheckbox, radio).
 *     @type string  $value       (required) The field's value (can be a null value).
 *     @type string  $compare     (required) Compare value.
 *     @type string  $class       (optional) Class identifier for the field.
 *     @type boolean $required    (optional) If a value is required default: true).
 *     @type string  $delimiter   (optional) The field delimiter (pipe or comma, default: | ).
 *     @type string  $placeholder (optional) Defines the placeholder attribute.
 *     @type string  $pattern     (optional) Adds a regex pattern to the field (HTML5).
 *     @type string  $title       (optional) Defines the title attribute.
 *     @type string  $min         (optional) Adds a min attribute (HTML5).
 *     @type string  $max         (optional) Adds a max attribute (HTML5).
 * }
 * @return string The HTML of the form field.
 */
function wpmem_form_field( $args ) {
	global $wpmem;
	return $wpmem->forms->create_form_field( $args );
}

/**
 * Wrapper for $wpmem->create_form_label().
 *
 * @since 3.1.7
 *
 * @global object $wpmem
 * @param array  $args {
 *     @type string $meta_key
 *     @type string $label_text
 *     @type string $type
 *     @type string $class      (optional)
 *     @type string $required   (optional)
 *     @type string $req_mark   (optional)
 * }
 * @return string The HTML of the form label.
 */
function wpmem_form_label( $args ) {
	global $wpmem;
	return $wpmem->forms->create_form_label( $args );
}

/**
 * Wrapper to get form fields.
 *
 * @since 3.1.1
 * @since 3.1.5 Checks if fields array is set or empty before returning.
 * @since 3.1.7 Added wpmem_form_fields filter.
 *
 * @global object $wpmem  The WP_Members object.
 * @param  string $tag    The action being used (default: null).
 * @param  string $form   The form being generated.
 * @return array  $fields The form fields.
 */
function wpmem_fields( $tag = '', $form = 'default' ) {
	global $wpmem;
	// Load fields if none are loaded.
	if ( ! isset( $wpmem->fields ) || empty( $wpmem->fields ) ) {
		$wpmem->load_fields( $form );
	}
	
	// @todo Convert tag.
	$tag = wpmem_convert_tag( $tag );
	
	/**
	 * Filters the fields array.
	 *
	 * @since 3.1.7
	 *
	 * @param  array  $wpmem->fields
	 * @param  string $tag (optional)
	 */
	return apply_filters( 'wpmem_fields', $wpmem->fields, $tag );
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
 * Checks if user has a particular role.
 *
 * Utility function to check if a given user has a specific role. Users can
 * have multiple roles assigned, so it checks the role array rather than using
 * the incorrect method of current_user_can( 'role_name' ). The function can
 * check the role of the current user (default) or a specific user (if $user_id
 * is passed).
 *
 * @since 3.1.1
 * @since 3.1.6 Include accepting an array of roles to check.
 * @since 3.1.9 Return false if user is not logged in.
 *
 * @global object        $current_user Current user object.
 * @global object        $wpmem        WP_Members object.
 * @param  string|array  $role         Slug or array of slugs of the role being checked.
 * @param  int           $user_id      ID of the user being checked (optional).
 * @return boolean       $has_role     True if user has the role, otherwise false.
 */
function wpmem_user_has_role( $role, $user_id = false ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	global $current_user, $wpmem;
	$has_role = false;
	if ( $user_id ) {
		$user = get_userdata( $user_id );
	}
	if ( is_user_logged_in() && ! $user_id ) {
		$user = ( isset( $current_user ) ) ? $current_user : wp_get_current_user();
	}
	if ( is_array( $role ) ) {
		foreach ( $role as $r ) {
			if ( in_array( $r, $user->roles ) ) {
				return true;
			}
		}
	} else {
		return ( in_array( $role, $user->roles ) ) ? true : $has_role;
	}
}

/**
 * Checks if a user has a given meta value.
 *
 * @since 3.1.8
 *
 * @global object  $wpmem     WP_Members object.
 * @param  string  $meta      Meta key being checked.
 * @param  string  $value     Value the meta key should have (optional).
 * @param  int     $user_id   ID of the user being checked (optional).
 * @return boolean $has_meta  True if user has the meta value, otherwise false.
 */
function wpmem_user_has_meta( $meta, $value = false, $user_id = false ) {
	global $wpmem;
	$user_id = ( $user_id ) ? $user_id : get_current_user_id();
	$has_meta = false;
	$user_value = get_user_meta( $user_id, $meta, true );
	if ( $value ) {
		$has_meta = ( $user_value == $value ) ? true : $has_meta;
	} else {
		$has_meta = ( $value ) ? true : $has_meta;
	}
	return $has_meta;
}

/**
 * Creates a membership number.
 *
 * @since 3.1.1
 *
 * @param  array  $args {
 *     @type string $option
 *     @type string $meta_key
 *     @type int    $start     (optional, default 0)
 *     @type int    $increment (optional, default 1)
 *     @type int    $lead
 * }
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
 * @since 3.1.3
 *
 * @param  string $tag     The form field or query string.
 * @param  string $default The default value (optional).
 * @param  string $type    post|get|request (optional).
 * @return string 
 */
function wpmem_get( $tag, $default = '', $type = 'post' ) {
	switch ( $type ) {
		case 'post':
			return ( isset( $_POST[ $tag ] ) ) ? $_POST[ $tag ] : $default;
			break;
		case 'get':
			return ( isset( $_GET[ $tag ] ) ) ? $_GET[ $tag ] : $default;
			break;
		case 'request':
			return ( isset( $_REQUEST[ $tag ] ) ) ? $_REQUEST[ $tag ] : $default;
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
 * Wrapper for load_dropins()
 *
 * @since 3.1.4
 *
 * @global object $wpmem The WP_Members object.
 */
function wpmem_load_dropins() {
	global $wpmem;
	$wpmem->load_dropins();
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
 * Inserts array items at a specific point in an array.
 *
 * @since 3.1.6
 *
 * @param  array  $array Original array.
 * @param  array  $new   Array of new items to insert into $array.
 * @param  string $key   Array key to insert new items before or after.
 * @param  string $loc   Location to insert relative to $key (before|after) default:after.
 * @return array         Original array with new items inserted.
 */
function wpmem_array_insert( array $array, array $new, $key, $loc = 'after' ) {
	$keys = array_keys( $array );
	if ( 'before' == $loc ) {
		$pos = (int) array_search( $key, $keys );
	} else {
		$index = array_search( $key, $keys );
		$pos = ( false === $index ) ? count( $array ) : $index + 1;
	}
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}

/**
 * Checks if a user is activated.
 *
 * @since 3.1.7
 *
 * @param  int  $user_id
 * @return bool
 */
function wpmem_is_user_activated( $user_id = false ) {
	$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
	$active  = get_user_meta( $user_id, 'active', true );
	return ( $active != 1 ) ? false : true;
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

// End of file.