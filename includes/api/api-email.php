<?php
/**
 * WP-Members Email API Functions
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
 */

/**
 * Returns the wp_mail from address (if set).
 *
 * @since 2.7
 * @since 3.1 Converted to use email var in object.
 *
 * @global object $wpmem
 * @return string $wpmem_mail_from|$email
 */
function wpmem_mail_from() {
	global $wpmem;
	return $wpmem->email->from;
}

/**
 * Returns the wp_mail from name (if set).
 *
 * @since 2.7
 * @since 3.1 Converted to use email var in object.
 *
 * @global object $wpmem
 * @return string $wpmem_mail_from_name|$name
 */
function wpmem_mail_from_name() {
	global $wpmem;
	return $wpmem->email->from_name;
}

/**
 * Builds emails for the user.
 *
 * @since 3.2.3
 *
 * @global object $wpmem         The WP_Members object.
 * @param  mixed  $args {
 *     Settings arguments or The User's ID.
 *
 *     @type int    $user_id
 *     @type string $password
 *     @type string $tag
 *     @type array  $wpmem_fields
 *     @type array  $fields
 *     @type array  $custom {
 *          Settings for custom email if used (optional).
 *
 *          @type string $subj The email subject.
 *          @type string $body The email message body.
 *          @type string $tag  The email tag.
 *      }
 * }
 * @param  string $password      Password from the registration process.
 * @param  string $tag           Indicates the email being sent (newreg|newmod|appmod|repass|getuser).
 * @param  array  $wpmem_fields  Array of the WP-Members fields (defaults to null).
 * @param  array  $fields        Array of the registration data (defaults to null).
 * @param  array  $custom {
 *     Array of custom email information (defaults to null).
 *
 *     @type string $subj The email subject.
 *     @type string $body The email message body.
 *     @type string $tag  The email tag.
 * }
 *
 * @todo Will probably change the WP_Members_Email::to_user() arguments to just accept the array.
 */
function wpmem_email_to_user( $args, $password = null, $tag = null, $wpmem_fields = null, $field_data = null, $custom = null ) {
	global $wpmem;
	if ( is_array( $args ) ) {
		$user_id      = $args['user_id'];
		$password     = $args['password'];
		$tag          = $args['tag'];
		$wpmem_fields = $args['wpmem_fields'];
		$field_data   = $args['field_data'];
		$custom       = $args['custom'];
	} else {
		$user_id = $args;
	}
	$wpmem->email->to_user( $user_id, $password, $tag, $wpmem_fields, $field_data, $custom );
	return;
}

if ( ! function_exists( 'wpmem_notify_admin' ) ):
/**
 * Builds the email for admin notification of new user registration.
 *
 * @since 2.3
 * @since 3.2.3 Changed inputs.
 *
 * @global object $wpmem                The WP_Members object.
 * @param  mixed  $args                 Settings arguments or The User's ID.
 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param  array  $field_data           Array of the registration data (defaults to null).
 */
function wpmem_notify_admin( $args, $wpmem_fields = null, $field_data = null ) {
	global $wpmem;
	if ( is_array( $args ) ) {
		$user_id      = $args['user_id'];
		$wpmem_fields = $args['wpmem_fields'];
		$field_data   = $args['field_data'];
	} else {
		$user_id = $args;
	}
	$wpmem->email->notify_admin( $user_id, $wpmem_fields, $field_data );
}
endif;