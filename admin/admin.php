<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage administration.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions included:
 * - wpmem_a_do_field_reorder
 * - wpmem_admin_plugin_links
 * - wpmem_load_admin_js
 * - wpmem_a_captcha_tab
 * - wpmem_add_captcha_tab
 * - wpmem_admin
 * - wpmem_admin_do_tab
 * - wpmem_admin_tabs
 * - wpmem_admin_action
 * - wpmem_admin_add_new_user
 * - wpmem_admin_enqueue_scripts
 */


/** 
 * Actions and Filters
 */
add_action( 'admin_enqueue_scripts',         'wpmem_admin_enqueue_scripts' );
add_action( 'wpmem_admin_do_tab',            'wpmem_admin_do_tab' );
add_action( 'wp_ajax_wpmem_a_field_reorder', 'wpmem_a_do_field_reorder' );
add_action( 'user_new_form',                 'wpmem_admin_add_new_user' );
add_filter( 'plugin_action_links',           'wpmem_admin_plugin_links', 10, 2 );


/**
 * Calls the function to reorder fields.
 *
 * @since 2.8.0
 */
function wpmem_a_do_field_reorder() {
	/**
	 * Load the fields tab functions.
	 */
	include_once( WPMEM_PATH . 'admin/tab-fields.php' );

	// Reorder registration fields.
	wpmem_a_field_reorder();
}


/**
 * Filter to add link to settings from plugin panel.
 *
 * @since 2.4.0
 *
 * @param  array  $links
 * @param  string $file
 * @return array  $links
 */
function wpmem_admin_plugin_links( $links, $file ) {
	static $wpmem_plugin;
	if ( ! $wpmem_plugin ) {
		$wpmem_plugin = plugin_basename( WPMEM_PATH . '/wp-members.php' );
	}
	if ( $file == $wpmem_plugin ) {
		$settings_link = '<a href="' . add_query_arg( 'page', 'wpmem-settings', 'options-general.php' ) . '">' . __( 'Settings', 'wp-members' ) . '</a>';
		$links = array_merge( array( $settings_link ), $links );
	}
	return $links;
}


/**
 * Loads the admin javascript and css files.
 *
 * @since 2.5.1
 * @deprecated 3.0.6 Replaced by wpmem_admin_enqueue_scripts().
 */
function wpmem_load_admin_js() {
	wpmem_write_log( "wpmem_load_admin_js() is deprecated as of WP-Members 3.0.6" );
	// Queue up admin ajax and styles.
	wp_enqueue_script( 'wpmem-admin-js',  WPMEM_DIR . 'admin/js/admin.js',   '', WPMEM_VERSION );
	wp_enqueue_style ( 'wpmem-admin-css', WPMEM_DIR . 'admin/css/admin.css', '', WPMEM_VERSION );
}


/**
 * Creates the captcha tab.
 *
 * @since 2.8.0
 *
 * @param  string      $tab The admin tab being displayed.
 * @return string|bool      The captcha options tab, otherwise false.
 */
function wpmem_a_captcha_tab( $tab ) {
	if ( $tab == 'captcha' ) {
		return wpmem_a_build_captcha_options();
	} else {
		return false;
	}
}


/**
 * Adds the captcha tab.
 *
 * @since 2.8.0
 *
 * @param  array $tabs The array of tabs for the admin panel.
 * @return array       The updated array of tabs for the admin panel.
 */
function wpmem_add_captcha_tab( $tabs ) {
	return array_merge( $tabs, array( 'captcha' => 'Captcha' ) );
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

	$did_update = ( isset( $_POST['wpmem_admin_a'] ) ) ? wpmem_admin_action( $_POST['wpmem_admin_a'] ) : false;

	global $wpmem;

	if ( $wpmem->captcha ) {
		add_filter( 'wpmem_admin_tabs', 'wpmem_add_captcha_tab' );
		add_action( 'wpmem_admin_do_tab', 'wpmem_a_captcha_tab', 1, 1 );
	} ?>

	<div class="wrap">
		<?php screen_icon( 'options-general' ); ?>
		<!--<h2>WP-Members <?php _e('Settings', 'wp-members'); ?></h2>-->
		<?php 
		$tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'options';

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
 * Displays the content for default tabs.
 *
 * While this function displays only the default tabs (options, fields, emails
 * and dialogs), custom tabs can be added via the action hook wpmem_admin_do_tab
 * in the wpmem_admin() function.
 * 
 * @since 2.8.0
 *
 * @param string $tab The tab that we are on and displaying.
 */
function wpmem_admin_do_tab( $tab ) {

	switch ( $tab ) {

	case 'options' :
		wpmem_a_build_options();
		break;
	case 'fields' :
		wpmem_a_build_fields();
		break;
	case 'dialogs' :
		wpmem_a_build_dialogs();
		break;
	case 'emails' :
		wpmem_a_build_emails();
		break;
	}
}


/**
 * Assemble the tabs for the admin panel.
 *
 * Creates the defaults tabs array (options, fields, dialogs, emails) that
 * can be extended for custom admin tabs with the wpmem_admin_tabs filter.
 *
 * @since 2.8.0
 * @since 3.1.0 Wrapper for API admin_tabs().
 *
 * @global object $wpmem   The WP_Members object class.
 * @param  string $current he tab that we are on.
 */
function wpmem_admin_tabs( $current = 'options' ) {
	global $wpmem;
	$wpmem->admin->do_tabs( $current );
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

	case 'update_fields':
	case 'add_field': 
	case 'edit_field':
		$did_update = wpmem_update_fields( $action );
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
	/**
	 * Load WP native registration functions.
	 */
	include_once( WPMEM_PATH . 'inc/wp-registration.php' );
	// Output the custom registration fields.
	echo wpmem_do_wp_newuser_form();
	return;
}


/**
 * Enqueues the admin javascript and css files.
 *
 * Only loads the js and css on admin screens that use them.
 *
 * @since 3.0.6
 *
 * @param str $hook The admin screen hook being loaded.
 */
function wpmem_admin_enqueue_scripts( $hook ) {
	if ( $hook == 'edit.php' || $hook == 'settings_page_wpmem-settings' ) {
		wp_enqueue_style( 'wpmem-admin', WPMEM_DIR . 'admin/css/admin.css', '', WPMEM_VERSION );
	}
	if ( $hook == 'settings_page_wpmem-settings' ) {
		wp_enqueue_script( 'wpmem-admin', WPMEM_DIR . 'admin/js/admin.js', '', WPMEM_VERSION );
	}
}

// End of File.