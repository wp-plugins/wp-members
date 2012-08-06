<?php
/**
 * WP-Members Core Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler 
 * @copyright 2006-2012
 */


/*****************************************************
 * PRIMARY FUNCTIONS
 *****************************************************/
 

if ( ! function_exists( 'wpmem' ) ):
/**
 * The Main Action Function
 *
 * Does actions required at initialization
 * prior to headers being sent.
 *
 * @since 0.1 
 *
 * @global string $wpmem_a the action variable also used in wpmem_securify
 * @global string $wpmem_regchk contains messages returned from $wpmem_a action functions, used in wpmem_securify
 */
function wpmem()
{	
	global $wpmem_a, $wpmem_regchk;

	if( isset( $_REQUEST['a'] ) ) { $wpmem_a = trim( $_REQUEST['a'] ); }

	switch ($wpmem_a) {

	case ("login"):
		$wpmem_regchk = wpmem_login();
		break;

	case ("logout"):
		wpmem_logout();
		break;

	case ("register"):
		include_once('wp-members-register.php');
		$wpmem_regchk = wpmem_registration('register');
		break;
	
	case ("update"):
		include_once('wp-members-register.php');
		$wpmem_regchk = wpmem_registration('update');
		break;
	
	case ("pwdchange"):
		$wpmem_regchk = wpmem_change_password();
		break;
	
	case ("pwdreset"):
		$wpmem_regchk = wpmem_reset_password();
		break;

	} // end of switch $a (action)

}
endif;


if ( ! function_exists( 'wpmem_securify' ) ):
/**
 * The Securify Content Filter
 *
 * This is the primary function that picks up where wpmem() leaves off.
 * Determines whether content is shown or hidden for both post and
 * pages.
 *
 * @since 2.0
 *
 * @global var $wpmem_a the action variable received from wpmem()
 * @global string $wpmem_regchk contains messages returned from wpmem() action functions
 * @global string $wpmem_themsg contains messages to be output
 * @global string $wpmem_captcha_err contains error message for reCAPTCHA
 * @global array $post needed for protecting comments
 * @param string $content
 * @return $content
 *
 * @todo continue testing wpmem_do_excerpt - designed to insert an excerpt if no 'more' tag is found.
 */
function wpmem_securify( $content = null ) 
{ 

	// @todo this is being tested...
	// $content = wpmem_do_excerpt( $content );


	if( ! wpmem_test_shortcode() ) {
		
		global $wpmem_regchk, $wpmem_themsg, $wpmem_a;
		
		if( $wpmem_regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		// Block/unblock Posts
		if( !is_user_logged_in() && wpmem_block() == true ) {
		
			// protects comments if user is not logged in
			global $post;
			$post->post_password = wp_generate_password();
		
			include_once('wp-members-dialogs.php');
			
			// show the login and registration forms
			if( $wpmem_regchk ) {
				
				// empty content in any of these scenarios
				$content = '';
	
				switch( $wpmem_regchk ) {
	
				case "loginfailed":
					$content = wpmem_inc_loginfailed();
					break;
	
				case "success":
					$content = wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;
	
				default:
					$content = wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}
	
			} else {
			
				// toggle shows excerpt above login/reg on posts/pages
				if( WPMEM_SHOW_EXCERPT == 1 ) {
				
					$len = strpos($content, '<span id="more');
					$content = substr( $content, 0, $len );
					
				} else {
				
					// empty all content
					$content = '';
				
				}
	
				$content = $content . wpmem_inc_login();
				
				if( WPMEM_NO_REG != 1 ) { $content = $content . wpmem_inc_registration(); } // toggle turns off reg process for all but registration page.
			}
	

		// Protects comments if expiration module is used and user is expired
		} elseif( is_user_logged_in() && wpmem_block() == true ){
			
			if( WPMEM_USE_EXP == 1 ) { 
				$content = wpmem_do_expmessage( $content ); 
			}
			
		}
		
	}
	
	return $content;
	
} // end wpmem_securify
endif;


if ( ! function_exists( 'wpmem_do_sc_pages' ) ):
/**
 * Determines if content should be blocked
 *
 * @since 2.6
 *
 * @uses apply_filters Calls 'wpmem_user_edit_heading'
 *
 * @param string $page
 * @global string $wpmem_regchk
 * @global string $wpmem_themsg
 * @global string $wpmem_a
 * @return $content 
 */
function wpmem_do_sc_pages( $page )
{
	global $wpmem_regchk, $wpmem_themsg, $wpmem_a;
	include_once( 'wp-members-dialogs.php' );
	
	if ( $page == 'members-area' || $page == 'register' ) { 
		
		if( $wpmem_regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}
		
		if( ! is_user_logged_in() ) {
			if( $wpmem_a == 'register' ) {

				switch( $wpmem_regchk ) {

				case "success":
					$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;

				default:
					$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}

			} elseif( $wpmem_a == 'pwdreset' ) {

				$content = wpmem_page_pwd_reset( $wpmem_regchk, $content );

			} else {

				if( $page == 'members-area' ) { $content = $content . wpmem_inc_login( 'members' ); }
				
				// turn off registration on all but the register page.
				if( $page == 'register' || WPMEM_NO_REG != 1 ) { $content = $content . wpmem_inc_registration(); }
			}

		} elseif( is_user_logged_in() && $page == 'members-area' ) {

			$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );
		
			switch( $wpmem_a ) {

			case "edit":
				$content = $content . wpmem_inc_registration( 'edit', $heading );
				break;

			case "update":

				// determine if there are any errors/empty fields

				if( $wpmem_regchk == "updaterr" || $wpmem_regchk == "email" ) {

					$content = $content . wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration( 'edit', $heading );

				} else {

					//case "editsuccess":
					$content = $content . wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_memberlinks();

				}
				break;

			case "pwdchange":

				$content = wpmem_page_pwd_reset( $wpmem_regchk, $content );
				break;

			case "renew":
				$content = wpmem_renew();
				break;

			default:
				$content = wpmem_inc_memberlinks();
				break;					  
			}

		} elseif( is_user_logged_in() && $page == 'register' ) {

			//return wpmem_inc_memberlinks( 'register' );
			
			$content = $content . wpmem_inc_memberlinks( 'register' );
		
		}
			
	}
	
	if( $page == 'login' ) {
		
		if( $wpmem_regchk == "loginfailed" ) {
			$content = wpmem_inc_loginfailed();
		}
		
		if( ! is_user_logged_in() ) {
			$content = $content . wpmem_inc_login( 'login' );
		} else {
			$content = wpmem_inc_memberlinks( 'login' );
		}
		
	}
	
	if( $page == 'password' ) {
		$content = wpmem_page_pwd_reset( $wpmem_regchk, $content );
	}
	
	if( $page == 'user-edit' ) {
		$content = wpmem_page_user_edit( $wpmem_regchk, $content );
	}
	
	return $content;
} // end wpmem_do_sc_pages
endif;


if ( ! function_exists( 'wpmem_block' ) ):
/**
 * Determines if content should be blocked
 *
 * @since 2.6
 *
 * @uses apply_filters Calls wpmem_block
 *
 * @return bool $block 
 */
function wpmem_block()
{
	$block = false;

	if( is_single() ) {
		//$not_mem_area = 1; 
		if( WPMEM_BLOCK_POSTS == 1 && ! get_post_custom_values( 'unblock' ) ) { $block = true; }
		if( WPMEM_BLOCK_POSTS == 0 &&   get_post_custom_values( 'block' ) )   { $block = true; }
	}

	if( is_page() && ! is_page( 'members-area' ) && ! is_page( 'register' ) ) { 
		//$not_mem_area = 1; 
		if( WPMEM_BLOCK_PAGES == 1 && ! get_post_custom_values( 'unblock' ) ) { $block = true; }
		if( WPMEM_BLOCK_PAGES == 0 &&   get_post_custom_values( 'block' ) )   { $block = true; }
	}
	
	return apply_filters( 'wpmem_block', $block );
}
endif;


add_shortcode( 'wp-members', 'wpmem_shortcode' );
/**
 * Executes shortcode for settings, register, and login pages
 *
 * @since 2.4 
 *
 * @param array $attr page and status
 * @param array $content
 * @return string returns the result of wpmem_do_sc_pages
 * @return string returns $content between open and closing tags
 */
function wpmem_shortcode( $attr, $content = null )
{
	// handles the 'page' attribute
	if( isset( $attr['page'] ) ) {
		return do_shortcode( wpmem_do_sc_pages( $attr['page'] ) ); 
	}
	
	// handles the 'status' attribute
	if( isset( $attr['status'] ) ) {
		if( $attr['status'] == 'in' && is_user_logged_in() ) {
			return do_shortcode( $content );
		} elseif ( $attr['status'] == 'out' && ! is_user_logged_in() ) {
			return do_shortcode( $content );
		}
	}
	
	// handles the 'field' attribute
	if( isset( $attr['field'] ) ) {
		global $user_ID;
		$user_info = get_userdata( $user_ID );
		return $user_info->$attr['field'] . do_shortcode( $content );
	}
}


if ( ! function_exists( 'wpmem_test_shortcode' ) ):
/**
 * Tests $content for the presence of the [wp-members] shortcode
 *
 * @since 2.6
 *
 * @global string $post
 * @uses get_shortcode_regex
 * @return bool
 *
 * @example http://codex.wordpress.org/Function_Reference/get_shortcode_regex
 */
function wpmem_test_shortcode()
{
	global $post;
	
	$pattern = get_shortcode_regex();
	
	preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches );
	
	if( is_array( $matches ) && array_key_exists( 2, $matches ) && in_array( 'wp-members', $matches[2] ) ) {
		return true;
    }
}
endif;


if( WPMEM_MOD_REG == 1 ) { add_filter( 'authenticate', 'wpmem_check_activated', 99, 3 ); }
/**
 * Checks if a user is activated
 *
 * @since 2.7.1
 *
 * @param int $user
 * @param string $username
 * @param string $password
 * @uses wp_check_password
 * @return int $user
 */ 
function wpmem_check_activated( $user, $username, $password ) 
{
	// password must be validated
	$pass = wp_check_password( $password, $user->user_pass, $user->ID );
	if( ! $pass ) { 
		return $user; 
	}

	// activation flag must be validated
	$active = get_user_meta( $user->ID, 'active', 1 );
	if( $active != 1 ) {
		return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: User has not been activated.', 'wp-members' ) );
	}

	// if the user is validated, return the $user object
	return $user;
}


if( ! function_exists( 'wpmem_login' ) ):
/**
 * Logs in the user
 *
 * Logs in the the user using wp_signon (since 2.5.2). If login 
 * is successful, it redirects and exits; otherwise "loginfailed"
 * is returned.
 *
 * @since 0.1
 *
 * @uses apply_filters Calls 'wpmem_login_redirect' hook to get $redirect_to
 *
 * @uses wp_signon
 * @uses wp_redirect Redirects to $redirect_to if login is successful
 * @return string Returns "loginfailed" if the login fails
 */
function wpmem_login()
{
	if( isset( $_POST['redirect_to'] ) ) {
		$redirect_to = $_POST['redirect_to'];
	} else {
		$redirect_to = $_SERVER['PHP_SELF'];
	}
	
	$redirect_to = apply_filters( 'wpmem_login_redirect', $redirect_to );

	if( isset( $_POST['rememberme'] ) == 'forever' ) {
		$rememberme = true;
	} else {
		$rememberme = false;
	}

	if( $_POST['log'] && $_POST['pwd'] ) {
		
		$user_login = sanitize_user( $_POST['log'] );
		
		$creds = array();
		$creds['user_login']    = $user_login;
		$creds['user_password'] = $_POST['pwd'];
		$creds['remember']      = $rememberme;
		
		$user = wp_signon( $creds, false );

		if( ! is_wp_error( $user ) ) {
			if( ! $using_cookie )
				wp_setcookie( $user_login, $user_pass, false, '', '', $rememberme );
			wp_redirect( $redirect_to );
			exit();
		} else {
			return "loginfailed";
		}
	
	} else {
		//login failed
		return "loginfailed";
	}	

} // end of login function
endif;


if ( ! function_exists( 'wpmem_logout' ) ):
/**
 * Logs the user out then redirects
 *
 * @since 2.0
 *
 * @uses apply_filters Calls wpmem_login_redirect
 * @uses wp_clearcookie
 * @uses wp_logout
 * @uses nocache_headers
 * @uses wp_redirect
 */
function wpmem_logout()
{
	$redirect_to = apply_filters( 'wpmem_logout_redirect', get_bloginfo( 'url' ) );

	wp_clearcookie();
	do_action( 'wp_logout' );
	nocache_headers();

	wp_redirect( $redirect_to );
	exit();
}
endif;


if ( ! function_exists( 'wpmem_login_status' ) ):
/**
 * Displays the user's login status
 *
 * @since 2.0
 *
 * @uses wpmem_inc_memberlinks()
 */
function wpmem_login_status()
{
	include_once('wp-members-dialogs.php');
	if (is_user_logged_in()) { echo wpmem_inc_memberlinks( 'status' ); }
}
endif;


if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
/**
 * Displays the sidebar
 *
 * @since 2.0
 *
 * @uses wpmem_do_sidebar()
 */
function wpmem_inc_sidebar()
{
	include_once('wp-members-sidebar.php');
	wpmem_do_sidebar();
}
endif;


if ( ! function_exists( 'widget_wpmemwidget_init' ) ):
/**
 * Initializes the widget
 *
 * @since 2.0
 *
 * @uses register_widget
 */
function widget_wpmemwidget_init()
{
	include_once( 'wp-members-sidebar.php' );
	register_widget( 'widget_wpmemwidget' );
}
endif;


if ( ! function_exists( 'wpmem_change_password' ) ):
/**
 * Handles user password change (not reset)
 *
 * @since 2.1
 *
 * @global $user_ID
 * @return string the value for $wpmem_regchk
 */
function wpmem_change_password()
{
	global $user_ID;
	if ($_POST['formsubmit']) {

		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		
		if ( ! $pass1 && ! $pass2 ) { // check for both fields being empty
		
			return "pwdchangempty";

		} elseif ( $pass1 != $pass2 ) { // make sure the fields match

			return "pwdchangerr";

		} else { // update password in db (wp_update_user hashes the password)

			wp_update_user( array ( 'ID' => $user_ID, 'user_pass' => $pass1 ) );
			return "pwdchangesuccess";

		}
	}
	return;
}
endif;


if( ! function_exists( 'wpmem_reset_password' ) ):
/**
 * Resets a forgotten password
 *
 * @since 2.1
 *
 * @uses wp_generate_password
 * @uses wp_update_user
 * @return string value for $wpmem_regchk
 */
function wpmem_reset_password()
{ 
	if( isset( $_POST['formsubmit'] ) ) {

		$username = $_POST['user'];
		$email    = $_POST['email'];

		if( ! $username || ! $email ) { 

			// there was an empty field
			return "pwdreseterr";

		} else {

			if( username_exists( $username ) ) {

				$user = get_user_by( 'login', $username );
				
				if( strtolower( $user->user_email ) !== strtolower( $email ) || ( ( WPMEM_MOD_REG == 1 ) && ( get_user_meta( $user->ID,'active', true ) != 1 ) ) ) {
					// the username was there, but the email did not match OR the user hasn't been activated
					return "pwdreseterr";
					
				} else {
					
					// generate a new password
					$new_pass = wp_generate_password();
					
					// update the users password
					wp_update_user( array ( 'ID' => $user->ID, 'user_pass' => $new_pass ) );

					// send it in an email
					require_once( 'wp-members-email.php' );
					wpmem_inc_regemail( $user->ID, $new_pass, 3 );
					
					return "pwdresetsuccess";
				}
			} else {

				// username did not exist
				return "pwdreseterr";
			}
		}
	}
	return;
}
endif;


if( ! function_exists( 'wpmem_no_reset' ) ):
/**
 * Keeps users not activated from resetting their password 
 * via wp-login when using registration moderation.
 *
 * @since 2.5.1
 *
 * @return bool
 */
function wpmem_no_reset() {

	if( strpos( $_POST['user_login'], '@' ) ) {
		$user = get_user_by_email( trim( $_POST['user_login'] ) );
	} else {
		$username = trim( $_POST['user_login'] );
		$user     = get_user_by( 'login', $username );
	}

	if( WPMEM_MOD_REG == 1 ) { 
		if( get_user_meta( $user->ID, 'active', true ) != 1 ) { 			
			return false;
		}
	}
	
	return true;
}
endif;


/**
 * Anything that gets added to the the <html> <head>
 *
 * @since 2.2
 */
function wpmem_head()
{ 
	echo "<!-- WP-Members version ".WPMEM_VERSION.", available at http://butlerblog.com/wp-members -->\r\n";
}


/*****************************************************
 * END PRIMARY FUNCTIONS
 *****************************************************/


/*****************************************************
 * UTILITY FUNCTIONS
 *****************************************************/


if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8
 *
 * @param string $name the name of the field
 * @param string $type the field type
 * @param string $value the default value for the field
 * @param string $valtochk optional for comparing the default value of the field
 * @param string $class optional for setting a specific CSS class for the field 
 * @return string $str the field returned as a string
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' )
{
	switch( $type ) {

	case "checkbox":
		if( $class = 'textbox' ) { $class = "checkbox"; }
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" " . wpmem_selected( $value, $valtochk, $type ) . " />\n";
		break;

	case "text":
		$value = stripslashes( $value );
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />\n";
		break;

	case "textarea":
		$value = stripslashes( $value );
		if( $class = 'textbox' ) { $class = "textarea"; }
		$str = "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\">$value</textarea>";
		break;

	case "password":
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\" />\n";
		break;

	case "hidden":
		$str = "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;

	case "option":
		$str = "<option value=\"$value\" " . wpmem_selected( $value, $valtochk, 'select' ) . " >$name</option>\n";
		break;

	case "select":
		if( $class = 'textbox' ) { $class = "dropdown"; }
		$str = "<select name=\"$name\" id=\"$name\" class=\"$class\">\n";
		foreach( $value as $option ) {
			$pieces = explode( '|', $option );
			$str = $str . "<option value=\"$pieces[1]\"" . wpmem_selected( $pieces[1], $valtochk, 'select' ) . ">$pieces[0]</option>\n";
		}
		$str = $str . "</select>\n";
		break;

	}
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes)
 *
 * @since 0.1
 *
 * @param string $value
 * @param string $valtochk
 * @param string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type=null )
{
	if( $type == 'select' ) {
		$issame = 'selected';
	} else {
		$issame = 'checked';
	}
	if( $value == $valtochk ){ return $issame; }
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings
 *
 * @since 2.0
 *
 * @uses get_permalink
 * @param string $url
 * @return string $return_url
 */
function wpmem_chk_qstr( $url = null )
{
	$permalink = get_option( 'permalink_structure' );
	if( ! $permalink ) {
		if( ! $url ) { $url = get_option( 'home' ) . "/?" . $_SERVER['QUERY_STRING']; }
		$return_url = $url . "&amp;";
	} else {
		if( !$url ) { $url = get_permalink(); }
		$return_url = $url . "?";
	}
	return $return_url;
}
endif;


if ( ! function_exists( 'wpmem_generatePassword' ) ):
/**
 * Generates a random password 
 *
 * @since 2.0
 *
 * @return string the random password
 */
function wpmem_generatePassword()
{	
	return substr( md5( uniqid( microtime() ) ), 0, 7);
}
endif;


/**
 * Overrides the wptexturize filter
 *
 * Currently only used for the login form to remove the <br> tag that WP puts in after the "Remember Me"
 *
 * @since 2.6.4
 *
 * @param string $content
 * @return string $new_content
 */
function wpmem_texturize( $content ) 
{
	$new_content = '';
	$pattern_full = '{(\[wpmem_txt\].*?\[/wpmem_txt\])}is';
	$pattern_contents = '{\[wpmem_txt\](.*?)\[/wpmem_txt\]}is';
	$pieces = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );

	foreach( $pieces as $piece ) {
		if( preg_match( $pattern_contents, $piece, $matches ) ) {
			$new_content .= $matches[1];
		} else {
			$new_content .= wptexturize( wpautop( $piece ) );
		}
	}

	return $new_content;
}


if ( ! function_exists( 'wpmem_enqueue_style' ) ):
/**
 * Loads the stylesheet for tableless forms
 *
 * @since 2.6
 *
 * @uses wp_register_style
 * @uses wp_enqueue_style
 */
function wpmem_enqueue_style()
{		
	if ( WPMEM_CSSURL != null ) { 
		$css_path = WPMEM_CSSURL; 
	} else {
		$css_path = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
		$css_path = $css_path."css/wp-members.css";
	}

	wp_register_style('wp-members', $css_path);
	wp_enqueue_style( 'wp-members');
}
endif;


/**
 * Creates an excerpt on the fly if there is no 'more' tag
 *
 * @since 2.6
 *
 * @param string $content
 * @return string $content
 */
function wpmem_do_excerpt( $content )
{
    if( ! is_single() && ! is_page() && ! is_search() ) {
    
        // test for 'more' tag or excerpt
		$test = stristr( $content, 'class="more-link"' );
		if( $test ) { 
			
		} else {	
			$content = substr( $content, 0, 300 );
		}
    }
	
	return $content;
	
}


/*****************************************************
 * USER PROFILE FUNCTIONS
 *****************************************************/


/**
 * add WP-Members fields to the WP user profile screen
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_user_profile()
{
	global $user_id; ?>

	<h3><?php _e( 'Additional Info', 'wp-members' ); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option( 'wpmembers_fields' );
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
			$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
		
			$chk_tos = true;
			if( $wpmem_fields[$row][2] == 'tos' && $val == 'agree' ) { 
				$chk_tos = false; 
				echo wpmem_create_formfield( $wpmem_fields[$row][2], 'hidden', $val );
			}
			
			$chk_pass = true;
			if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = false; }
		
			if( $wpmem_fields[$row][4] == "y" && $wpmem_fields[$row][6] == "n" && $chk_tos && $chk_pass ) { 
			
				// if there are any required fields, set a toggle to show indicator in last line
				if( $wpmem_fields[$row][5] == 'y' ) { $has_req = true; } ?>  
				
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><?php
					
						$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
						if( $wpmem_fields[$row][3] == 'checkbox' || $wpmem_fields[$row][3] == 'select' ) {
							$valtochk = $val; 
							$val = $wpmem_fields[$row][7];
						}
						echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk );
						if( $wpmem_fields[$row][5] == 'y' ) { echo '<font color="red">*</font>'; }
						$valtochk = ''; // empty for the next field in the loop
					?></td>
				</tr>
			<?php } 
		}
		
		if( $has_req ) { ?>
				<tr>
					<th>&nbsp;</th>
					<td><font color="red">*</font> <?php _e( 'Indicates a required field', 'wp-members' ); ?></td>
				</tr><?php
		} ?>
	</table><?php
}


/**
 * updates WP-Members fields from the WP user profile screen
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_profile_update()
{
	global $user_id;
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {

		// if the field is user editable, 
		if( $wpmem_fields[$row][4] == "y" && $wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][2] != 'password' ) {
		
			// check for required fields
			$chk = '';
			if( $wpmem_fields[$row][5] == "n" || ( ! $wpmem_fields[$row][5] ) ) { $chk = 'ok'; }
			if( $wpmem_fields[$row][5] == "y" && $_POST[$wpmem_fields[$row][2]] != '' ) { $chk = 'ok'; }

			if( $chk == 'ok' ) { 
				update_user_meta( $user_id, $wpmem_fields[$row][2], $_POST[$wpmem_fields[$row][2]] ); 
			} 
		}
	} 
}
?>