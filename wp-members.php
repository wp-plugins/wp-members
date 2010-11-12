<?php
/*
Plugin Name: WP-Members
Plugin URI:  http://butlerblog.com/wp-members/
Description: WP access restriction and user registration.  For more information and to download the free "quick start guide," visit <a href="http://butlerblog.com/wp-members">http://butlerblog.com/wp-members</a>. View the live demo at <a href="http://butlerblog.com/wpmembers">http://butlerblog.com/wpmembers</a>. WP-Members(tm) is a trademark of butlerblog.com.
Version:     2.4.0 beta 1
Author:      Chad Butler
Author URI:  http://butlerblog.com/
License:     GPLv2
*/


/*  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

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
	copyright, trademark, or do not	understand the difference between open 
	source and public domain, contact the original author at:
	plugins@butlerblog.com.


	INSTALLATION PROCEDURE:

	* Upload the wp-members folder to your plugins directory
	* Login to the WP admin
	* Go to plugins tab and activate the plugin
	* That's it!

	For more complete installation and usage instructions,
	visit http://butlerblog.com/wp-members/
*/


/*****************************************************
CONSTANTS, ACTIONS, HOOKS, FILTERS & INCLUDES
*****************************************************/


$wpmem_settings = get_option('wpmembers_settings');

define("WPMEM_VERSION",      "2.4.0");
define('WPMEM_DEBUG',        false);

define('WPMEM_VERSION',      $wpmem_settings[0]);
define('WPMEM_BLOCK_POSTS',  $wpmem_settings[1]);
define('WPMEM_BLOCK_PAGES',  $wpmem_settings[2]);
define('WPMEM_SHOW_EXCERPT', $wpmem_settings[3]);
define('WPMEM_NOTIFY_ADMIN', $wpmem_settings[4]);
define('WPMEM_MOD_REG',      $wpmem_settings[5]);
define('WPMEM_CAPTCHA',      $wpmem_settings[6]);
define('WPMEM_NO_REG',       $wpmem_settings[7]);

add_action('init', 'wpmem');  							// runs the wpmem() function right away, allows for setting cookies
add_action('widgets_init', 'widget_wpmemwidget_init');  // if you are using widgets, this initializes the widget
add_action('wp_head', 'wpmem_head');					// runs header functions
add_filter('the_content', 'wpmem_securify', $content);  // runs securifies the $content.

// scripts for admin panels only load for admins - makes the front-end of the plugin lighter
add_action('admin_init', 'wpmem_chk_admin');
function wpmem_chk_admin()
{
	if ( current_user_can('edit_users') ) { require_once('wp-members-admin.php'); }
}

// admin planel only loads if user has manage_options capabilities
add_action('admin_menu', 'wpmem_admin_options');
function wpmem_admin_options()
{
	add_options_page('WP-Members', 'WP-Members', 'manage_options', basename(__FILE__), 'wpmem_admin');
}

// install scripts only load if we are installing, makes the plugin lighter
register_activation_hook(__FILE__, 'wpmem_install');
function wpmem_install()
{
	require_once("wp-members-install.php");
	wpmem_do_install();	
}

// loads the plugin core
include_once('wp-members-core.php');
?>