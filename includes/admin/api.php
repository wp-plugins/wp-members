<?php
/**
 * WP-Members Admin API Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 *
 * Functions included:
 * - wpmem_add_custom_email
 * - wpmem_add_custom_dialog
 * - wpmem_is_tab
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Wrapper function for adding custom emails.
 *
 * @since 3.1.1
 *
 * @global object $wpmem         The WP_Members object class.
 * @param  string $tag           Slug for the custom email.
 * @param  string $heading       Heading to display in admin panel.
 * @param  string $subject_input Slug for the subject. 
 * @param  string $message_input Slug for the message body.
 */
function wpmem_add_custom_email( $tag, $heading, $subject_input, $message_input ) {
	global $wpmem;
	$args = array(
		'name'          => $tag,
		'heading'       => $heading, 
		'subject_input' => $subject_input,
		'body_input'    => $message_input,	
	);
	$wpmem->admin->add_email( $args );
}

/**
 * Checks the current tab being displayed in the admin panel.
 *
 * @since 3.1.4
 *
 * @param  string $tab The tab slug.
 * @return bool
 */
function wpmem_is_tab( $tab ) {
	return ( $tab == wpmem_get( 'tab', false, 'get' ) ) ? true : false;
}

/**
 * Utility function generates link to user profile.
 *
 * @since 3.1.7
 *
 * @param  int    $user_id
 * @return string user profile URL.
 */
function wpmem_admin_user_profile( $user_id ) {
	return add_query_arg( 'user_id', $user_id, admin_url( 'user-edit.php' ) );
}

/**
 * Wrapper for form_post_url().
 *
 * @since 3.1.8
 *
 * @global object $wpmem The WP_Members Object.
 * @param  string $tab   The plugin tab being displayed.
 * @param  mixed  $args  Array of additional arguments|boolean. Default: false.
 * @return string $url
 */
function wpmem_admin_form_post_url( $args = false ) {
	global $wpmem;
	return $wpmem->admin->form_post_url( $args );
}

/**
 * Returns an array of WordPress reserved terms.
 *
 * @since 3.0.2
 * @since 3.2.3 Moved to Admin API as wrapper for WP_Members_Admin_API::wp_reserved_terms().
 *
 * @global object $wpmem
 * @return array  An array of WordPress reserved terms.
 */
function wpmem_wp_reserved_terms() {
	global $wpmem;
	return $wpmem->admin->wp_reserved_terms();
}

/**
 * Export all or selected users
 *
 * @since 2.9.7
 * @since 3.2.0 Updated to use fputcsv.
 * @since 3.2.1 Added user data filters.
 * @since 3.3.0 Call object class static method.
 *
 * @global object $wpmem
 *
 * @param array $args
 * @param array $users
 */
function wpmem_export_users( $args, $users = null ) {
	global $wpmem;
	include_once( $wpmem->path . 'includes/admin/class-wp-members-export.php' );
	WP_Members_Export::export_users( $args, $users );
}