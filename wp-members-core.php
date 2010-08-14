<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as 
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


/*****************************************************
PRIMARY FUNCTIONS
*****************************************************/

// Generally, the plugin uses a/wpmem_a to define an action that it is passing from page to page.  
// It usese wpmem_regchk to pass what it is doing between functions (specifically, the init and the_content).

function wpmem()
{
	global $wpmem_a;

	$wpmem_a = trim($_REQUEST['a']);

	switch ($wpmem_a) {

	case ("login"):
		wpmem_login();
		break;

	case ("logout"):
		wpmem_logout();
		break;

	case ("register"):
		wpmem_register();
		break;
	
	case ("update"):
		wpmem_update();
		break;
	
	case ("pwdchange"):
		wpmem_change_password();
		break;
	
	case ("pwdreset"):
		wpmem_reset_password();
		break;

	} // end of switch $a (action)

}


function wpmem_securify ($content) 
{
	global $wpmem_regchk, $wpmem_themsg, $wpmem_a;

	/*if ($wpmem_regchk == "loginfailed") {
		wpmem_inc_loginfailed();
		$content = '';
		return $content;
	}*/

	if (is_single()) {
		$not_mem_area = 1; 
		if (WPMEM_BLOCK_POSTS == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if (WPMEM_BLOCK_POSTS == 0 &&  get_post_custom_values('block')) { $chk_securify = "block"; }
	}

	if (is_page() && !is_page('members-area')) { 
		$not_mem_area = 1; 
		if (WPMEM_BLOCK_PAGES == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if (WPMEM_BLOCK_PAGES == 0 &&  get_post_custom_values('block')) { $chk_securify = "block"; }
	}

	// Block/unblock Posts
	if ( !is_user_logged_in() && $not_mem_area == 1 && $chk_securify == "block" ) {
	
		include_once('wp-members-dialogs.php');

		// show the login and registration forms
		if ($wpmem_regchk) {

			switch($wpmem_regchk) {

			case "loginfailed":
				wpmem_inc_loginfailed();
				break;

			case "success":
				wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
				wpmem_inc_login();
				break;

			default:
				wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
				wpmem_inc_registration($fields);
				break;
			}

		} else {
		
			// new in 2.3, toggle shows excerpt above login/reg on posts/pages
			if (WPMEM_SHOW_EXCERPT == 1) {
				$len = strpos($content, 'more');
				echo "<p>".substr($content, 0, $len-10)."</p>";
			}

			wpmem_inc_login();
			
			if (WPMEM_NO_REG != 1) { wpmem_inc_registration($fields); } // new in 2.3, toggle to turn off registration process.
		}

		//return empty content			
		$content = "";
	}

	// Members Area
	//   this takes a bit of manipulation to get it all to work on one page.
	//   make sure if you use this, to set the page slug to "members-area" or change the is_page() below
	if (is_page('members-area')) {
	
		include_once('wp-members-dialogs.php');
		
		if (!is_user_logged_in()) {
			if ($wpmem_a == 'register') {

				switch($wpmem_regchk) {

				case "success":
					wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
					wpmem_inc_login();
					break;

				default:
					wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
					wpmem_inc_registration($fields);
					break;
				}

			} elseif ($wpmem_a == 'pwdreset') {

				switch($wpmem_regchk) {

				case "pwdreseterr":
					wpmem_inc_regmessage($wpmem_regchk);
					break;

				case "pwdresetsuccess":
					wpmem_inc_regmessage($wpmem_regchk);
					break;

				default:
					wpmem_inc_resetpassword();
					break;
				}

			} else {

				wpmem_inc_login('members');
				
				if (WPMEM_NO_REG != 1) { wpmem_inc_registration($fields); } // new in 2.3, toggle to turn off registration process.
			}
			$output = '';
			$content = '';

		} else {

			switch($wpmem_a) {

			case "edit":
				wpmem_inc_registration($fields,'edit',"Edit Your Information");
				$content = '';
				break;

			case "update":

				// determine if there are any errors/empty fields

				if ($wpmem_regchk == "updaterr") {

					wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
					wpmem_inc_registration($fields,'edit',"Edit Your Information");
					$content = '';

				} else {

					//case "editsuccess":
					wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
					
					$output = wpmem_inc_memberlinks();

				}
				break;

			case "pwdchange":

				switch ($wpmem_regchk) {

				case "pwdchangerr":
					wpmem_inc_regmessage($wpmem_regchk);
					wpmem_inc_changepassword();
					$content = '';
					break;

				case "pwdchangesuccess":
					wpmem_inc_regmessage($wpmem_regchk);
					break;

				default:
					wpmem_inc_changepassword();
					$content = '';
					break;				
				}
				break;
				
			// new for 2.3 expirations
			case "renew":
				$content = "insert the renewal process...";
				//wpmem_renew;
				break;

			default:
			// new for 2.3 expirations
				if (WPMEM_USE_EXP == 1) {
					global $user_ID;
					$output = "<p>".ucfirst(get_usermeta( $user_ID, 'exp_type')).
						" expires: ".get_usermeta( $user_ID, 'expires');
					$link = wpmem_chk_qstr();
					$output.= "&nbsp;&nbsp;[ <a href=\"".$link."a=renew\">renew</a> ]</p>";
				}
					
				$output .= wpmem_inc_memberlinks();
				break;					  
			}

		}

		$content = preg_replace("/\<!--members-area-->/", $output, $content);

	}

	return $content;
} // end wpmem_securify


// login function
function wpmem_login()
{
	global $wpdb, $wpmem_regchk;

	$redirect_to = $_REQUEST['redirect_to'];
	if (!$redirect_to) {
		$redirect_to = $_SERVER['PHP_SELF'];
	}

	// we are reusing WP's own login scripts here.  there is a reason for this...

	$user_login = $_POST['log'];
	$user_login = sanitize_user( $user_login );
	$user_pass  = $_POST['pwd'];
	$rememberme = $_POST['rememberme'];

	do_action('wp_authenticate', array(&$user_login, &$user_pass));

	if ( $user_login && $user_pass ) {
		$user = new WP_User(0, $user_login);

		if ( wp_login($user_login, $user_pass, $using_cookie) ) {
			if ( !$using_cookie )
				wp_setcookie($user_login, $user_pass, false, '', '', $rememberme);
			do_action('wp_login', $user_login);
			wp_redirect($redirect_to);
			exit();
		} else {
			$wpmem_regchk = "loginfailed";
		}

	} else {
		//login failed
		$wpmem_regchk = "loginfailed";
	}

} // end of login function


function wpmem_logout()
{
	//take 'em to the blog home page
	$redirect_to = get_bloginfo('url');

	wp_clearcookie();
	do_action('wp_logout');
	nocache_headers();

	wp_redirect($redirect_to);
	exit();
}


function wpmem_login_status()
{
	include_once('wp-members-sidebar.php');
	if (is_user_logged_in()) {	echo wpmem_inc_status(); }
}


function wpmem_change_password()
{ 
	global $wpdb,$user_ID,$userdata,$wpmem_regchk;
	if ($_POST['formsubmit']) {

		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];

		if ($pass1 != $pass2) {

			$wpmem_regchk = "pwdchangerr";

		} else {

			//update password in wpdb
			$new_pass = md5($pass1);			
			$wpdb->update( $wpdb->users, array( 'user_pass' => $new_pass ), array( 'ID' => $user_ID ), array( '%s' ), array( '%d' ) );

			$wpmem_regchk = "pwdchangesuccess";

		}
	}
	return;
}


function wpmem_reset_password()
{ 
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');

	global $wpdb,$wpmem_regchk;
	if ($_POST['formsubmit']) {

		$user  = $_POST['user'];
		$email = $_POST['email'];

		if (!$user || !$email) { 

			// there was an empty field
			$wpmem_regchk = "pwdreseterr";
			//return;

		} else {

			if (username_exists($user)) {

				$user_info = get_userdatabylogin($user);
				if($user_info->user_email !== $email) {
					// the username was there, but the email did not match
					$wpmem_regchk = "pwdreseterr";
					//return;
				} else {
					// everything checks out, go ahead and reset
					$new_pass     = substr( md5( uniqid( microtime() ) ), 0, 7);
					$hashpassword = md5($new_pass);
					$wpdb->update( $wpdb->users, array( 'user_pass' => $hashpassword ), array( 'user_login' => $user ), array( '%s' ), array( '%s' ) );
					$the_id = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_login = '{$user}'");

					require_once('wp-members-email.php');
					wpmem_inc_regemail($the_id,$new_pass,3);
					$wpmem_regchk = "pwdresetsuccess";
					//return;
				}
			} else {

				// username did not exist
				$wpmem_regchk = "pwdreseterr";
				//return;
			}
		}
	}
	return;
}


function wpmem_head()
{
	echo "<!-- WP-Members version ".WPMEM_VERSION.", available at http://butlerblog.com/wp-members -->";
}


function  wpmem_register()
{
	include_once('wp-members-register.php');
	wpmem_registration('register');
}


function wpmem_update()
{
	include_once('wp-members-register.php');
	wpmem_registration('update');
}


// new in 2.4
function wpmem_renew()
{
	// insert the renewal process...
}


// new in 2.4
function wpmem_set_exp($user_id)
{
	// get the expiration periods
	$exp_arr = get_option('wpmembers_experiod');

	if (WPMEM_USE_TRL == 1) {

		// if there is a trial period, use that
		$exp_num = $exp_arr['trial_num'];
		$exp_per = $exp_arr["trial_per"];

	} else { 

		// otherwise, use the subscription period
		$exp_num = $exp_arr['subscription_num'];
		$exp_per = $exp_arr["subscription_per"];

	}

	$wpmem_exp = wpmem_exp_date( $exp_num, $exp_per ); 
	update_user_meta( $user_id, 'expires', $wpmem_exp );
	
	if (WPMEM_USE_TRL == 1) {
		update_user_meta( $user_id, 'exp_type', 'trial');
	} else {
		update_user_meta( $user_id, 'exp_type', 'subscription');
	}
}


/*****************************************************
END PRIMARY FUNCTIONS
*****************************************************/


/*****************************************************
UTILITY FUNCTIONS
*****************************************************/


function wpmem_create_formfield($name,$type,$value,$valtochk=null)
{
	switch ($type) {

	case "checkbox":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" ";wpmem_selected($value,$valtochk,$type);echo " />\n";
		break;

	case "text":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" />\n";
		break;

	case "textarea":
		echo "<textarea cols=\"20\" rows=\"5\" name=\"$name\">$value</textarea>";
		break;

	case "password":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" />\n";
		break;

	case "hidden":
		echo "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;
		
	case "option":
		echo "<option value=\"$value\" "; wpmem_selected($value, $valtochk, 'select'); echo " >$name</option>";

	}
}


function wpmem_selected($value,$valtochk,$type=null)
{
	if($type == 'select') {
		$issame = 'selected';
	} else {
		$issame = 'checked';
	}
	if($value == $valtochk){ echo $issame; }
}


function wpmem_chk_qstr()
{
	$permalink = get_option('permalink_structure');
	if (!$permalink) {
		// no fancy permalinks.  Append to ?=
		$return_url = get_option('home') . "/?" . $_SERVER['QUERY_STRING'] . "&amp;";
	} else {
		// permalinks in use.  Add a ?
		$return_url = get_permalink() . "?";
	}
	return $return_url;
}


function wpmem_generatePassword()
{	
	return substr( md5( uniqid( microtime() ) ), 0, 7);
}


function wpmem_exp_date ( $exp_num, $exp_per )
{
	switch ( $exp_per ) {

	case "d":
		$wpmem_exp = date("m/d/y", mktime( 0, 0, 0, date("m"), date("d") + $exp_num, date("Y") ));
		break;

	case "w":
		$exp_num = $exp_num * 7;
		$wpmem_exp = date("m/d/y", mktime( 0, 0, 0, date("m"), date("d") + $exp_num, date("Y") ));
		break;

	case "m":
		$wpmem_exp = date("m/d/y", mktime( 0, 0, 0, date("m") + $exp_num, date("d"), date("Y") ));
		break;

	case "y":
		$wpmem_exp = date("m/d/y", mktime( 0, 0, 0, date("m"), date("d"), date("Y") + $exp_num ));
		break;
	}

	return $wpmem_exp;
}


/*****************************************************
END UTILITY FUNCTIONS
*****************************************************/


/*****************************************************
WIDGET FUNCTIONS
*****************************************************/


function widget_wpmemwidget_init()
{
	function widget_wpmemwidget($args)
	{
		extract($args);

		$options = get_option('widget_wpmemwidget');
		$title = $options['title'];

		echo $before_widget;

			// Widget Title
			if (!$title) {$title = "Login Status";}
			echo $before_title . $title . $after_title;

			// The Widget
			if (function_exists('wpmem')) { 
				include_once('wp-members-sidebar.php');
				wpmem_inc_sidebar($widget);
			}

		echo $after_widget;
	}
	
	function widget_wpmemwidget_control()
	{
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_wpmemwidget');
		if ( !is_array($options) )
			$options = array('title'=>'', 'buttontext'=>__('WP Members', 'widgets'));
		if ( $_POST['wpmemwidget-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['wpmemwidget-title']));
			update_option('widget_wpmemwidget', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);

		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="wpmemwidget-title">' . __('Title:') . ' <input style="width: 200px;" id="wpmemwidget-title" name="wpmemwidget-title" type="text" value="'.$title.'" /></label></p>';
		echo '<input type="hidden" id="wpmemwidget-submit" name="wpmemwidget-submit" value="1" />';
	}

	// register_sidebar_widget('WP Members', 'widget_wpmemwidget');
	// register_widget_control('WP Members', 'widget_wpmemwidget_control');
	// updated the above deprecated calls to the calls below in 2.3
	wp_register_sidebar_widget ( 'WP Members', 'WP Members', 'widget_wpmemwidget', ''); 
	wp_register_widget_control ( 'WP Members', 'WP Members', 'widget_wpmemwidget_control', '' );	
}


/*****************************************************
END WIDGET FUNCTIONS
*****************************************************/
?>