<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage administration.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2018
 *
 * Functions included:
 * - wpmem_admin
 * - wpmem_admin_do_tab
 * - wpmem_admin_tabs
 * - wpmem_admin_action
 * - wpmem_admin_add_new_user
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Primary admin function.
 *
 * @since 2.1.0
 * @since 3.1.0 Added WP_Members_Admin_API.
 *
 * @global object $wpmem The WP_Members object.
 */
function wpmem_admin() {

	$did_update = ( isset( $_POST['wpmem_admin_a'] ) ) ? wpmem_admin_action( sanitize_text_field( $_POST['wpmem_admin_a'] ) ) : false;

	global $wpmem;

	if ( $wpmem->captcha ) {
		add_filter( 'wpmem_admin_tabs',   'wpmem_add_captcha_tab' );
		add_action( 'wpmem_admin_do_tab', 'wpmem_a_captcha_tab', 1, 1 );
	}
	if ( $wpmem->dropins ) {
		add_filter( 'wpmem_admin_tabs',   'wpmem_add_dropins_tab'          );
		add_action( 'wpmem_admin_do_tab', 'wpmem_render_dropins_tab', 1, 1 );
	} ?>

	<div class="wrap">
		<?php 
		$tab = sanitize_text_field( wpmem_get( 'tab', 'options', 'get' ) );

		// Render the tab being displayed.
		$wpmem->admin->do_tabs( $tab );

		// Render any warning messages.
		wpmem_a_do_warnings( $did_update );

		/**
		 * Fires at the end of creating an admin panel tab.
		 *
		 * This action is part of the plugin's admin panel API for adding
		 * additional admin tabs. This action is for adding content for
		 * a custom tab.
		 *
		 * @since 2.8.0
		 *
		 * @param string $tab The tab being generated.
		 */
		do_action( 'wpmem_admin_do_tab', $tab );
		?>
	</div><!-- .wrap --><?php

	return;
}


/**
 * Handles the various update actions for the default tabs.
 *
 * @since 2.8.0
 *
 * @param  string $action     The action that is being done.
 * @return string $did_update The update message result.
 */
function wpmem_admin_action( $action ) {

	$did_update = ''; // makes sure $did_update is defined
	switch ( $action ) {

	case 'update_settings':
	case 'update_cpts':
		$did_update = ( 'update_cpts' == $action ) ? wpmem_update_cpts() : wpmem_update_options();
		break;

	case 'update_dialogs':
		$did_update = wpmem_update_dialogs();
		break;

	case 'update_emails':
		$did_update = wpmem_update_emails();
		break;

	case 'update_captcha':
		$did_update = wpmem_update_captcha();
		break;
	}

	return $did_update;
}


/**
 * Adds WP-Members custom fields to the WP Add New User form.
 *
 * @since 2.9.1
 */
function wpmem_admin_add_new_user() {
	// Output the custom registration fields.
	echo wpmem_do_wp_newuser_form();
	return;
}

// End of File.