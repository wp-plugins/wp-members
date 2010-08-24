<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
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
		include_once('wp-members-register.php');
		wpmem_registration('register');
		break;
	
	case ("update"):
		include_once('wp-members-register.php');
		wpmem_registration('update');
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

	if ( is_page() && !is_page('members-area') && !is_page('register') ) { 
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
	if ( is_page('members-area') || is_page('register') ) {
	
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

				if (is_page('members-area')) { wpmem_inc_login('members'); }
				
				if (WPMEM_NO_REG != 1) { wpmem_inc_registration($fields); } // new in 2.3, toggle to turn off registration process.
			}
			$output = '';
			$content = '';

		} elseif (is_user_logged_in() && is_page('members-area')) {

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

			default:
				$output = wpmem_inc_memberlinks();
				break;					  
			}

		} elseif (is_user_logged_in() && is_page('register')) {
		
			$output = wpmem_inc_memberlinks('register');
		
		}

		if ( is_page('members-area') ) { $replacestr = "/\<!--members-area-->/"; }
		if ( is_page('register') )     { $replacestr = "/\<!--reg-area-->/"; }

		$content = preg_replace( $replacestr, $output, $content );

	}

	return $content;
} // end wpmem_securify


// login function
function wpmem_login()
{
	global $wpdb, $wpmem_regchk;

	$redirect_to = $_POST['redirect_to'];
	if (!$redirect_to) {
		$redirect_to = $_SERVER['PHP_SELF'];
	}

	// we are reusing WP's own login scripts here.  there is a reason for this...

	$user_login = $_POST['log'];
	$user_login = sanitize_user( $user_login );
	$user_pass  = $_POST['pwd'];
	$rememberme = $_POST['rememberme'];

	//do_action('wp_authenticate', array(&$user_login, &$user_pass));

	if ( $user_login && $user_pass ) {
		//$user = new WP_User(0, $user_login);

		if ( wp_login($user_login, $user_pass, $using_cookie) ) {
			if ( !$using_cookie )
				wp_setcookie($user_login, $user_pass, false, '', '', $rememberme);
			//do_action('wp_login', $user_login);
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
	echo "<!-- WP-Members version ".WPMEM_VERSION.", available at http://butlerblog.com/wp-members -->\r\n";
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
		echo "<textarea cols=\"20\" rows=\"5\" name=\"$name\">$val</textarea>";
		break;

	case "password":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" />\n";
		break;

	case "hidden":
		echo "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;

	}
}


function wpmem_selected($value,$valtochk,$type=null)
{
	if($value == $valtochk){ echo "checked"; }
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
			include_once('wp-members-sidebar.php');
			if (function_exists('wpmem')) { wpmem_inc_sidebar($widget);}

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

	wp_register_sidebar_widget ( 'WP Members', 'WP Members', 'widget_wpmemwidget', ''); 
	wp_register_widget_control ( 'WP Members', 'WP Members', 'widget_wpmemwidget_control', '' );	
}


/*****************************************************
END WIDGET FUNCTIONS
*****************************************************/
?>