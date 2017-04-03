<?php
/*
Plugin Name: WP-Members
Plugin URI:  http://rocketgeek.com
Description: WP access restriction and user registration.  For more information on plugin features, refer to <a href="http://rocketgeek.com/plugins/wp-members/users-guide/">the online Users Guide</a>. A <a href="http://rocketgeek.com/plugins/wp-members/quick-start-guide/">Quick Start Guide</a> is also available. WP-Members(tm) is a trademark of butlerblog.com.
Version:     3.1.7
Author:      Chad Butler
Author URI:  http://butlerblog.com/
Text Domain: wp-members
Domain Path: /lang
License:     GPLv2
*/


/*  
	Copyright (c) 2006-2017  Chad Butler

	The name WP-Members(tm) is a trademark of butlerblog.com

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html
*/


/*
	A NOTE ABOUT LICENSE:

	While this plugin is freely available and open-source under the GPL2
	license, that does not mean it is "public domain." You are free to modify
	and redistribute as long as you comply with the license. Any derivative 
	work MUST be GPL licensed and available as open source.  You also MUST give 
	proper attribution to the original author, copyright holder, and trademark
	owner.  This means you cannot change two lines of code and claim copyright 
	of the entire work as your own.  The GPL2 license requires that if you
	modify this code, you must clearly indicate what section(s) you have
	modified and you may only claim copyright of your modifications and not
	the body of work.  If you are unsure or have questions about how a 
	derivative work you are developing complies with the license, copyright, 
	trademark, or if you do not understand the difference between
	open source and public domain, contact the original author at:
	http://rocketgeek.com/contact/.


	INSTALLATION PROCEDURE:
	
	For complete installation and usage instructions,
	visit http://rocketgeek.com
*/


// Initialize constants.
define( 'WPMEM_VERSION', '3.1.7' );
define( 'WPMEM_DEBUG', false );
define( 'WPMEM_DIR',  plugin_dir_url ( __FILE__ ) );
define( 'WPMEM_PATH', plugin_dir_path( __FILE__ ) );

// Localization.
add_action( 'init', 'wpmem_load_textdomain' ); //add_action( 'plugins_loaded', 'wpmem_load_textdomain' );

// Initialize the plugin.
add_action( 'after_setup_theme', 'wpmem_init', 10 );

// Install the plugin.
register_activation_hook( __FILE__, 'wpmem_install' );

// Downgrade settings on deactivation.
//register_deactivation_hook( __FILE__, 'wpmem_downgrade' );


/**
 * Initialize WP-Members.
 *
 * The initialization function contains much of what was previously just
 * loaded in the main plugin file. It has been moved into this function
 * in order to allow action hooks for loading the plugin and initializing
 * its features and options.
 *
 * @since 2.9.0
 * @since 3.1.6 Dependencies now loaded by object.
 *
 * @global object $wpmem The WP-Members object class.
 */
function wpmem_init() {

	// Set the object as global.
	global $wpmem;

	/**
	 * Fires before initialization of plugin options.
	 *
	 * @since 2.9.0
	 */
	do_action( 'wpmem_pre_init' );

	/**
	 * Load the WP_Members class.
	 */
	require_once( WPMEM_PATH . 'inc/class-wp-members.php' );
	
	// Invoke the WP_Members class.
	$wpmem = new WP_Members();

	/**
	 * Fires after initialization of plugin options.
	 *
	 * @since 2.9.0
	 */
	do_action( 'wpmem_after_init' );
}


/**
 * Adds the plugin options page and JavaScript.
 *
 * @since 2.5.2
 */
function wpmem_admin_options() {
	if ( ! is_multisite() || ( is_multisite() && current_user_can( 'edit_theme_options' ) ) ) {
		$plugin_page = add_options_page ( 'WP-Members', 'WP-Members', 'manage_options', 'wpmem-settings', 'wpmem_admin' );
	}
}


/**
 * Install the plugin options.
 *
 * @since 2.5.2
 * @since 3.1.1 Added rollback.
 * @since 3.1.6 Removed rollback.
 *
 * @param 
 */
function wpmem_install() {

	/**
	 * Load the install file.
	 */
	require_once( WPMEM_PATH . 'wp-members-install.php' );

	// Multisite requires different install process.
	if ( is_multisite() ) {

		// If it is multisite, install options for each blog.
		global $wpdb;
		$blogs = $wpdb->get_results(
			"SELECT blog_id
			FROM {$wpdb->blogs}
			WHERE site_id = '{$wpdb->siteid}'
			AND spam = '0'
			AND deleted = '0'
			AND archived = '0'"
		);
		$original_blog_id = get_current_blog_id();   
		foreach ( $blogs as $blog_id ) {
			switch_to_blog( $blog_id->blog_id );
			wpmem_do_install();
		}
		switch_to_blog( $original_blog_id );

	} else {

		// Single site install.
		wpmem_do_install();
	}
}


/**
 * Runs downgrade steps in install function.
 *
 * @since 3.1.1
 */
function wpmem_downgrade() {
	//wpmem_install( 'downgrade' );
}


add_action( 'wpmu_new_blog', 'wpmem_mu_new_site', 10, 6 );
/**
 * Install default plugin options for a newly added blog in multisite.
 *
 * @since 2.9.3
 *
 * @param $blog_id
 * @param $user_id
 * @param $domain
 * @param $path
 * @param $site_id
 * @param $meta
 */
function wpmem_mu_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	/**
	 * Load the install file.
	 */
	require_once( WPMEM_PATH . 'wp-members-install.php' );

	// Switch to the new blog.
	switch_to_blog( $blog_id );

	// Run the WP-Members install.
	wpmem_do_install();

	// Switch back to the current blog.
	restore_current_blog();
}


/**
 * Loads translation files.
 *
 * @since 3.0.0
 */
function wpmem_load_textdomain() {
	
	// @see: https://ulrich.pogson.ch/load-theme-plugin-translations for notes on changes.
	
	// Plugin textdomain.
	$domain = 'wp-members';
	
	// Wordpress locale.
	/** This filter is documented in wp-includes/l10n.php */
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	/**
	 * Filter translation file.
	 *
	 * If the translate.wordpress.org language pack is available, it will
	 * be /wp-content/languages/plugins/wp-members-{locale}.mo by default.
	 * You can filter this if you want to load a language pack from a
	 * different location (or different file name).
	 *
	 * @since 3.0.0
	 *
	 * @param string $file The translation file to load.
	 */
	$file = apply_filters( 'wpmem_localization_file', trailingslashit( WP_LANG_DIR ) . 'plugins/' . $domain . '-' . $locale . '.mo' );

	$loaded = load_textdomain( $domain, $file );
	if ( $loaded ) {
		return $loaded;
	} else {
		
		/**
		 * Filter translation directory.
		 *
		 * @since 3.0.3
		 *
		 * @param string $dir The translation directory.
		 */
		$dir = apply_filters( 'wpmem_localization_dir', dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		load_plugin_textdomain( $domain, FALSE, $dir );
	}
	return;
}

// End of file.