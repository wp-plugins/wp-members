<?php
/*
Plugin Name: WP-Members
Plugin URI:  http://rocketgeek.com
Description: WP access restriction and user registration.  For more information on plugin features, refer to <a href="http://rocketgeek.com/plugins/wp-members/users-guide/">the online Users Guide</a>. A <a href="http://rocketgeek.com/plugins/wp-members/quick-start-guide/">Quick Start Guide</a> is also available. WP-Members(tm) is a trademark of butlerblog.com.
Version:     2.8.7
Author:      Chad Butler
Author URI:  http://butlerblog.com/
License:     GPLv2
*/


/*  
	Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)

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

	While this plugin is released as free and open-source under the GPL2
	license, that does not mean it is "public domain." You are free to modify
	and redistribute as long as you comply with the license. Any derivative 
	work MUST be GPL licensed and available as open source.  You also MUST give 
	proper attribution to the original author, copyright holder, and trademark
	owner.  This means you cannot change two lines of code and claim copyright 
	of the entire work as your own.  If you are unsure or have questions about 
	how a derivative work you are developing complies with the license, 
	copyright, trademark, or if you do not understand the difference between
	open source and public domain, contact the original author at:
	plugins@butlerblog.com.


	INSTALLATION PROCEDURE:
	
	For complete installation and usage instructions,
	visit http://rocketgeek.com
*/


/**
 * CONSTANTS, ACTIONS, HOOKS, FILTERS & INCLUDES
 */


/**
 * start with any potential translation
 */
load_plugin_textdomain( 'wp-members', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );


/**
 * load options
 */
$wpmem_settings = get_option( 'wpmembers_settings' );


/**
 * define constants based on option settings
 */
define( 'WPMEM_VERSION',      '2.8.7' );
define( 'WPMEM_DEBUG',        false );

// define('WPMEM_VERSION',    $wpmem_settings[0]  );
define( 'WPMEM_BLOCK_POSTS',  $wpmem_settings[1]  );
define( 'WPMEM_BLOCK_PAGES',  $wpmem_settings[2]  );
define( 'WPMEM_SHOW_EXCERPT', $wpmem_settings[3]  );
define( 'WPMEM_NOTIFY_ADMIN', $wpmem_settings[4]  );
define( 'WPMEM_MOD_REG',      $wpmem_settings[5]  );
define( 'WPMEM_CAPTCHA',      $wpmem_settings[6]  );
define( 'WPMEM_NO_REG',       $wpmem_settings[7]  );
define( 'WPMEM_OLD_FORMS',    $wpmem_settings[8]  );
define( 'WPMEM_USE_EXP',      $wpmem_settings[9]  );
define( 'WPMEM_USE_TRL',      $wpmem_settings[10] );
define( 'WPMEM_IGNORE_WARN',  $wpmem_settings[11] );

define( 'WPMEM_MSURL',  get_option( 'wpmembers_msurl', null ) );
define( 'WPMEM_REGURL', get_option( 'wpmembers_regurl',null ) );

define( 'WPMEM_DIR',  plugin_dir_url ( __FILE__ ) );
define( 'WPMEM_PATH', plugin_dir_path( __FILE__ ) );


/**
 * define the stylesheet
 */
$wpmem_style = get_option( 'wpmembers_cssurl', null );
$wpmem_style = ( ! $wpmem_style ) ? get_option( 'wpmembers_style', null ) : $wpmem_style;
define( 'WPMEM_CSSURL', $wpmem_style );


/**
 * preload any custom functions, if available
 */
if( file_exists( WP_PLUGIN_DIR . '/wp-members-pluggable.php' ) ) {
	include( WP_PLUGIN_DIR . '/wp-members-pluggable.php' );
}


/**
 * preload the expiration module, if available
 */
if( in_array( 'wp-members-expiration/module.php' , get_option( 'active_plugins' ) ) ) { 
	define( 'WPMEM_EXP_MODULE', true ); 
} else {
	define( 'WPMEM_EXP_MODULE', false ); 
}


/**
 * load the core
 */
include_once( 'wp-members-core.php' );


/**
 * actions and filters
 */
add_action( 'init', 'wpmem' );                           // runs the wpmem() function right away, allows for setting cookies
add_action( 'widgets_init', 'widget_wpmemwidget_init' ); // if you are using widgets, this initializes the widget
add_action( 'wp_head', 'wpmem_head' );                   // runs functions for the head
add_action( 'admin_init', 'wpmem_chk_admin' );
add_action( 'admin_menu', 'wpmem_admin_options' );
add_action( 'user_register', 'wpmem_wp_reg_finalize' );
add_action( 'login_enqueue_scripts', 'wpmem_wplogin_stylesheet' );

add_filter( 'allow_password_reset', 'wpmem_no_reset' );  // prevents non-activated users from resetting password via wp-login
add_filter( 'the_content', 'wpmem_securify', 1, 1 );     // securifies the_content
add_filter( 'register_form', 'wpmem_wp_register_form' ); // adds wp-members fields to the default wp registration form
add_filter( 'registration_errors', 'wpmem_wp_reg_validate', 10, 3 );


/**
 * add the wp-members shortcode
 */
add_shortcode( 'wp-members', 'wpmem_shortcode' );


/**
 * load the stylesheet if using the new forms
 */
if( WPMEM_OLD_FORMS != 1 ) {
	add_action( 'wp_print_styles', 'wpmem_enqueue_style' );
}


/**
 * if registration is moderated, check for activation (blocks backend login by non-activated users)
 */
if( WPMEM_MOD_REG == 1 ) { 
	add_filter( 'authenticate', 'wpmem_check_activated', 99, 3 ); 
}


/**
 * scripts for admin panels only load for admins - makes the front-end of the plugin lighter
 */
function wpmem_chk_admin()
{
	// if user has a role that can edit users, load the admin functions
	if( current_user_can( 'edit_users' ) ) { 
		require_once( 'admin/admin.php' );
		require_once( 'admin/users.php' );
		include_once( 'admin/user-profile.php' );
	} else {
		// user profile actions for non-admins
		require_once( WPMEM_PATH . 'users.php' );
		add_action( 'show_user_profile', 'wpmem_user_profile'   );
		add_action( 'edit_user_profile', 'wpmem_user_profile'   );
		add_action( 'profile_update',    'wpmem_profile_update' );
	}
	
	// if user has a role that can edit posts, add the block/unblock meta boxes and custom post/page columns
	if( current_user_can( 'edit_posts' ) ) {
		include_once( 'admin/post.php' );
		add_action( 'add_meta_boxes', 'wpmem_block_meta_add' );  
		add_action( 'save_post', 'wpmem_block_meta_save' );
		add_filter( 'manage_posts_columns', 'wpmem_post_columns' );  
		add_action( 'manage_posts_custom_column', 'wpmem_post_columns_content', 10, 2 );
		add_filter( 'manage_pages_columns', 'wpmem_page_columns' );
		add_action( 'manage_pages_custom_column', 'wpmem_page_columns_content', 10, 2 );		
	}
}


/**
 * admin panel only loads if user has manage_options capabilities
 */
function wpmem_admin_options()
{
	$plugin_page = add_options_page ( 'WP-Members', 'WP-Members', 'manage_options', 'wpmem-settings', 'wpmem_admin'    );
	              //add_users_page   ( 'WP-Members', 'WP-Members', 'create_users',   'wpmem-users', 'wpmem_admin_users' );
	               add_action       ( 'load-'.$plugin_page, 'wpmem_load_admin_js' ); // enqueues javascript for admin
				  //add_action		( 'load-'.$plugin_page, 'wpmem_load_admin_help' );
}


/**
 * install scripts only load if we are installing, makes the plugin lighter
 */
register_activation_hook( __FILE__, 'wpmem_install' );
function wpmem_install()
{
	require_once( 'wp-members-install.php' );
	wpmem_do_install();
}

/** End of File **/