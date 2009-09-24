<?php
/*
Plugin Name: WP-Members
Plugin URI: http://butlerblog.com/wp-members/
Description: WP access restriction and user registration.
Version: 2.1.1
Author: Chad Butler
Author URI: http://butlerblog.com/
*/


/*  Copyright (c) 2009  Chad Butler (email : cbutlerjr@hotmail.com)


    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
	
	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html#SEC1
*/


/*
INSTALLATION PROCEDURE:
	
	* Save wp-members.php to your plugins directory
	* Login to the WP admin
	* Go to plugins tab and activate the plugin
	* That's it!
	
	For more complete installation and usage instructions,
	visit http://butlerblog.com/wp-members/

*/


// hooks and filters
add_action('init', 'wpmem');  // runs the wpmem() function right away, allows for setting cookies
add_action('widgets_init', 'widget_wpmemwidget_init');  // if you are using widgets, this initializes the widget
add_filter('the_content', 'wpmem_securify', $content);  // runs the wpmem_securify on the $content.


// Generally, the plugin uses a/wpmem_a to define an action that it is passing from page to page.  
// It usese wpmem_regchk to pass what it is doing between functions (specifically, the init and the_content).
 

/*****************************************************
PRIMARY FUNCTIONS
*****************************************************/


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
	
	if ($wpmem_regchk == "login_failed") {
		wpmem_inc_loginfailed();
		$content = '';
		return $content;
	}

	if (is_single()) {
		if (!is_user_logged_in()) {
		
			if (!get_post_custom_values('unblock')) { 
		
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
				
					wpmem_inc_login();
					wpmem_inc_registration($fields);
				}
				
				//return empty content			
				$content = "";
			}
		}
	}
	
	//testing new block/unblock for pages
	if (is_page() && !is_page('members-area')) {
		
		if (!is_user_logged_in()) {
		
			if (get_post_custom_values('block')) { 
		
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
				
					wpmem_inc_login();
					wpmem_inc_registration($fields);
				}
				
				//return empty content			
				$content = "";
			}
		}	
	}
	
	// Members Area
	//   this takes a bit of manipulation to get it all to work on one page.
	//   make sure if you use this, to set the page slug to "members-area" or change the is_page() below
	if (is_page('members-area')) {
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
				wpmem_inc_registration($fields);
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
				
			default:
				$output = wpmem_inc_memberlinks();
				break;					  
			}
					  
		}
	
		$content = preg_replace("/\<!--members-area-->/", $output, $content);
	
	}
		
	return $content;
}


// login function
function wpmem_login()
{
	global $wpdb;
	
	$redirect_to = $_REQUEST['redirect_to'];
	if (!$redirect_to) {
		$redirect_to = $_SERVER['PHP_SELF'];
	}
	
	// we are reusing WP's own login scripts here.
	
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
	if (is_user_logged_in()) {	
		echo wpmem_inc_status();
	}
}


// registration
function wpmem_register()
{
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');
	
	global $wpmem_regchk, $wpmem_themsg;
	global $wpdb;
	global $username,$password,$fname,$lname,$addr1,$addr2,$city,
		$thecity,$thestate,$zip,$country,$phone1,$email;

	$username  = $_POST['log'];
	$fname     = $_POST['fname'];
	$lname     = $_POST['lname'];
	$addr1     = $_POST['addr1'];
	$addr2     = $_POST['addr2'];
	$city      = $_POST['city'];
	$thestate  = $_POST['thestate'];
	$zip       = $_POST['zip'];
	$country   = $_POST['country'];
	$phone1    = $_POST['phone1'];
	$email     = $_POST['email'];
	
	// check for required fields
	if ( !$email ) 	  { $wpmem_themsg = "email is a required field"; }	
	if ( !$phone1 )   { $wpmem_themsg = "phone is a required field"; }
	if ( !$country )  { $wpmem_themsg = "country is a required field"; }
	if ( !$zip )      { $wpmem_themsg = "zip is a required field"; }
	if ( !$thestate ) { $wpmem_themsg = "state is a required field"; }
	if ( !$city )     { $wpmem_themsg = "city is a required field"; }
	if ( !$addr1 )    { $wpmem_themsg = "address is a required field"; }
	if ( !$lname )    { $wpmem_themsg = "last name is a required field"; }
	if ( !$fname )    { $wpmem_themsg = "first name is a required field"; }
	if ( !$username ) { $wpmem_themsg = "username is a required field"; }
	
	if ( $wpmem_themsg ) {
	
		$wpmem_regchk = "empty";
	
	} else {
	
		if (username_exists($username)) {
			
			$wpmem_regchk = "user";
			
		} else {
		
			$email_exists = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email = '$email'");
			if ( $email_exists) {
				
				$wpmem_regchk = "email";
				
			} else {
			
			//everything checks out, so go ahead and insert
			
				//The insertion process was taken from the WP core.
			
				$password = substr( md5( uniqid( microtime() ) ), 0, 7);
				$hashpassword = md5($password);
				$user_registered = gmdate('Y-m-d H:i:s');
				
				$query = "INSERT INTO $wpdb->users 
					(user_login, user_pass, user_email, user_registered, user_nicename, display_name) VALUES 
					('$username', '$hashpassword', '$email', '$user_registered', '$username', '$username')";
				
				$query = apply_filters('create_user_query', $query);
				$wpdb->query( $query );
				$user_id = $wpdb->insert_id;
		
				update_usermeta( $user_id, 'first_name', $fname);
				update_usermeta( $user_id, 'last_name', $lname);
				update_usermeta( $user_id, 'addr1', $addr1);
				update_usermeta( $user_id, 'addr2', $addr2);
				update_usermeta( $user_id, 'city', $city);
				update_usermeta( $user_id, 'thestate', $thestate);
				update_usermeta( $user_id, 'zip', $zip);
				update_usermeta( $user_id, 'country', $country);
				update_usermeta( $user_id, 'phone1', $phone1);
				
				update_usermeta( $user_id, 'nickname', $username);
				
			
				//if this was successful, and you have email properly
				//configured, send a notification email to the user
				wpmem_inc_regemail($user_id,$password);
	
				// successful registration message
				$wpmem_regchk = "success";											
				
			}
		}
	}
	
} // end of registration function


function wpmem_change_password()
{ 
	global $wpdb;
	global $user_ID, $userdata;
	global $wpmem_regchk;
	if ($_POST['formsubmit']) {
	
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		
		if ($password1 != $password2) {
		
			$wpmem_regchk = "pwdchangerr";
		
		} else {
		
			//update password in wpdb
			$new_pass = md5($password1);			
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
	
	global $wpdb;
	global $wpmem_regchk;
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

					wpmem_inc_regemail($the_id,$new_pass,'true');
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


function wpmem_update()
{
	global $wpmem_regchk, $wpmem_themsg;
	global $wpdb;
	global $user_ID, $userdata;
	global $username,$password,$fname,$lname,$addr1,$addr2,$city,
		$thecity,$thestate,$zip,$country,$phone1,$email;
		
	$fname     = $_POST['fname'];
	$lname     = $_POST['lname'];
	$addr1     = $_POST['addr1'];
	$addr2     = $_POST['addr2'];
	$city      = $_POST['city'];
	$thestate  = $_POST['thestate'];
	$zip       = $_POST['zip'];
	$country   = $_POST['country'];
	$phone1    = $_POST['phone1'];
	$email     = $_POST['email'];
    
		
	// check for required fields:
	if ( !$email ) 	  { $wpmem_themsg = "email is a required field"; }	
	if ( !$phone1 )   { $wpmem_themsg = "phone is a required field"; }
	if ( !$country )  { $wpmem_themsg = "country is a required field"; }
	if ( !$zip )      { $wpmem_themsg = "zip is a required field"; }
	if ( !$thestate ) { $wpmem_themsg = "state is a required field"; }
	if ( !$city )     { $wpmem_themsg = "city is a required field"; }
	if ( !$addr1 )    { $wpmem_themsg = "address is a required field"; }
	if ( !$lname )    { $wpmem_themsg = "last name is a required field"; }
	if ( !$fname )    { $wpmem_themsg = "first name is a required field"; }
	
	if ( $wpmem_themsg ) {
	
		$wpmem_regchk = "updaterr";
		
	} else {
		
		$wpdb->update( $wpdb->users, array( 'user_email' => $email ), array( 'ID' => $user_ID ), array( '%s' ), array( '%d' ) );
			
		update_usermeta( $user_ID, 'first_name', $fname);
		update_usermeta( $user_ID, 'last_name', $lname);
		update_usermeta( $user_ID, 'addr1', $addr1);
		update_usermeta( $user_ID, 'addr2', $addr2);
		update_usermeta( $user_ID, 'city', $city);
		update_usermeta( $user_ID, 'thestate', $thestate);
		update_usermeta( $user_ID, 'zip', $zip);
		update_usermeta( $user_ID, 'country', $country);
		update_usermeta( $user_ID, 'phone1', $phone1);

		// additional optional fields
		global $aim,$yim,$jabber,$description;
		if ($_POST['url']) { 
		     $user_url = $_POST['url'];
		     $query = "UPDATE $wpdb->users SET user_url = '$user_url' WHERE ID = $user_ID";
		     $wpdb->query( $query );
		}
		if ($_POST['aim']) { update_usermeta( $user_ID, 'aim', $_POST['aim']); }
		if ($_POST['yim']) { update_usermeta( $user_ID, 'yim', $_POST['yim']); }
		if ($_POST['jabber']) { update_usermeta( $user_ID, 'jabber', $_POST['jabber']); }
		if ($_POST['description']) { update_usermeta( $user_ID, 'description', $_POST['description']); }
		// end optional extra fields

		
		$wpmem_regchk = "editsuccess";
		
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
	if ($type == "checkbox") {
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" ";
		wpmem_selected($value,$valtochk,$type);
		echo " />\n";
	} else {
		echo "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" />\n";
	}
}


function wpmem_chk_qstr()
{
	$permalink = get_settings('permalink_structure');
	if (!$permalink) {
		// no fancy permalinks.  Append to ?=
		$return_url = get_settings('home') . "/?" . $_SERVER['QUERY_STRING'] . "&amp;";
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

	register_sidebar_widget('WP Members', 'widget_wpmemwidget');
	register_widget_control('WP Members', 'widget_wpmemwidget_control');
}


/*****************************************************
END WIDGET FUNCTIONS
*****************************************************/


/*****************************************************
CUSTOMIZABLE OUTPUT FUNCTIONS
*****************************************************/


function wpmem_inc_login($page='page')
{ 
	/*
	This is the form and table for the login.
	You can redesign in any way you wish as long
	as you DO NOT change the form or input properties */
	
	global $wpmem_regchk;
	if($page == "page"){
	     if($wpmem_regchk!="success"){
		
		//this shown above blocked content ?>
		<p>Content is restricted to site members.  Site membership is free, register below. 
		If you are an existing user, please login.</p>
		
	<?php } 	
	} ?>
		
	<form name="form1" method="post" action="<?php the_permalink() ?>">
	  <table width="400" border="0" cellspacing="0" cellpadding="4">
		<tr align="left"> 
		  <td colspan="2"><h2>Existing Users Login</h2></td>
		</tr>
		<tr> 
		  <td width="118" align="right">Username</td>
		  <td width="166"><input type="text" name="log" /></td>
		</tr>
		<tr> 
		  <td width="118" align="right">Password</td>
		  <td width="166"><input type="password" name="pwd" /></td>
		</tr>
		<tr>
		  <td width="118">&nbsp;</td>
		  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember me</td>
		</tr>
		<tr> 
		  <td>&nbsp;</td>
		  <td>
		  	<input type="hidden" name="redirect_to" value="<?php the_permalink() ?>" />
			<input type="hidden" name="a" value="login" />
			<input type="submit" name="Submit" value="login" />
		  </td>
		</tr>
		
	  <?php if($page == "members"){ 
	  	$link = wpmem_chk_qstr(); 
	  	
	  	// this is only shown on the "members" page ?>
	  	<tr>
	  	  <td colspan="2">Forgot password? <a href="<?php echo $link; ?>a=pwdreset">Click here to reset</a></td>
	  	</tr>
	  <?php } ?>
	  </table>
	</form>	
	
	<?php  // end edits for function wpmem_inc_login()
}


function wpmem_inc_loginfailed() 
{ 
	/* 
	failed login message.  
	you can customize this to fit your theme, etc.
	
	You may edit below this line */?>
	
	<div align="center">
		<h2>Login Failed!</h2>
		<p>You entered an invalid username or password.</p>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'];?>">Click here to continue.</a></p>
	</div>
	
	<?php  // end edits for function wpmem_inc_loginfailed()
}


function wpmem_inc_status()
{ 	
	/*
	reminder email was successfully sent message.  
	you can customize this to fit your theme, etc.
	*/
	
	global $user_login;
	$logout = get_bloginfo('url')."/?a=logout";
	
	//You may edit below this line
	
	$wpmem_login_status = "
	<p>".__('You are logged in as')." $user_login | <a href=\"".$logout."\">".__('click here to logout')."</a></p>";
	
	// end edits for function wpmem_inc_status()
	
	return $wpmem_login_status;
}


function wpmem_inc_sidebar()
{
	/*
	This function determines if the user is logged in
	and displays either a login form, or the user's 
	login status. Typically used for a sidebar.		
	You can call this directly, or with the widget
	*/
	global $user_login;
	$url = get_bloginfo('url');
	$logout = $url."/?a=logout";
	
	//this returns us to the right place
	if(is_home()) {
		$post_to = $_SERVER['PHP_SELF'];
	}else{
		$post_to = get_permalink();
	}
		
	if (!is_user_logged_in()){
	/*
	This is the login form.
	You may edit below this line, but do not
	change the <?php ?> tags or their contents */?>
	<ul>
		<p>You are not currently logged in.<br />
			<form name="form" method="post" action="<?php echo $post_to; ?>">
			Username<br />
			<input type="text" name="log" style="font:10px verdana,sans-serif;" /><br />
			Password<br />
			<input type="password" name="pwd" style="font:10px verdana,sans-serif;" /><br />
			<input type="hidden" name="rememberme" value="forever" />
			<input type="hidden" name="redirect_to" value="<?php echo $post_to; ?>" />
			<input type="hidden" name="a" value="login" />
			<input type="submit" name="Submit" value="login" style="font:10px verdana,sans-serif;" />
			</form>
		</p>
	</ul>
	<?php } else { 
	/*
	This is the displayed when the user is logged in.
	You may edit below this line, but do not
	change the <?php ?> tags or their contents */?>
	<ul>
		<p>
		  You are logged in as <?php echo $user_login; ?><br />
		  <a href="<?php echo $logout;?>">click here to logout</a>
		</p>
	</ul>
	
	<?php }
}


function wpmem_inc_registration($fields,$toggle = 'new',$heading = '')
{

	global $wpdb; 
	global $user_ID, $userdata;
	global $securify,$wpmem_regchk;
	global $username,$fname,$lname,$addr1,$addr2,
		$city,$thestate,$zip,$country,$phone1,$email;
		
	if (!$heading) { $heading = "<h2>New Users Registration</h2>"; }
	
	if (is_user_logged_in()) {
	
		get_currentuserinfo();
		
		if ( ($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
		
			$fname     = get_usermeta($user_ID,'first_name');
			$lname     = get_usermeta($user_ID,'last_name');
			$addr1     = get_usermeta($user_ID,'addr1');
			$addr2     = get_usermeta($user_ID,'addr2');
			$city      = get_usermeta($user_ID,'city');
			$thestate  = get_usermeta($user_ID,'thestate');
			$zip       = get_usermeta($user_ID,'zip');
			$country   = get_usermeta($user_ID,'country');
			$phone1    = get_usermeta($user_ID,'phone1');
			$email     = $userdata->user_email;
		}
	}
	/*
	This is the form and table for registration.
	You can redesign in any way you wish as long as you 
	DO NOT change what is inside the <?php ?> tags.
	*/
	?>
	
	
	<form name="form2" method="post" action="<?php the_permalink();//wpmem_chk_qstr();?>">
		  
	  <table width="400" border="0" cellspacing="0" cellpadding="4">
		<tr align="left"> 
		  <td colspan="2"><?php echo $heading; ?></td>
		</tr>
		<?php if ($toggle == 'edit') { ?>
		<tr> 
		  <td width="49%" align="right">Username:</td>
		  <td width="51%" align="left"><?php echo $userdata->user_login?></td>
		</tr>			
		<?php } else { ?>
		<tr> 
		  <td width="49%" align="right">Choose a Username <font color="red">*</font></td>
		  <td width="51%"><input name="log" type="text" value="<?php echo $username;?>" /></td>
		</tr>
		<?php } ?>
		<tr> 
		  <td align="right">&nbsp;</td>
		  <td>&nbsp;</td>
		</tr>
		<tr> 
		  <td align="right">First Name <font color="red">*</font></td>
		  <td><input name="fname" type="text" value="<?php echo $fname;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Last Name <font color="red">*</font></td>
		  <td><input name="lname" type="text" value="<?php echo $lname;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Address <font color="red">*</font></td>
		  <td><input name="addr1" type="text" value="<?php echo $addr1;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Address 2</td>
		  <td><input name="addr2" type="text" value="<?php echo $addr2;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">City <font color="red">*</font></td>
		  <td><input name="city" type="text" value="<?php echo $city;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">State (or Province) <font color="red">*</font></td>
		  <td><input name="thestate" type="text" value="<?php echo $thestate;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Zip (or Postal Code) <font color="red">*</font></td>
		  <td><input name="zip" type="text" value="<?php echo $zip;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Country <font color="red">*</font></td>
		  <td><input name="country" type="text" value="<?php echo $country;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">&nbsp;</td>
		  <td>&nbsp;</td>
		</tr>
		<tr> 
		  <td align="right">Day Phone <font color="red">*</font></td>
		  <td><input name="phone1" type="text" value="<?php echo $phone1;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">&nbsp;</td>
		  <td>&nbsp;</td>
		</tr>
		<tr> 
		  <td align="right">Email <font color="red">*</font></td>
		  <td><input name="email" type="text" value="<?php echo $email;?>" /></td>
		</tr>
		

		<?php /*  This section could be removed if you do not want to utilize these fields
			I put this in for plugin users that might be integrating with a forum 

			Also, this only shows when the user is logged in and is editing their info, not on regular registration
			*/

		if (is_user_logged_in()) { 
		
		global $aim,$yim,$jabber,$description; ?>

		<tr> 
		  <td align="right">Website </td>
		  <td><input name="url" type="text" value="<?php echo $userdata->user_url;?>" /></td>
		</tr>
		<tr> 
		  <td align="right">AIM </td>
		  <td><input name="aim" type="text" value="<?php echo get_usermeta($user_ID,'aim');?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Yahoo IM </td>
		  <td><input name="yim" type="text" value="<?php echo get_usermeta($user_ID,'yim');?>" /></td>
		</tr>
		<tr> 
		  <td align="right">Jabber/Google Talk </td>
		  <td><input name="jabber" type="text" value="<?php echo get_usermeta($user_ID,'jabber');?>" /></td>
		</tr>
		<tr> 
		  <td align="right" valign="top">Bio </td>
		  <td><textarea cols="15" rows="5" name="description"><?php echo get_usermeta($user_ID,'description');?></textarea></td>
		</tr>

		<?php 
	
		}

			/*  End of the optional fields 
			(well... really they are all optional, use what you need, modify/delete the rest...)  */ ?>

		<tr> 
		  <td align="right">&nbsp;</td>
		  <td>&nbsp;</td>
		</tr>


		<tr> 
		  <td align="right">&nbsp;</td>
		  <td>
		  <?php if ($toggle == 'edit') { ?>
		  	<input name="a" type="hidden" value="update" />
		  <?php } else { ?>
		    <input name="a" type="hidden" value="register" />
		  <?php } ?>
			<input name="redirect_to" type="hidden" value="<?php the_permalink();?>" />
			<input name="Submit" type="submit" value="submit" /> 
			&nbsp;&nbsp; 
			<input name="Reset" type="reset" value="Clear Form" />
		  </td>
		</tr>
		<tr>
		  <td align="right">&nbsp;</td>
		  <td><font color="red">*</font> Required</td>
		</tr>
	  </table>
	</form>
	<?php
}


function wpmem_inc_changepassword()
{ ?>
	  <form name="form" method="post" action="<?php the_permalink();?>">
	  <table width="400" border="0" cellspacing="0" cellpadding="4">
		<tr align="left"> 
		  <td colspan="2"><h2>Change Password</h2></td>
		</tr>
		<tr> 
		  <td width="118" align="right">New Password</td>
		  <td width="166"><input type="password" name="password1" /></td>
		</tr>
		<tr> 
		  <td width="118" align="right">Repeat Password</td>
		  <td width="166"><input type="password" name="password2" /></td>
		</tr>
		<tr> 
		  <td width="118">&nbsp;</td>
		  <td width="166">
		  	<input type="hidden" name="redirect_to" value="<?php the_permalink() ?>" />
			<input type="hidden" name="formsubmit" value="1" />
			<input type="hidden" name="a" value="pwdchange" />
			<input type="submit" name="Submit" value="Update Password" />
		  </td>
		</tr>
	  </table> 
	  </form> 
	  <?php
}


function wpmem_inc_resetpassword()
{ ?>
	  <form name="form" method="post" action="<?php the_permalink();?>">
	  <table width="400" border="0" cellspacing="0" cellpadding="4">
		<tr align="left"> 
		  <td colspan="2"><h2>Reset Forgotten Password</h2></td>
		</tr>
		<tr> 
		  <td width="118" align="right">Username</td>
		  <td width="166"><input type="text" name="user" /></td>
		</tr>
		<tr> 
		  <td width="118" align="right">Email</td>
		  <td width="166"><input type="text" name="email" /></td>
		</tr>
		<tr> 
		  <td width="118">&nbsp;</td>
		  <td width="166">
		  	<input type="hidden" name="redirect_to" value="<?php the_permalink() ?>" />
			<input type="hidden" name="formsubmit" value="1" />
			<input type="hidden" name="a" value="pwdreset" />
			<input type="submit" name="Submit" value="Reset Password" />
		  </td>
		</tr>
	  </table> 
	  </form> 
	  <?php	
}


function wpmem_inc_memberlinks()
{
	$link = wpmem_chk_qstr();
	$str  = "<ul>\n<li><a href=\"".$link."a=edit\">Edit My Information</a></li>\n
			<li><a href=\"".$link."a=pwdchange\">Change Password</a></li>\n</ul>";
	return $str;
}


function wpmem_inc_regmessage($toggle,$themsg='')
{ 

	/*
	This is the error message for the registration form.
	You can customize this to fit your theme, but only 
	change the html. I've added notes on what can be
	changed.
	*/

	switch ($toggle) {
	
	case ("user"):
	
		// this is the duplicate user message.
		// you can customize this:
		?>
		
		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b>Sorry, that username is taken, please try another</b></p>
			<p>&nbsp;</p>
		</div>
		
		<?php 
		// stop changes
		
		break;
		
	case ("email"):
	
		// this is the duplicate email message.
		// you can customize this:
		?>
		
		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b>Sorry, that email address already has an account,<br />
				please try another</b></p>
			<p>&nbsp;</p>
		</div>
		
		<?php 
		// stop changes
	
		break;
		
	case ("success"):
	
		// this is the duplicate email message.
		// you can customize this:
		?>	
		
		<div class="wpmem_msg" align="center">
			<p>Congratulations! Your registration was successful.</p>
			<p>You may now login using the password that was emailed to you.</p>
		</div>
		
		<?php 
		// stop changes
	
		break;
		
	case ("editsuccess"):
		
		?>
		<div class="wpmem_msg" align="center">
			<p>Your information was updated!</p>
		</div>
		<?php
		break;
		
	case ("pwdchangerr"):
		
		?>
		<div class="wpmem_msg" align="center">
			<p>Passwords did not match</p>
			<p>Please <a href="javascript:history.back(1)">&laquo;Go Back</a> and try again</p>
		</div>
		<?php
		break;
		
	case ("pwdchangesuccess"):
		
		?>
		<div class="wpmem_msg" align="center">
			<p>Password successfully changed!</p>
			<p>You will need to re-login with your new password.</p>
		</div>
		<?php
		break;
		
	case ("pwdreseterr"):
		
		?>
		<div class="wpmem_msg" align="center">
			<p>Either the username or email address do not exist in our records.</p>
			<p>Please <a href="javascript:history.back(1)">&laquo;Go Back</a> and try again</p>
		</div>
		<?php
		break;
		
	case ("pwdresetsuccess"):
		
		?>
		<div class="wpmem_msg" align="center">
			<p>Password successfully reset!</p>
			<p>An email containing a new password has been sent 
			   to the email address on file for your account. You 
			   may change this random password when re-login with 
			   your new password.</p>
		</div>
		<?php
		break;	
		
	default:

		// you can customize this:
		?>
		
		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b>Sorry, <?php echo $themsg; ?></b></p>
			<p>&nbsp;</p>
		</div>
		
		<?php 
		// stop changes
		
		break;
	}
} 


function wpmem_inc_regemail($user_id,$password,$pwdreset='false')
{ 
	/*
	here you can customize the message that is sent to 
	a user when they request a reminder of their login info
	*/
	$user = new WP_User($user_id);
	
	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	$blogname = get_settings('blogname');
	$the_permalink = $_REQUEST['redirect_to'];
	
	/*
	You may edit below this line,
	  but be careful not to change variables */
	
	// set the subject line of the message
	$subj = "Your registration info for $blogname";
	
	// set the body of the message
	if ($pwdreset == "false") {
		//this is a new registration
		$body = "Thank you for registering for $blogname\r\n\r\n";
		$body.= "Your registration information is below.\r\n\r\n";
		$body.= "You may wish to retain a copy for your records.\r\n\r\n";
		$body.= "username: $user_login\r\n";
	}else{
		//this is not a new registration
		$body = "Your password has been reset for $blogname\r\n\r\n";
		$body.= "Your new password is included below.\r\n\r\n";
		$body.= "You may wish to retain a copy for your records.\r\n\r\n";		
	}
		
	$body.= "password: $password\r\n\r\n";
	$body.= "You may login here:\r\n";
	$body.= "$the_permalink\r\n\r\n";
	$body.= "-----------------------------------\r\n";
	$body.= "This is an automated message \r\n";
	$body.= "from $blogname\r\n";
	$body.= "Please do not reply to this address\r\n";
	
	// end edits for function wpmem_inc_regemail()
	
	wp_mail($user_email, $subj, $body, $headers = '');

}


/*****************************************************
END CUSTOMIZABLE OUTPUT FUNCTIONS
*****************************************************/



/*****************************************************
BEGIN ADMIN FEATURES
*****************************************************/

add_action('edit_user_profile', 'wpmem_admin_fields');
function wpmem_admin_fields()
{
	$user_id = $_REQUEST['user_id']; ?>
	
	<h3>WP-Members Additional Fields</h3>
	<table class="form-table">
	<?php
		$wpmem_customfields = array('addr1','addr2','city','thestate','zip','country','phone1');
		foreach($wpmem_customfields as $field) {
	?>    
        <tr>
        	<th><label><?php echo $field; ?></label></th>
        	<td><input id="<?php echo $field; ?>" type="text" class="input" name="<?php echo $field; ?>" value="<?php echo get_usermeta($user_id, $field);?>" size="25" /></td>
        </tr>
		<?php } ?>

	</table><?php
}

add_action('profile_update', wpmem_admin_update);
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_customfields = array('addr1','addr2','city','thestate','zip','country','phone1');
	foreach($wpmem_customfields as $field) {
		if($_POST[$field]){update_usermeta($user_id,$field,$_POST[$field]);}
	}
}

/*add_action('admin_menu', 'wpmem_admin_options');
function wpmem_admin_options()
{
	add_options_page('WP-Members', 'WP-Members', 8, basename(__FILE__), 'wpmem_admin');
}
function wpmem_admin()
{
	echo "<h3>WP-Members</h3>";
}*/


/*****************************************************
END ADMIN FEATURES
*****************************************************/



// that's all folks!
?>