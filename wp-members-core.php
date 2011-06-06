<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
PRIMARY FUNCTIONS
*****************************************************/

if ( ! function_exists( 'wpmem' ) ):
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
endif;


if ( ! function_exists( 'wpmem_securify' ) ):
function wpmem_securify ($content = null, $wpmem_sc_page = null) 
{
	global $wpmem_regchk, $wpmem_themsg, $wpmem_a;
	
	if ($wpmem_regchk == "captcha") {
		global $wpmem_captcha_err;
		$wpmem_themsg = __("There was an error with the CAPTCHA form.")."<br /><br />".$wpmem_captcha_err;
	}

	if (is_single()) {
		$not_mem_area = 1; 
		if (WPMEM_BLOCK_POSTS == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if (WPMEM_BLOCK_POSTS == 0 &&  get_post_custom_values('block'))   { $chk_securify = "block"; }
	}

	if ( is_page() && !is_page('members-area') && !is_page('register') ) { 
		$not_mem_area = 1; 
		if (WPMEM_BLOCK_PAGES == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if (WPMEM_BLOCK_PAGES == 0 &&  get_post_custom_values('block'))   { $chk_securify = "block"; }
	}

	// Block/unblock Posts
	if ( !is_user_logged_in() && $not_mem_area == 1 && $chk_securify == "block" ) {
	
		// NEW in 2.5.1 - overrides the need to add code snippet to comments.php...
		global $post;
		$post->post_password = wpmem_generatePassword();
	
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
	//}
	// new 2.4 for expirations
	//		NOTE: there is some reworking needed before exp module final release
	} elseif ( is_user_logged_in() && $chk_securify == 'block' ){
		
		if (WPMEM_USE_EXP == 1) { wpmem_do_expmessage($content); }
		
	} elseif ( is_user_logged_in() && get_option('comment_registration') == 1 ) {
	
		global $user_ID;
		$user_ID = '';
		
	}

	// Members Area
	//   this takes a bit of manipulation to get it all to work on one page.
	//   make sure if you use this, to set the page slug to "members-area" or change the is_page() below
	
	if ($wpmem_sc_page == 'members-area' || is_page('members-area')) { $wpmem_page = "members-area"; }
	if ($wpmem_sc_page == 'register' || is_page('register')) { $wpmem_page = "register"; }
	if ($wpmem_sc_page == 'login') { $wpmem_page = "login"; }
	
	if ( $wpmem_page == 'members-area' || $wpmem_page == 'register' ) {
	
		include_once('wp-members-dialogs.php');
		
		if ($wpmem_regchk == "loginfailed") {
			wpmem_inc_loginfailed();
			return;
		}
		
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

				if ($wpmem_page == 'members-area') { wpmem_inc_login('members'); }
				
				// NEW in 2.5.1 - updated this to turn off registration on all but the register page.
				if ( $wpmem_page == 'register' || WPMEM_NO_REG != 1 ) { wpmem_inc_registration($fields); } // new in 2.3, toggle to turn off registration process.
			}
			$output = '';
			$content = '';

		} elseif (is_user_logged_in() && $wpmem_page == 'members-area') {

			$edit_heading = __('Edit Your Information', 'wp-members');
		
			switch($wpmem_a) {

			case "edit":
				wpmem_inc_registration($fields, 'edit', $edit_heading);
				$content = '';
				break;

			case "update":

				// determine if there are any errors/empty fields

				if ($wpmem_regchk == "updaterr" || $wpmem_regchk == "email") {

					wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
					wpmem_inc_registration($fields, 'edit', $edit_heading);
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

			// new for 2.4 expirations
			case "renew":
				$content = "insert the renewal process...";
				//wpmem_renew;
				break;

			default:
				$output = wpmem_inc_memberlinks();
				
				// new for 2.4 expirations
				if (WPMEM_USE_EXP == 1) {
					$addto  = wpmem_user_page_detail(); 
					$output = $output.$addto;
				}
				break;					  
			}

		} elseif (is_user_logged_in() && $wpmem_page == 'register') {
		
			$output = wpmem_inc_memberlinks('register');
			
			if ($wpmem_sc_page == 'register') {
				echo $output;
				return;
			}
		
		}
		
		if ($wpmem_sc_page == 'members-area') { 
		
			echo $output; 
			return;
			
		} else {		

			if ( is_page('members-area') ) { $replacestr = "/\<!--members-area-->/"; }
			if ( is_page('register') )     { $replacestr = "/\<!--reg-area-->/"; }
			
			// the conditional here is allow for use of either the legacy version or 
			// the shortcode on members-area or register pages without preg_replace error
			if ( !$wpmem_sc_page ) { $content = preg_replace( $replacestr, $output, $content ); }
			
		}
			
	}
	
	if ( $wpmem_page == 'login' ) {
	
		include_once('wp-members-dialogs.php');
		
		if ($wpmem_regchk == "loginfailed") {
			wpmem_inc_loginfailed();
			return;
		}
		
		if (!is_user_logged_in()) {
			wpmem_inc_login('login');
		} else {
			include_once('wp-members-sidebar.php');
			wpmem_do_sidebar();
		}
		
	}

	return $content;
} // end wpmem_securify
endif;


add_shortcode ('wp-members', 'wpmem_shortcode');
function wpmem_shortcode($attr)
{
	wpmem_securify('', $attr['page']);
}


if ( ! function_exists( 'wpmem_login' ) ):
// login function
function wpmem_login()
{
	global $wpdb, $wpmem_regchk;

	$redirect_to = $_POST['redirect_to'];
	if (!$redirect_to) {
		$redirect_to = $_SERVER['PHP_SELF'];
	}

	if ( $_POST['log'] && $_POST['pwd'] ) {
		
		$user_login = sanitize_user( $_POST['log'] );
		
		$creds = array();
		$creds['user_login']    = $user_login;
		$creds['user_password'] = $_POST['pwd'];
		$creds['remember']      = $_POST['rememberme'];
		
		$user = wp_signon( $creds, false );
	
		if ( !is_wp_error($user) ) {
			if ( !$using_cookie )
				wp_setcookie($user_login, $user_pass, false, '', '', $rememberme);
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
endif;


if ( ! function_exists( 'wpmem_logout' ) ):
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
endif;


if ( ! function_exists( 'wpmem_login_status' ) ):
function wpmem_login_status()
{
	include_once('wp-members-sidebar.php');
	if (is_user_logged_in()) {	echo wpmem_inc_status(); }
}
endif;


if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
function wpmem_inc_sidebar()
{
	include_once('wp-members-sidebar.php');
	wpmem_do_sidebar();
}
endif;


if ( ! function_exists( 'widget_wpmemwidget_init' ) ):
function widget_wpmemwidget_init()
{
	include_once('wp-members-sidebar.php');
	wp_register_sidebar_widget ( 'WP-Members', 'WP-Members', 'widget_wpmemwidget', ''); 
	wp_register_widget_control ( 'WP-Members', 'WP-Members', 'widget_wpmemwidget_control', '' );	
}
endif;


if ( ! function_exists( 'wpmem_change_password' ) ):
function wpmem_change_password()
{ 
	global $wpdb,$user_ID,$userdata,$wpmem_regchk;
	if ($_POST['formsubmit']) {

		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];

		if ( ($pass1 != $pass2) || (!$pass1 && !$pass2) ) {

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
endif;


if ( ! function_exists( 'wpmem_reset_password' ) ):
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

		} else {

			if (username_exists($user)) {

				$user_info = get_userdatabylogin($user);
				
				if( $user_info->user_email !== $email || ( (WPMEM_MOD_REG == 1) && (get_user_meta($user_info->ID,'active','true') != 1) ) ) {
					// the username was there, but the email did not match OR the user hasn't been activated
					$wpmem_regchk = "pwdreseterr";
					
				} else {
					// everything checks out, go ahead and reset
					$new_pass     = substr( md5( uniqid( microtime() ) ), 0, 7);
					$hashpassword = md5($new_pass);
					$wpdb->update( $wpdb->users, array( 'user_pass' => $hashpassword ), array( 'user_login' => $user ), array( '%s' ), array( '%s' ) );
					$the_id = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_login = '{$user}'");

					require_once('wp-members-email.php');
					wpmem_inc_regemail($the_id,$new_pass,3);
					$wpmem_regchk = "pwdresetsuccess";
				}
			} else {

				// username did not exist
				$wpmem_regchk = "pwdreseterr";
			}
		}
	}
	return;
}
endif;


if ( ! function_exists( 'wpmem_no_reset' ) ):
// NEW in 2.5.1 - when using registration moderation, keeps users not activated from resetting their password via wp-login
function wpmem_no_reset() {

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}
	
	if (WPMEM_MOD_REG == 1) { 
		if (get_user_meta($user_data->ID,'active','true') != 1) { 			
			return false;
		}
	}
	
	return true;
}
endif;


if ( ! function_exists( 'wpmem_head' ) ):
function wpmem_head()
{ 
	echo "<!-- WP-Members version ".WPMEM_VERSION.", available at http://butlerblog.com/wp-members -->\r\n";
	
	// new in 2.5.1 , CSS for forms and custom CSS
	
	if ( WPMEM_OLD_FORMS != 1 ) {
		$wpmem_cssurl = get_option('wpmembers_cssurl',null);			
		if ( $wpmem_cssurl != null ) { 
			$css_path = $wpmem_cssurl; 
		} else {
			$css_path = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
			$css_path = $css_path."css/wp-members.css";
		}
		echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"$css_path\" />";
	}
}
endif;


/*****************************************************
END PRIMARY FUNCTIONS
*****************************************************/


/*****************************************************
UTILITY FUNCTIONS
*****************************************************/


if ( ! function_exists( 'wpmem_create_formfield' ) ):
function wpmem_create_formfield($name,$type,$value,$valtochk=null,$class='textbox')
{
	switch ($type) {

	case "checkbox":
		if ($class = 'textbox') { $class = "checkbox"; }
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" ";wpmem_selected($value,$valtochk,$type);echo " />\n";
		break;

	case "text":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />\n";
		break;

	case "textarea":
		if ($class = 'textbox') { $class = "textarea"; }
		echo "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\">$value</textarea>";
		break;

	case "password":
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\" />\n";
		break;

	case "hidden":
		echo "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;

	case "option":
		echo "<option value=\"$value\" "; wpmem_selected($value, $valtochk, 'select'); echo " >$name</option>\n";

	}
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
function wpmem_selected($value,$valtochk,$type=null)
{
	if($type == 'select') {
		$issame = 'selected';
	} else {
		$issame = 'checked';
	}
	if($value == $valtochk){ echo $issame; }
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
function wpmem_chk_qstr($url = null)
{
	$permalink = get_option('permalink_structure');
	if (!$permalink) {
		if (!$url) { $url = get_option('home') . "/?" . $_SERVER['QUERY_STRING']; }
		$return_url = $url."&amp;";
	} else {
		if (!$url) { $url = get_permalink(); }
		$return_url = $url."?";
	}
	return $return_url;
}
endif;


if ( ! function_exists( 'wpmem_generatePassword' ) ):
function wpmem_generatePassword()
{	
	return substr( md5( uniqid( microtime() ) ), 0, 7);
}
endif;


/*****************************************************
END UTILITY FUNCTIONS
*****************************************************/
?>