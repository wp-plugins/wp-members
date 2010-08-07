<?php
/*
Plugin Name: WP-Members
Plugin URI:  http://butlerblog.com/wp-members/
Description: WP access restriction and user registration.  For more information and to download the free "quick start guide," visit <a href="http://butlerblog.com/wp-members">http://butlerblog.com/wp-members</a>.  View the live demo at <a href="http://butlerblog.com/wpmembers">http://butlerblog.com/wpmembers</a>.
Version:     2.2.2
Author:      Chad Butler
Author URI:  http://butlerblog.com/
License:     GPL2
*/


/*  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

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
	http://www.gnu.org/licenses/gpl.html#SEC1
*/


/*
	A NOTE ABOUT LICENSE:

	While this plugin is released as free and open-source under the GPL2
	license, that does not mean it is "public domain." You are free to modify
	and redistribute as long as you comply with the license. This includes
	keeping a derivative work available as open source and also giving proper
	attribution to the original author and copyright holder.  This means you 
	cannot change two lines of code and claim copyright of the entire work as
	your own.  If you are unsure or have questions about how a derivative work
	you are developing complies with the license and copyright, contact the 
	original author at plugins@butlerblog.com.


	INSTALLATION PROCEDURE:

	* Save wp-members.php to your plugins directory
	* Login to the WP admin
	* Go to plugins tab and activate the plugin
	* That's it!

	For more complete installation and usage instructions,
	visit http://butlerblog.com/wp-members/
*/

define("WP_MEM_VERSION", "2.2.2");


/*****************************************************
ACTIONS, HOOKS, FILTERS & INCLUDES
*****************************************************/

add_action('init', 'wpmem');  // runs the wpmem() function right away, allows for setting cookies
add_action('widgets_init', 'widget_wpmemwidget_init');  // if you are using widgets, this initializes the widget
add_action('wp_head', 'wpmem_head');
add_filter('the_content', 'wpmem_securify', $content);  // runs the wpmem_securify on the $content.

add_action('admin_init', 'wpmem_chk_admin');
function wpmem_chk_admin()
{
	if ( current_user_can('manage_options') ) { require_once('wp-members-admin.php'); }
}

add_action('admin_menu', 'wpmem_admin_options');
function wpmem_admin_options()
{
	add_options_page('WP-Members', 'WP-Members', 8, basename(__FILE__), 'wpmem_admin');
}

register_activation_hook(__FILE__, 'wpmem_install');
function wpmem_install()
{
	require_once("wp-members-install.php");
	wpmem_do_install();	
}
 

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

	if ($wpmem_regchk == "loginfailed") {
		wpmem_inc_loginfailed();
		$content = '';
		return $content;
	}

	//check settings
	$wpmem_settings = get_option('wpmembers_settings');

	if (is_single()) {
		$not_mem_area = 1; 
		if ($wpmem_settings[1] == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if ($wpmem_settings[1] == 0 &&  get_post_custom_values('block')) { $chk_securify = "block"; }
	}

	if (is_page() && !is_page('members-area')) { 
		$not_mem_area = 1; 
		if ($wpmem_settings[2] == 1 && !get_post_custom_values('unblock')) { $chk_securify = "block"; }
		if ($wpmem_settings[2] == 0 &&  get_post_custom_values('block')) { $chk_securify = "block"; }
	}

	// Block/unblock Posts
	if ( !is_user_logged_in() && $not_mem_area == 1 && $chk_securify == "block" ) {

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
} // end wpmem_securify


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


function wpmem_head()
{
	echo "<!-- WP-Members version ".WP_MEM_VERSION.", available at http://butlerblog.com/wp-members -->";
}


function  wpmem_register()
{
	wpmem_registration('register');
}


function wpmem_update()
{
	wpmem_registration('update');
}


function wpmem_registration($toggle)
{
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');

	global $wpdb,$user_ID,$userdata,$wpmem_regchk,$wpmem_themsg,$username,$user_email,$wpmem_fieldval_arr;

	if($toggle=='register'){ $username = $_POST['log']; }
	$user_email = $_POST['user_email'];

	//build array of the posts
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		$wpmem_fieldval_arr[$row] = $_POST[$wpmem_fields[$row][2]];
	}

	// check for required fields	
	$wpmem_fields_rev = array_reverse($wpmem_fields);
	$wpmem_fieldval_arr_rev = array_reverse($wpmem_fieldval_arr);

	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ( $wpmem_fields_rev[$row][5] == 'y' ) {
			if ( !$wpmem_fieldval_arr_rev[$row] ) { $wpmem_themsg = $wpmem_fields_rev[$row][1]." is a required field."; }
		}
	} 

	switch($toggle) {

	case "register":

		if ( !$username ) { $wpmem_themsg = "username is a required field"; } 
		if ( $wpmem_themsg ) {

			$wpmem_regchk = "empty";

		} else {

			if (username_exists($username)) {

				$wpmem_regchk = "user";

			} else {

				$email_exists = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
				if ( $email_exists) {

					$wpmem_regchk = "email";

				} else {

				//everything checks out, so go ahead and insert

					//The main insertion process was taken from the WP core, the rest is modified to accomodate WP-Members user defined fields.

					$password = substr( md5( uniqid( microtime() ) ), 0, 7);
					$hashpassword = md5($password);
					$user_registered = gmdate('Y-m-d H:i:s');

					$query = "INSERT INTO $wpdb->users 
						(user_login, user_pass, user_email, user_registered, user_nicename, display_name) VALUES 
						('$username', '$hashpassword', '$user_email', '$user_registered', '$username', '$username')";

					$query = apply_filters('create_user_query', $query);
					$wpdb->query( $query );
					$user_id = $wpdb->insert_id;

					//Sets the user to the default role.
					$user = new WP_User($user_id);
					$user->set_role(get_option('default_role'));

					update_usermeta( $user_id, 'nickname', $username); // gotta have this whether it's used or not; if it's included w/ custom, value should be overwritten below.
					for ($row = 0; $row < count($wpmem_fields); $row++) {

						/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
						  they go into the wp_users table.  email is already done above, we need to then screen for putting in 
						  website, if used, and screen out email, since it's already done. */
						if ($wpmem_fields[$row][2] == 'user_url') {
							$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_id) );
						} else {
							if ($wpmem_fields[$row][2] != 'user_email') {update_usermeta( $user_id, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);}
						}
					} 

					//if this was successful, and you have email properly
					//configured, send a notification email to the user
					wpmem_inc_regemail($user_id,$password);

					// successful registration message
					$wpmem_regchk = "success";											

				}
			}
		}

		break;

	case "update":

		if ( $wpmem_themsg ) {

			$wpmem_regchk = "updaterr";

		} else {

			for ($row = 0; $row < count($wpmem_fields); $row++) {

				/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
				  they go into the wp_users table.  we need to then screen for these and put them in a different way*/
				switch ($wpmem_fields[$row][2]) {

				case ('user_url'):
					$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				case ('user_email'):
					$wpdb->update( $wpdb->users, array('user_email'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				default:
					update_usermeta( $user_ID, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);
					break;
				}
			} 

			$wpmem_regchk = "editsuccess";

		}

		break;

	}

} // end registration function


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
DIALOG OUTPUT FUNCTIONS
*****************************************************/


function wpmem_inc_login($page='page')
{ 	
	global $wpmem_regchk;

	$wpmem_dialogs = get_option('wpmembers_dialogs');

	if($page == "page"){
	     if($wpmem_regchk!="success"){
		
		//this shown above blocked content ?>
		<p><?php echo $wpmem_dialogs[0]; ?></p>

	<?php } 	
	} 

    $wpmem_login_form_arr = array('Existing users Login', 'Username', 'text', 'log', 'Password', 'password', 'pwd', 'login', 'Login');
    wpmem_login_form( $wpmem_login_form_arr );
}


function wpmem_inc_changepassword()
{ 
	$wpmem_login_form_arr = array('Change Password', 'New Password', 'password', 'pass1', 'Repeat Password', 'password', 'pass2', 'pwdchange', 'Update Password');
    wpmem_login_form( $wpmem_login_form_arr );
}


function wpmem_inc_resetpassword()
{ 
	$wpmem_login_form_arr = array('Reset Forgotten Password', 'Username', 'text', 'user', 'Email', 'text', 'email', 'pwdreset', 'Reset Password');
    wpmem_login_form( $wpmem_login_form_arr );
}


function wpmem_login_form( $wpmem_login_form_arr ) 
{ ?>	
	  <div class="wpmem_login">
		  <form name="form" method="post" action="<?php the_permalink();?>">
			  <table width="400" border="0" cellspacing="0" cellpadding="4">
				<tr align="left"> 
				  <td colspan="2"><h2><?php echo $wpmem_login_form_arr[0]; ?></h2></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[1]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[3], $wpmem_login_form_arr[2], '' ); ?></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[4]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[6], $wpmem_login_form_arr[5], '' ); ?></td>
				</tr>
			<?php if ( $wpmem_login_form_arr[7] == 'login' ) { ?>
				<tr>
				  <td width="118">&nbsp;</td>
				  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember me</td>
				</tr>
			<?php } ?>
				<tr> 
				  <td width="118">&nbsp;</td>
				  <td width="166">
					<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" /><?php
					if ( $wpmem_login_form_arr[7] != 'login' ) { wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
					wpmem_create_formfield( 'a', 'hidden', $wpmem_login_form_arr[7] ); ?>
					<input type="submit" name="Submit" value="<?php echo $wpmem_login_form_arr[8]; ?>" />
				  </td>
				</tr>
			<?php if ( $wpmem_login_form_arr[7] == 'login' ) { 
				$link = wpmem_chk_qstr(); ?>
				<tr>
				  <td colspan="2">Forgot password? <a href="<?php echo $link; ?>a=pwdreset">Click here to reset</a></td>
				</tr>
			<?php } ?>
			  </table> 
		  </form>
	  </div><?php
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
	global $wpdb,$user_ID, $userdata,$securify,$wpmem_regchk,$username,$wpmem_fieldval_arr;

	if (!$heading) { $heading = "<h2>New Users Registration</h2>"; }
	if (is_user_logged_in()) { get_currentuserinfo(); }	?>

	<div class="wpmem_reg">
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
			  <td colspan="2">&nbsp;</td>
			</tr>

			<?php
			$wpmem_fields = get_option('wpmembers_fields');
			for ($row = 0; $row < count($wpmem_fields); $row++)
			{ 
				if ($wpmem_fields[$row][4] == 'y') { ?>
					<tr<?php if( $wpmem_fields[$row][2] == 'description' ){ echo " valign=\"top\""; } ?>>
						<td align="right"><?php 
							echo $wpmem_fields[$row][1].":";
							if ($wpmem_fields[$row][5] == 'y') { ?><font color="red">*</font><?php } ?>
						</td>
						<td>
						<?php 
						if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
							switch ($wpmem_fields[$row][2]) {
							case('description'):
								$val = get_usermeta($user_ID,'description');
								break;

							case('user_email'):
								$val = $userdata->user_email;
								break;

							case('user_url'):
								$val = $userdata->user_url;
								break;

							default:				
								$val = get_usermeta($user_ID,$wpmem_fields[$row][2]);
								break;
							}

						} else {

							$val = $wpmem_fieldval_arr[$row]; 

						}

						wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$val,'');
						?>
						</td>
					</tr>
				<?php } 
			} ?>
			<tr><td colspan="2">&nbsp;</td></tr>
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
			  <td>&nbsp;</td>
			  <td><font color="red">*</font> Required field</td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td align="center"><!-- Attribution keeps this plugin free!! -->
				<small>Membership management by <br /><a href="http://butlerblog.com/wp-members" target="_blank">WP-Members</a><small>
			  </td>
			</tr>
		  </table>
		</form>
	</div>
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
	$wpmem_dialogs = get_option('wpmembers_dialogs');
	$wpmem_dialogs_toggle = array('user','email','success','editsuccess','pwdchangerr','pwdchangesuccess','pwdreseterr','pwdresetsuccess');

	for ($row = 0; $row < count($wpmem_dialogs_toggle); $row++) {

		if ($toggle == $wpmem_dialogs_toggle[$row]) { ?>

			<div class="wpmem_msg" align="center">
				<p>&nbsp;</p>
				<p><b><?php echo $wpmem_dialogs[$row+1]; ?></b></p>
				<p>&nbsp;</p>
			</div>

			<?php
			$didtoggle = "true";
		}	
	}

	if ($didtoggle != "true") { ?>

		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b>Sorry, <?php echo $themsg; ?></b></p>
			<p>&nbsp;</p>
		</div>

	<?php }		
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


function wpmem_inc_dialog_title()
{
	$wpmem_dialog_title_arr = array(
    	"Restricted post (or page), displays above the login/registration form",
        "Username is taken",
        "Email is registered",
        "Registration completed",
        "User update",
        "Passwords did not match",
        "Password changes",
        "Username or email do not exist when trying to reset forgotten password",
        "Password reset"  
    );
	return $wpmem_dialog_title_arr;
}


/*****************************************************
END DIALOG OUTPUT FUNCTIONS
*****************************************************/
?>