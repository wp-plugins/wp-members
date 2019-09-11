<?php
/**
 * WP-Members Form Building Functions
 *
 * Handles functions that build the various forms.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Form Building Functions
 * @author Chad Butler
 * @copyright 2006-2019
 *
 * Functions Included:
 * - wpmem_inc_login
 * - wpmem_login_form
 * - wpmem_inc_registration
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_inc_login' ) ):
/**
 * Login Dialog.
 *
 * Loads the login form for user login.
 *
 * @since 1.8
 * @since 3.1.4 Global $wpmem_regchk no longer needed.
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_login_form()
 *
 * @global object $post         The WordPress Post object.
 * @global object $wpmem        The WP_Members object.
 * @param  string $page         If the form is being displayed in place of blocked content. Default: page.
 * @param  string $redirect_to  Redirect URL. Default: null.
 * @param  string $show         If the form is being displayed in place of blocked content. Default: show.
 * @return string $str          The generated html for the login form.
 */
function wpmem_inc_login( $page = "page", $redirect_to = null, $show = 'show' ) {
	global $wpmem;
	return $wpmem->forms->do_login_form( $page, $redirect_to, $show );
}
endif;

if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Dialog.
 *
 * Outputs the form for new user registration and existing user edits.
 *
 * @since 2.5.1
 * @since 3.1.7 Now a wrapper for $wpmem->forms->register_form()
 * @since 3.2.0 Preparing for deprecation, use wpmem_register_form() instead.
 *
 * @global object $wpmem        The WP_Members object.
 * @param  string $tag          (optional) Toggles between new registration ('new') and user profile edit ('edit').
 * @param  string $heading      (optional) The heading text for the form, null (default) for new registration.
 * @return string $form         The HTML for the entire form as a string.
 */
function wpmem_inc_registration( $tag = 'new', $heading = '', $redirect_to = null ) {
	global $wpmem;
	$args = array( 'tag' => $tag, 'heading' => $heading, 'redirect_to' => $redirect_to );
	return $wpmem->forms->register_form( $args );
} // End wpmem_inc_registration.
endif;

/**
 * Create an attribution link in the form.
 *
 * @since 2.6.0
 * @since 3.1.1 Updated to use new object setting.
 *
 * @global object $wpmem
 * @return string $str
 */
function wpmem_inc_attribution() {

	global $wpmem;
	$str = '
	<div align="center">
		<small>Powered by <a href="https://rocketgeek.com" target="_blank">WP-Members</a></small>
	</div>';
		
	return ( 1 == $wpmem->attrib ) ? $str : '';
}

/**
 * Add registration fields to WooCommerce registration.
 *
 * As of WooCommerce 3.0, the WC registration process no longer includes the
 * WP register_form action hook.  It only includes woocommerce_register_form.
 * In previous versions, WP-Members hooked to register_form for both WP and
 * WC registration. To provide backward compatibility with users who may
 * continue to use updated WP-Members with pre-3.0 WooCommerce, this function
 * checks for WC version and if it is older than 3.0 it will ignore adding
 * the WP-Members form fields as they would have already been added when the
 * register_form action hook fired.
 *
 * @since 3.1.8
 */
function wpmem_woo_register_form() {
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
			wpmem_wp_register_form( 'woo' );
		}
	}
}

// End of file.