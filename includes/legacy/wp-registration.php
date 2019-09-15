<?php
/**
 * WP-Members Functions for WordPress Native Registration
 *
 * Handles functions that add WP-Members custom fields to the 
 * WordPress native (wp-login.php) registration and the 
 * Users > Add New screen.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
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
	$exclude = wpmem_get_excluded_meta( 'wp-register' );

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
	$is_native  = ( __( 'Register' ) == wpmem_get( 'wp-submit' ) ) ? true : false;
	// Is this a Users > Add New process? Checks the post action.
	$is_add_new = ( 'createuser' == wpmem_get( 'action' ) ) ? true : false;
	// Is this a WooCommerce checkout registration? Checks for WC fields.
	$is_woo     = ( wpmem_get( 'woocommerce_checkout_place_order' ) || wpmem_get( 'woocommerce-register-nonce' ) ) ? true : false;
	if ( $is_native || $is_add_new || $is_woo ) {
		// Get any excluded meta fields.
		$exclude = wpmem_get_excluded_meta( 'wp-register' );
		foreach ( wpmem_fields( 'wp_finalize' ) as $meta_key => $field ) {
			$value = wpmem_get( $meta_key, false );
			if ( false !== $value && ! in_array( $meta_key, $exclude ) && 'file' != $field['type'] && 'image' != $field['type'] ) {
				if ( 'multiselect' == $field['type'] || 'multicheckbox' == $field['type'] ) {
					$value = implode( $field['delimiter'], $value );
				}
				$sanitized_value = sanitize_text_field( $value );
				update_user_meta( $user_id, $meta_key, $sanitized_value );
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
 *
 * @global object $wpmem
 */
function wpmem_wplogin_stylesheet() {
	global $wpmem;
	// @todo Should this enqueue styles?
	echo '<link rel="stylesheet" id="custom_wp_admin_css"  href="' . $wpmem->url . 'assets/css/wp-login.css" type="text/css" media="all" />';
}

// End of file.